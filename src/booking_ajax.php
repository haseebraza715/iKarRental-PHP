<?php
require_once 'storage.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'You must be logged in to book a car.']);
    exit;
}

$carId = $_POST['car_id'] ?? null;
$startDate = $_POST['start_date'] ?? null;
$endDate = $_POST['end_date'] ?? null;
$userId = $_SESSION['user']['id'] ?? null;

if (!$carId || !$startDate || !$endDate || strtotime($startDate) > strtotime($endDate)) {
    echo json_encode(['success' => false, 'error' => 'Invalid booking details.']);
    exit;
}

$carsStorage = new Storage(new JsonIO(__DIR__ . '/../data/cars.json'));
$bookingsStorage = new Storage(new JsonIO(__DIR__ . '/../data/bookings.json'));

$car = $carsStorage->findById($carId);
if (!$car) {
    echo json_encode(['success' => false, 'error' => 'Car not found.']);
    exit;
}


foreach ($bookingsStorage->findAll() as $booking) {
    if (
        $booking['car_id'] === $carId &&
        !(strtotime($endDate) < strtotime($booking['start_date']) ||
          strtotime($startDate) > strtotime($booking['end_date']))
    ) {
        echo json_encode(['success' => false, 'error' => 'This car is already booked for the selected dates.']);
        exit;
    }
}


$totalDays = (strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24) + 1;
$totalPrice = $totalDays * $car['daily_price_huf'];

$bookingsStorage->add([
    'id' => uniqid(),
    'car_id' => $carId,
    'user_id' => $userId,
    'start_date' => $startDate,
    'end_date' => $endDate,
]);
$bookingsStorage->save();

echo json_encode([
    'success' => true,
    'message' => 'Booking confirmed!',
    'car_name' => $car['brand'] . ' ' . $car['model'],
    'start_date' => $startDate,
    'end_date' => $endDate,
    'total_price' => number_format($totalPrice),
]);
exit;
