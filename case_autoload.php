<?php
/** step 0 
 * --- vlucas/phpdotenv  
 *
 */


define('ENV_PATH', __DIR__.'/conn');
/**
 * Step 1: ALWAYS load the Composer autoloader first.
 * This is the most critical step.
 */
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Step 2: Load the .env file using the full, absolute path.
 * This is the most reliable way and removes any doubt about the path.
 *  ในไฟล์ case_autoload.php เดียวกันนี้ คุณได้มีการเรียกใช้ไลบรารี vlucas/phpdotenv (ซึ่งเป็นไลบรารีมาตรฐานที่ใช้กันแพร่หลายในการโหลดไฟล์ .env) อยู่แล้วในส่วน:
 */

try {
    $dotenv = Dotenv\Dotenv::createImmutable(ENV_PATH);
    $dotenv->load();
} catch (Exception $e) {
    die("Error: Could not load the .env file. Please check the path. Details: " . $e->getMessage());
}


/**
 * Step 3: Use the loaded variable to connect to Google Cloud Storage.
 */
use Google\Cloud\Storage\StorageClient;

echo "<h1>GCP Connection Test</h1>";

try {
    // Check if the key exists before using it
    if (empty($_ENV['GCP_KEY_FILE_PATH'])) {
        throw new Exception("GCP_KEY_FILE_PATH is not defined in your .env file.");
    }
        // -- คำสั่ง เรียก Class  $storage = new StorageClient 
        // --  'keyFilePath' => $_ENV['GCP_KEY_FILE_PATH']
        // --- 'keyFilePath' นำไปใช้ที่ไหน ??
    $storage = new StorageClient([
        'keyFilePath' => $_ENV['GCP_KEY_FILE_PATH']
    ]);

    echo "<p>✅ Connection Successful!</p>";
    echo "<h2>Listing Storage Buckets:</h2>";

    $buckets = $storage->buckets();
    if (iterator_count($buckets) > 0) {
        echo "<ul>";
        foreach ($buckets as $bucket) {
            printf('<li>%s</li>', $bucket->name());
        }
        echo "</ul>";
    } else {
        echo "<p>No buckets found. This is a successful test!</p>";
    }

} catch (Exception $e) {
    echo "<h3>❌ An error occurred!</h3>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
}

/*
ไลบรารี vlucas/phpdotenv ทำหน้าที่นี้ได้ดีกว่าและปลอดภัยกว่ามาก
การจัดการที่ไม่เหมาะสม

การใช้ htmlspecialchars() กับ key และ value ของตัวแปรสภาพแวดล้อมอาจทำให้ค่าบางอย่างผิดเพี้ยนไปได้ (เช่น ถ้าค่ามีเครื่องหมาย < หรือ >) ซึ่งโดยปกติแล้วไม่จำเป็นสำหรับการโหลดตัวแปรสภาพแวดล้อม
มีคำสั่ง print_r($_ENV); และ print_r($_ENV[$key]); อยู่ในลูป ซึ่งจะพิมพ์ข้อมูลจำนวนมากออกไปที่หน้าจอทุกครั้งที่โหลดตัวแปร ทำให้เกิด output ที่ไม่ต้องการและอาจเปิดเผยข้อมูลได้
คำแนะนำ:

คุณควรลบคลาส DotEnv_check_Environment และโค้ดที่เรียกใช้งานมันออกไปได้เลยครับ
และใช้ vlucas/phpdotenv ที่คุณได้ require_once __DIR__ . '/vendor/autoload.php';
และเรียกใช้ Dotenv\Dotenv::createImmutable(ENV_PATH)->load();
เพียงอย่างเดียวก็เพียงพอแล้วครับ
** การใช้ไลบรารีมาตรฐานจะช่วยให้โค้ดของคุณมีความน่าเชื่อถือ ปลอดภัย และบำรุงรักษาง่ายขึ้นครับ
*/

/*
หากคุณได้กำหนด GCP_PROJECT_ID, GCP_LOCATION, และ GCP_MODEL_ID เป็น Secrets หรือ Environment Variables บน Cloud Run แล้ว คุณไม่จำเป็นต้องใช้คำสั่งพิเศษใดๆ ในโค้ด PHP เพื่อ "ดึง" ค่าเหล่านั้นครับ

Cloud Run จะทำการ inject (ฉีด) ค่าเหล่านี้เข้าไปในสภาพแวดล้อมของคอนเทนเนอร์โดยอัตโนมัติ ทำให้คุณสามารถเข้าถึงได้โดยตรงผ่านตัวแปร Global ของ PHP:

$_ENV Superglobal:
นี่เป็นวิธีที่นิยมและแนะนำที่สุดในการเข้าถึงตัวแปรสภาพแวดล้อมใน PHP โดยเฉพาะเมื่อคุณใช้ไลบรารีอย่าง phpdotenv (ซึ่งจะโหลดค่าจาก .env และใส่ลงใน $_ENV ด้วย)

// filepath: (ในไฟล์ PHP ของคุณ เช่น AppConfig.php หรือ Controller)
// ...existing code...
$projectId = $_ENV['GCP_PROJECT_ID'] ?? null;
$location = $_ENV['GCP_LOCATION'] ?? null;
$modelId = $_ENV['GCP_MODEL_ID'] ?? null;

if ($projectId === null) {
    // จัดการกรณีที่ตัวแปรไม่ได้ถูกกำหนด
    throw new Exception("GCP_PROJECT_ID is not set.");
}

*/
