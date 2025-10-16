
<?php
require_once __DIR__ .  '/vendor/autoload.php';
require_once __DIR__ .  '/conn/config.php';              // ไฟล์ config.php
require_once __DIR__ .  '/conn/define_config.php';              // ไฟล์ config.php

require_once __DIR__ .  '/classes/Database.php';    // Class สำหรับการเชื่อมต่อฐานข้อมูล
require_once __DIR__ .  '/classes/Auth.php';        // Class สำหรับการจัดการ Authentication และ Authorization

require_once __DIR__ .  '/classes/controllers/AuthController.php'; // Class สำหรับ Auth Controller
require_once __DIR__ .  '/classes/controllers/AdminController.php'; 
require_once __DIR__ .  '/classes/controllers/AgencyController.php';

require_once __DIR__ .  '/classes/models/User.php'; // Class สำหรับ User Model (จัดการตาราง users)
require_once __DIR__ .  '/classes/models/TravelType.php'; // <--- เพิ่มบรรทัดนี้
require_once __DIR__ .  '/classes/models/Location.php'; // <--- เพิ่มบรรทัดนี้
require_once __DIR__ .  '/classes/models/Route.php';
require_once __DIR__ .  '/classes/models/Price.php'; // <--- เพิ่มบรรทัดนี้
require_once __DIR__ .  '/classes/models/booking.php'; // <--- รวม bookingController เข้าไว้เรียบร้อย 

require_once __DIR__ .  '/classes/services/PriceService.php'; // <--- เพิ่มบรรทัดนี้

?>