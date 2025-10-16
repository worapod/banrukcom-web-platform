<?php
// ไฟล์นี้ทำหน้าที่ดึงข้อมูล "จุดต้นทาง" จากฐานข้อมูล
// และส่งกลับไปให้ Ajax ในรูปแบบ JSON
// ตั้งค่า Header เพื่อบอก Browser ว่าข้อมูลที่ส่งกลับไปเป็น JSON
//D:\webserver\xampp\htdocs\booking_agency_authLog\classes\services\get_origins.php
header('Content-Type: application/json; charset=utf-8');

// --- 1. เชื่อมต่อฐานข้อมูล ---
// *** กรุณาใส่ Code การเชื่อมต่อฐานข้อมูลของคุณที่นี่ ***
/* ตัวอย่างการเชื่อมต่อด้วย PDO:
    $host = 'localhost';
    $db   = 'your_database_name';
    $user = 'your_username';
    $pass = 'your_password';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
         $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
         // ใน Production ควร log error แทนการ echo
         http_response_code(500);
         echo json_encode(['error' => 'Database connection failed.']);
         exit();
    }
*/

// สมมติว่า $pdo คือตัวแปรที่ใช้เชื่อมต่อฐานข้อมูล
$pdo = null; // ** ให้แทนที่ด้วย Object การเชื่อมต่อของคุณ **

// ตรวจสอบว่ามีการเชื่อมต่อฐานข้อมูลหรือไม่
if (!$pdo) {
    // ส่งข้อมูล JSON แสดงข้อผิดพลาดกลับไป
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้']);
    exit(); // จบการทำงาน
}

// --- 2. รับค่าจาก Ajax ---
// รับค่า TravelTypeID จาก parameter 'q'
$travelTypeId = isset($_GET['q']) ? $_GET['q'] : null;

if (!$travelTypeId) {
    // ถ้าไม่ได้รับ travel type id มา ให้ส่ง array ว่างกลับไป
    echo json_encode([]);
    exit();
}

$results = [];

try {
    // --- 3. เขียน SQL Query ตามตรรกะที่ถูกต้อง ---
    // ค้นหา "จุดต้นทาง" ที่ไม่ซ้ำกันทั้งหมดจากตาราง `locations`
    // โดยอ้างอิงจาก `TravelTypeID` ที่ระบุในตาราง `routes`

    $sql = "
        SELECT DISTINCT
            l.LocationID,
            l.LocationName,
            l.LocationType
        FROM
            locations AS l
        JOIN
            routes AS r ON l.LocationID = r.OriginLocationID
        WHERE
            r.TravelTypeID = ?
        ORDER BY
            l.LocationName ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$travelTypeId]);
    
    $results = $stmt->fetchAll();

} catch (\PDOException $e) {
    // กรณี Query ผิดพลาด
    http_response_code(500);
    // ไม่ควรแสดง $e->getMessage() ใน Production จริงเพื่อความปลอดภัย
    // ควรทำการ log ข้อผิดพลาดแทน
    error_log($e->getMessage());
    echo json_encode(['error' => 'เกิดข้อผิดพลาดในการดึงข้อมูล']); 
    exit();
}


// --- 4. ส่งข้อมูลกลับไปในรูปแบบ JSON ---
echo json_encode($results);

?>