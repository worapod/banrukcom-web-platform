<?php
// --- \booking_agency_web\main.php
/*
คำอธิบาย:

ใน Constructor ของ AppConfig (หรือในส่วนอื่นๆ ของโค้ดที่คุณต้องการใช้ค่าเหล่านี้) คุณสามารถเข้าถึง $_ENV['GCP_PROJECT_ID'], $_ENV['GCP_LOCATION'], และ $_ENV['GCP_MODEL_ID'] ได้โดยตรง
ผมได้เพิ่ม ?? throw new Exception(...) เพื่อให้แน่ใจว่าถ้าตัวแปรเหล่านี้ไม่ได้ถูกตั้งค่าบน Cloud Run จะมีการแจ้งเตือนข้อผิดพลาดที่ชัดเจน
สำหรับ GCP_KEY_FILE_PATH หากคุณตั้งค่าเป็น Secret บน Cloud Run และต้องการใช้ Service Account Key file คุณจะต้องแน่ใจว่าไฟล์นั้นถูก mount เข้ามาในคอนเทนเนอร์อย่างถูกต้อง หรือใช้ Application Default Credentials (ADC) ซึ่งเป็นวิธีที่แนะนำสำหรับ Cloud Run หากคุณให้สิทธิ์ Service Account ของ Cloud Run อย่างเหมาะสม
การใช้งานใน index.php หรือ Controller:
*/
// ...existing code...

/**
 * Step 1: ALWAYS load the Composer autoloader first.
 * This is the most critical step for loading classes like Banruk\AppConfig.
 */
require_once __DIR__ . '/vendor/autoload.php';

// ...existing code...

// ส่วนนี้จะเป็นการเรียกใช้ AppConfig
try {
    // สร้าง instance ของ AppConfig
    // เนื่องจาก AppConfig อยู่ใน namespace Banruk และไฟล์ AppGCP.php อยู่ใน classes/
    // Composer autoloader ที่ตั้งค่า "Banruk\\": "classes/" จะจัดการการโหลดให้
    $appConfig = new Banrukcom\AppConfig();

    // ตอนนี้คุณสามารถเข้าถึงค่าต่างๆ และ StorageClient ได้แล้ว
    $gcpProjectId = $appConfig->getProjectId();
    $gcpLocation = $appConfig->getLocation();
    $gcpModelId = $appConfig->getModelId();
    $gcpStorageClient = $appConfig->getStorageClient();

    // ตัวอย่างการใช้งาน (คุณสามารถลบออกได้เมื่อใช้งานจริง)
    // echo "GCP Project ID: " . $gcpProjectId . "<br>";
    // echo "GCP Location: " . $gcpLocation . "<br>";
    // echo "GCP Model ID: " . $gcpModelId . "<br>";

} catch (Exception $e) {
    // จัดการข้อผิดพลาดในการตั้งค่า
    // ในสภาพแวดล้อมจริง ควรจะ log ข้อผิดพลาดนี้และแสดงข้อความที่เป็นมิตรกับผู้ใช้
    error_log("Application configuration error: " . $e->getMessage());
    die("Application setup failed. Please contact support. Error: " . $e->getMessage());
}

// ...existing code...
// โค้ดส่วนที่เหลือของ index.php ที่ใช้ $gcpStorageClient หรือค่าอื่นๆ
// --------------------------------
// ...existing code...
require_once 'vendor/autoload.php';

// และหลังจากตั้งค่าอื่นๆ ที่จำเป็น
//--- ขาดอะไรไปหรือเปล่า
try {

    $appConfig = new Banrukcom\AppConfig();
    print var_dump($appConfig);

    $projectId = $appConfig->getProjectId();
    $location = $appConfig->getLocation();
    $modelId = $appConfig->getModelId();
    $storageClient = $appConfig->getStorageClient();

    // ตอนนี้คุณสามารถใช้ $projectId, $location, $modelId และ $storageClient ได้แล้ว
    // เช่น:
    // echo "Project ID: " . $projectId . "<br>";
    // echo "Location: " . $location . "<br>";
    // echo "Model ID: " . $modelId . "<br>";

} catch (Exception $e) {
    // จัดการข้อผิดพลาดในการตั้งค่า
    error_log("Application configuration error: " . $e->getMessage());
    // อาจจะแสดงหน้า error หรือข้อความแจ้งผู้ใช้
    die("Application setup failed. Please contact support.");
}

// ...existing code...