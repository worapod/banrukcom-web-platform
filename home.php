<?php
session_start();
date_default_timezone_set('Asia/Bangkok');
define('ROOT_FLIE', str_replace('\\', '/', dirname(__FILE__)) . '/');
define('ROOT_PATH', str_replace('\\', '/', dirname(__DIR__)) . '/');
require_once("frm-require.php");
require_once("function-math.php");
/*
$sample_test = decode256_distination($key_after,$key_before);
print var_dump($sample_test);
*/
$db = new Database(DB_HOST, DB_USER, DB_PASS, DB_NAME); // สร้างการเชื่อมต่อฐานข้อมูลเพียงครั้งเดียว
Auth::init($db->getConnection());

if (isset($_GET['page']) && $_POST['page'] == "Auth") {
    $page = $_GET['page'] ?? 'login';
} else {
    $page = $_GET['page'] ?? 'login';
}

if (isset($_GET['SOS']) && $_POST['SOS'] == "") {
    /// --- กำลังวางแผนพัฒนา API REST
    $action = $_GET['SOS'] ?? 'un';
}

switch ($page) {
    case 'admin':
        if (isset($_SESSION['user_id'])) { // ---- ตรวจสอย สิทธิและใช้ MENUADMINS
            if($_SESSION['role']==="Admin"){

                    $controller = new AdminController($db);
                    $action = $_GET['action'] ?? 'dashboard';

                    switch ($action) {  // ----- action 13.
                        case 'gemini_google':
                            $controller->manageAI_Agent();
                            break;
                        case 'dashboard':
                            $controller->dashboard();
                            break;
                        case 'travel_types':
                            $controller->manageTravelTypes();
                            break;
                        case 'locations':
                            $controller->manageLocations();
                            break;
                        case 'routes':
                            $controller->manageRoutes();
                            break;
                        case 'pricing':
                            $controller->managePricing();
                            break;
                        case 'pricing2save':
                            $controller->managePricesToSave();
                            break;
                        case 'users':
                            $controller->manageUsers();
                            break;
                        case 'pricing2process':
                            $controller->savePrice_process();
                            break;                                             
                        case 'booking_history':
                            $controller->manageBookingHistory();
                            break;
                        case 'logs':
                            $controller->manageLogsHistory();
                            break;
                        case 'agencies':
                            $controller->manageAgencies();
                            break;                            
                        default:
                            $controller->dashboard();
                            break;
                    } 
                    // "Admin Dashboard (Placeholder for action: {$action})"; // Placeholder ชั่วคราว 
                }
            }
        else{
            require_once VIEWS_PATH . '/auth/login.php';
            break;
        }
 
    case 'agency':
        // ---- ตรวจสอยสิทธิและใช้ agency ---------------->
        if (isset($_SESSION['user_id'])) {  // ---- ตรวจสอย สิทธิและใช้ MENUADMINS
            if($_SESSION['role']==="Agency"){

                    $controller = new AgencyController($db); // <--- สร้าง instance ของ AgencyController
                    $action = $_GET['action'] ?? 'booking_open';    

            // -------------------------->  เพิ่ม switch case สำหรับ Agency actions // BookingToSave
                    switch ($action) {
                        /*
                        case 'booking_Model':
                            $controller->booking_Framework();
                            break;     */

                        case 'booking_open':
                            $controller->profile();
                            break;

                        case 'booking_screen':
                            $controller->bookingScreen();
                        break;
                        
                        case 'booking_history':
                            $controller->bookingHistory();
                            break;
                        case 'process':
                                $controller->processNewBooking();
                                //-------------- detail
                        break;  
                        default:
                            $controller->bookingPricing();
                        break;
                    }
            }            
        }else{
            require_once VIEWS_PATH . '/auth/login.php';
            break;
        }

    case 'error':
        $controller = new AuthController($db);  // สร้าง instance ของ AuthController
        $controller->error_From();                   // เรียก method login (ซึ่งจะเรียก showLoginForm ภายใน)        
        break;

    case 'Auth':
        $controller = new AuthController($db);  // สร้าง instance ของ AuthController
        $controller->login();                   // เรียก method login (ซึ่งจะเรียก showLoginForm ภายใน)    
        break;

    case 'login':
        if (isset($_SESSION['user_id'])) { // ---- ตรวจสอบ สิทธิและใช้ MENUADMINS
            if(isset($_SESSION['role'])){
                define('CHECKADMINS',strtolower($_SESSION['role']));
                header('Location:index.php?page='.CHECKADMINS);                
            }            
        }
        else {

            include VIEWS_PATH . 'auth/login.php';
        }
    break;

    
    case 'logout':  // ถ้าผู้ใช้ต้องการ Logout
        $controller = new AuthController($db); // สร้าง instance ของ AuthController
        $controller->logout(); // เรียก method logout
        include VIEWS_PATH . 'auth/login.php';
    break;

    // ------------------------------

     case 'gemini_google':

        if (isset($_SESSION['user_id'])) { // ---- ตรวจสอย สิทธิและใช้ MENUADMINS
            if($_SESSION['role']==="Admin"){

        $checkIP_add = ($_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN');
        
         if (isset($_GET['SOS'])) {
            $decode_method = base64_decode($_GET['SOS']);
            $checkAuthentication =  explode('::',$decode_method);

            $name_Auth = htmlspecialchars(trim($checkAuthentication['0'] ?? ''));
            $word_Auth = htmlspecialchars(trim($checkAuthentication['1'] ?? ''));
            $controller = new AdminController($db);
            $controller->manageAI_Agent();

         }

        if ($action === 'admin') {
            $controller = new AuthController($db);  
            $controller->login_api_rest();                   // เรียก method login (ซึ่งจะเรียก showLoginForm ภายใน)    
        }

    
        $action = $_GET['action'] ?? 'un';
        if ($action === 'get_locations_excluding') {
            if (!Auth::isAdmin() || (!Auth::isLoggedIn())) {
                http_response_code(401); // Unauthorized
                echo json_encode(['error' => 'Authentication required.']);
                Auth::warning("Unauthorized API access attempt to 'get_locations_excluding'. IP: " . ($checkIP_add));
                exit();
            }

            $controller = new AdminController($db); 
            $controller->getLocationsExcluding();
            
        } else {
            http_response_code(404); // Not Found
            echo json_encode(['error' => 'API endpoint not found.']);
            Auth::warning("Unauthorized API access attempt to '404'. IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'));
            exit();
        }

    }
}
        
        
    break;

    default:
        if (isset($_SESSION['user_id'])) { // ---- ตรวจสอย สิทธิและใช้ MENUADMINS
            if(isset($_SESSION['role'])){
                header('Location:index.php?page='.MENUADMINS);                
            }            
        }
        else {
            include VIEWS_PATH . '/auth/login.php';
        }
        break;
} // --- end switch
