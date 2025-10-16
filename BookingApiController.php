<?php

// ตัวอย่างใน PHP 8 Controller (e.g., app/Controllers/BookingApiController.php)
// -- booking_agency_authLog\classes\services\BookingApiController.php


class BookingApiController extends BaseController {
    public function createBooking() {
        // Get JSON raw body
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->responseJson(['success' => false, 'message' => 'Invalid JSON'], 400);
        }

        // Example: Data validation and saving using your new Models
        $bookingModel = new BookingModel();
        try {
            $newBookingId = $bookingModel->saveBooking($data);
            return $this->responseJson(['success' => true, 'booking_id' => $newBookingId], 201);
        } catch (Exception $e) {
            return $this->responseJson(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}

?>