<?php

// ต้องมีการ include 'classes/Database.php', 'classes/models/Price.php',
// 'classes/models/Route.php', 'classes/models/TravelType.php', 'classes/models/Location.php'

class PriceService {
    private Database $db;
    private Price $priceModel;
    private Route $routeModel;
    private TravelType $travelTypeModel;
    private Location $locationModel; // อาจจะใช้หรือไม่ก็ได้ ขึ้นอยู่กับว่า Service นี้ต้องดึง Location เองไหม

    public function __construct(Database $db) {
        $this->db = $db;
        $this->priceModel = new Price($db);
        $this->routeModel = new Route($db);
        $this->travelTypeModel = new TravelType($db);
        $this->locationModel = new Location($db); // หรือจะส่งผ่านข้อมูลจาก Controller เข้ามาก็ได้
    }


    /**
     * Prepares data for displaying the pricing matrix (Origin x Destination or Route x TravelType).
     *
     * @param int $selectedTravelTypeId The TravelTypeID to filter by (0 for all/default matrix).
     * @return array Contains 'allTravelTypes', 'allLocations', 'allRoutes', 'pricesData', 'selectedTravelTypeName'.
     */
    public function preparePricingMatrixData(int $selectedTravelTypeId): array {
        $allTravelTypes = [];
        $allLocations = [];
        $allRoutes = [];
        $pricesData = [];
        $selectedTravelTypeName = null;

        try {
            $allTravelTypes = $this->travelTypeModel->getAll();
            $allLocations = $this->locationModel->getAll();
            $allRoutes = $this->routeModel->getAll();

            if ($selectedTravelTypeId > 0) {
                $foundType = false;
                foreach ($allTravelTypes as $type) {
                    if ((int)$type['TravelTypeID'] === $selectedTravelTypeId) {
                        $selectedTravelTypeName = $type['TypeName'];
                        $foundType = true;
                        break;
                    }
                }
                if (!$foundType) {
                    throw new Exception("Invalid Travel Type ID.");
                }

                $sql = "SELECT PriceID, RouteID, TravelTypeID, Price, Currency FROM prices WHERE TravelTypeID = :travel_type_id";
                $stmt = $this->db->getConnection()->prepare($sql);
                $stmt->execute([':travel_type_id' => $selectedTravelTypeId]);
                $pricesForSelectedType = $stmt->fetchAll();

                foreach ($pricesForSelectedType as $priceEntry) {
                    $routeInfo = null;
                    foreach($allRoutes as $route) {
                        if ((int)$route['RouteID'] === (int)$priceEntry['RouteID']) {
                            $routeInfo = $route;
                            break;
                        }
                    }
                    if ($routeInfo) {
                        // Store price keyed by OriginID_DestinationID
                        $pricesData[$routeInfo['OriginLocationID'] . '_' . $routeInfo['DestinationLocationID']] = $priceEntry['Price'];
                    }
                }
            } else {
                // If no specific travel type is selected, you might want to load
                // prices for the default Route x TravelType matrix, or just return empty pricesData
                // For now, we'll assume this means no prices for O-D matrix until type is selected.
                // Or you could load the default Route x TravelType prices here:
                // $pricesData = $this->priceModel->getAll(); // This would be for Route x TravelType matrix
            }

        } catch (PDOException $e) {
            error_log("Database error in PriceService::preparePricingMatrixData: " . $e->getMessage());
            throw new Exception("Error preparing pricing data: " . $e->getMessage());
        }

        return [
            'allTravelTypes' => $allTravelTypes,
            'allLocations' => $allLocations,
            'allRoutes' => $allRoutes,
            'pricesData' => $pricesData, // This will be the O-D prices if selectedTravelTypeId > 0
            'selectedTravelTypeName' => $selectedTravelTypeName
        ];
    }

    /**
     * Saves prices from the Origin x Destination matrix form.
     *
     * @param array $pricesToSave Associative array prices[OriginID][DestinationID] = price.
     * @param int $selectedTravelTypeId The TravelTypeID for which these prices are being saved.
     * @throws Exception If there's a database error during saving.
     */
    public function savePricingMatrix(array $pricesToSave, int $selectedTravelTypeId) {

        
        if ($selectedTravelTypeId === 0) {
            $_SESSION['flash_danger'] = "กรุณาเลือกประเภทการเดินทางก่อนบันทึกราคา.";
            throw new Exception("กรุณาเลือกประเภทการเดินทางก่อนบันทึกราคา.");
        }
                                
        try {

            $this->db->getConnection()->beginTransaction();
            $allRoutes = $this->routeModel->getAll(); 
            foreach ($pricesToSave as $originId => $destinationPrices) {

                foreach ($destinationPrices as $destinationId => $priceValue) {

                    $routeId = null;

                    foreach($allRoutes as $route) {                 
                        if ((int)$route['OriginLocationID'] === (int)$originId && 
                            (int)$route['DestinationLocationID'] === (int)$destinationId) {
                            $routeId = (int)$route['RouteID'];
                            break;
                        }                    
                    }
               
                    // -------- ปรับเปลี่ยน NULL === 0.00 --------------------
                    if ($routeId !== null) { // Only save if a valid route exists
                        $priceValue = filter_var($priceValue, FILTER_VALIDATE_FLOAT);
                        if ($priceValue === false || $priceValue === null) {
                            $priceValue = 0.00;
                           // continue;                           
                        }
                        
                        $this->priceModel->createOrUpdate(
                            $routeId,
                            $selectedTravelTypeId,
                            (float)$priceValue,
                            'THB'
                        );
                    }
                }
            }
            $this->db->getConnection()->commit();

        } catch (PDOException $e) {
            $this->db->getConnection()->rollBack();
            error_log("Database error in savePricingMatrix::savePricingMatrix: " . $e->getMessage());
            throw new Exception("เกิดข้อผิดพลาดในการบันทึกราคา: " . $e->getMessage());
        }
    }

  /**
     * Records the actions that occur when adding or updating data to the "price_change_logs" database table.
     * นี่คือฟังก์ชันสำหรับ "บันทึก Log การเปลี่ยนแปลงราคา" ครับ
     * ผมย้ายมาจาก PriceModel ที่เราเคยคุยกัน เพื่อให้มันอยู่ใน PriceService (ตามที่คุณต้องการ)
     *
     * @param int $userId ID ของผู้ใช้งานที่ทำรายการ
     * @param string $actionType ประเภทการกระทำ (เช่น 'CREATE_PRICE', 'UPDATE_PRICE')
     * @param int $routeId ID ของเส้นทางที่ถูกเปลี่ยนแปลง
     * @param int $travelTypeId ID ของประเภทการเดินทาง
     * @param float|null $oldPrice ราคาเดิมก่อนการเปลี่ยนแปลง
     * @param float $newPrice ราคาใหม่หลังการเปลี่ยนแปลง
     * @param string $currency สกุลเงิน
     * @param string|null $ipAddress IP Address ของผู้ใช้งาน
     * @return bool True ถ้าบันทึก Log สำเร็จ, False ถ้าล้มเหลว
     */
    public function logPriceChange(int $userId, string $actionType, int $routeId, int $travelTypeId, ?float $oldPrice, float $newPrice, string $currency, ?string $ipAddress = null): bool {
        // คุณต้องแน่ใจว่า Class Price (priceModel) ของคุณมีเมธอดสำหรับการบันทึก Log
        // หรือคุณจะสร้าง Model แยกต่างหากสำหรับ Log ก็ได้ (เช่น PriceChangeLogModel)
        // เพื่อความง่าย ผมจะเพิ่มเมธอดนี้ใน Price Class ของคุณ (ถ้ายังไม่มี)
        // หรือถ้าคุณมี Class Log ของคุณเอง ก็เรียกใช้ผ่าน Class นั้นได้เลย
        
        // สมมติว่าใน Price Class (priceModel) ของคุณมีเมธอดชื่อ savePriceLog แล้ว
        // ถ้ายังไม่มี คุณต้องไปเพิ่มใน classes/models/Price.php
        return $this->priceModel->savePriceLog(
            $userId, 
            $actionType, 
            $routeId, 
            $travelTypeId, 
            $oldPrice, 
            $newPrice, 
            $currency, 
            $ipAddress
        );
    }


    /**
     * Saves prices from the Origin x Destination matrix form. (เวอร์ชั่นใหม่)
     * - กรองค่าว่างและไม่ถูกต้อง
     * - ตรวจสอบและสร้าง Route อัตโนมัติถ้ายังไม่มี
     * - บันทึก Log การเปลี่ยนแปลงราคา
     *
     * @param array $pricesToSave Associative array prices[OriginID][DestinationID] = price.
     * @param int $selectedTravelTypeId The TravelTypeID for which these prices are being saved.
     * @throws Exception If there's an error during processing or saving.
     * @return array ผลลัพธ์รวม: จำนวนรายการที่สำเร็จ และรายละเอียด Log ที่บันทึก
     */
    public function saveData_Matrix(array $pricesToSave, int $selectedTravelTypeId): array {
        // ตรวจสอบ User ID และ IP Address (เอามาจาก Controller ที่เรียกใช้)
        $userId = $_SESSION['user_id'] ?? 0; 
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;

        if ($selectedTravelTypeId === 0) {
            $_SESSION['flash_warning'] = "เลือกประเภทการเดินทาง จากนั้น บันทึกราคา.";
            throw new Exception("เลือกประเภทการเดินทาง คลิกปุ่ม บันทึกราคา ต่อไป.");
        }

        $processedLogs = []; // Array สำหรับเก็บข้อมูล Log ที่จะถูกบันทึก
        
        try {
            $this->db->getConnection()->beginTransaction(); // เริ่ม Transaction

            foreach ($pricesToSave as $originId => $destinationPrices) {
                // ตรวจสอบว่าเป็น Array จริงๆ เพื่อป้องกัน Error
                if (!is_array($destinationPrices)) {
                    continue;
                }
                
                foreach ($destinationPrices as $destinationId => $priceValue) {
                    // --- 1. Logic การกรอง "ตัดทิ้ง" ค่าว่างเปล่าและไม่ถูกต้อง ---
                    $trimmedPriceValue = is_string($priceValue) ? trim($priceValue) : $priceValue;
                    if ($trimmedPriceValue === null || $trimmedPriceValue === '') {
                        continue; // ข้ามรายการที่เป็นค่าว่างเปล่า
                    }
                    $filteredPriceValue = filter_var($trimmedPriceValue, FILTER_VALIDATE_FLOAT);
                    if ($filteredPriceValue === false) {
                        continue; // ข้ามรายการที่มีค่าไม่ถูกต้อง (ไม่ใช่ตัวเลข)
                    }
                    $newPrice = (float)$filteredPriceValue; // ค่าราคาที่สะอาดและเป็น float แล้ว

                    // --- 2. Logic การ "หาหรือสร้าง" RouteID อัตโนมัติ ---
                    $routeId = null;
                    
                    // พยายามค้นหา RouteID ที่มีอยู่แล้ว ด้วยเมธอด findByDetails ของ Class Route ของคุณ
                    $existingRoute = $this->routeModel->findByDetails(
                        (int)$originId, 
                        (int)$destinationId, 
                        (int)$selectedTravelTypeId // ต้องส่ง TravelTypeID เข้าไปด้วย
                    );

                    if ($existingRoute !== false && isset($existingRoute['RouteID'])) {
                        // ถ้าเจอ Route ที่มีอยู่แล้ว
                        $routeId = (int)$existingRoute['RouteID'];
                    } else {
                        // ถ้ายังไม่มี Route สำหรับคู่นี้และ TravelType นี้
                        try {
                            // สร้างเส้นทางใหม่ด้วยเมธอด createRoutes ของ Class Route ของคุณ
                            $createdRouteId = $this->routeModel->createRoutes(
                                (int)$originId,
                                (int)$destinationId,
                                (int)$selectedTravelTypeId, // ใช้ TravelTypeID จากฟังก์ชันหลัก
                                null, // distanceKM (คุณสามารถปรับเปลี่ยนได้ตามต้องการ)
                                null  // estimatedTravelTime (คุณสามารถปรับเปลี่ยนได้ตามต้องการ)
                            );
                            
                            if ($createdRouteId !== false && $createdRouteId !== null) {
                                $routeId = (int)$createdRouteId;
                            } else {
                                // ถ้า createRoutes คืนค่า false หรือ null (สร้างไม่สำเร็จ)
                                throw new Exception("ไม่สามารถสร้าง RouteID ใหม่ได้สำหรับ Origin " . $originId . " Destination " . $destinationId . " TravelType " . $selectedTravelTypeId);
                            }

                        } catch (Exception $e) {
                            // หากเกิดข้อผิดพลาดในการสร้างเส้นทาง (เช่น Database Error)
                            error_log("Failed to create new route: " . $e->getMessage()); // บันทึก Log
                            continue; // ข้ามการประมวลผลรายการราคาสำหรับเส้นทางนี้
                        }
                    }

                    // ถ้า $routeId ยังคงเป็น null (กรณีที่ควรมี RouteID แต่หาไม่เจอหรือสร้างไม่ได้)
                    if ($routeId === null) {
                        error_log("Skipping price save: No valid RouteID found or created for " . $originId . "-" . $destinationId . " (TravelType: " . $selectedTravelTypeId . ").");
                        continue;
                    }

                    // --- 3. บันทึก/อัปเดตราคาในตาราง prices และเตรียมข้อมูล Log ---
                    // เมธอด createOrUpdate ใน Price Class (priceModel) ควรคืนค่า status และ old_price
                    $priceUpdateResult = $this->priceModel->createOrUpdatePrice(
                        $routeId,
                        $selectedTravelTypeId,
                        $newPrice,
                        'THB'
                    );
                    
                    // เพิ่มข้อมูลการเปลี่ยนแปลงราคานี้ลงใน processedLogs
                    $processedLogs[] = [
                        'user_id' => $userId,
                        'action_type' => $priceUpdateResult['status'] ?? 'UNKNOWN', // 'CREATE_PRICE' หรือ 'UPDATE_PRICE'
                        'route_id' => $routeId,
                        'travel_type_id' => $selectedTravelTypeId,
                        'old_price' => $priceUpdateResult['old_price'] ?? null,
                        'new_price' => $newPrice,
                        'currency' => 'THB',
                        'ip_address' => $ipAddress
                    ];
                }
            }
            
            // --- 4. บันทึก Log การเปลี่ยนแปลงราคาลงในตาราง price_change_logs ---
            foreach ($processedLogs as $logData) {
                // เรียกใช้เมธอด logPriceChange ที่เราเพิ่งสร้าง
                $this->logPriceChange(
                    $logData['user_id'],
                    $logData['action_type'],
                    $logData['route_id'],
                    $logData['travel_type_id'],
                    $logData['old_price'],
                    $logData['new_price'],
                    $logData['currency'],
                    $logData['ip_address']
                );
            }

            $this->db->getConnection()->commit(); // Commit Transaction

            // คืนค่าผลลัพธ์: จำนวนรายการที่บันทึกสำเร็จ และรายละเอียด Log
            return [
                'success_count' => count($processedLogs),
                'logs_recorded' => $processedLogs // ถ้า Controller ต้องการรายละเอียด Log
            ];

        } catch (PDOException $e) {
            $this->db->getConnection()->rollBack(); // Rollback ถ้ามี Database Error
            error_log("Database error in PriceService::saveData_Matrix: " . $e->getMessage());
            $_SESSION['flash_error'] = "Database error while saving prices: " . $e->getMessage();
            throw new Exception("เกิดข้อผิดพลาดในการบันทึกราคา: " . $e->getMessage());
        } catch (Exception $e) { // ดักจับ Exception อื่นๆ (เช่น จาก Route Class)
            $this->db->getConnection()->rollBack(); // Rollback ถ้ามี Error ทั่วไป
            error_log("Application error in PriceService::saveData_Matrix: " . $e->getMessage());
            $_SESSION['flash_error'] = "An application error occurred while saving prices: " . $e->getMessage();
            throw new Exception("เกิดข้อผิดพลาดในการประมวลผลราคา: " . $e->getMessage());
        }
    }

}
