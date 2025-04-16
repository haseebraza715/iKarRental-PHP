<?php
require_once 'storage.php';

header('Content-Type: application/json');

$carId = $_GET['car_id'] ?? null;

if (!$carId) {
    echo json_encode(['error' => 'Car ID is required']);
    exit;
}

$bookingsStorage = new Storage(new JsonIO(__DIR__ . '/../data/bookings.json'));

try {
    $bookings = $bookingsStorage->findMany(function ($booking) use ($carId) {
        return $booking['car_id'] === $carId;
    });

    $unavailableDates = [];
    foreach ($bookings as $booking) {
        $startDate = strtotime($booking['start_date']);
        $endDate = strtotime($booking['end_date']);

        while ($startDate <= $endDate) {
            $unavailableDates[] = date('Y-m-d', $startDate);
            $startDate = strtotime('+1 day', $startDate);
        }
    }

    echo json_encode(['unavailable_dates' => $unavailableDates]);
} catch (Exception $e) {
    error_log('Error fetching unavailable dates: ' . $e->getMessage());
    echo json_encode(['error' => 'An unexpected error occurred. Please try again later.']);
    exit;
}
