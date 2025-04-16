<?php
require_once 'storage.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$bookingsStorage = new Storage(new JsonIO(__DIR__ . '/../data/bookings.json'));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    $bookingId = $_POST['booking_id'];
    
    $booking = $bookingsStorage->findById($bookingId);
    if (!$booking) {
        $_SESSION['error'] = "Booking not found.";
        header("Location: profile.php");
        exit;
    }

    if ($_SESSION['user']['is_admin'] || $booking['user_id'] === $_SESSION['user']['id']) {
        $bookingsStorage->delete($bookingId);
        $bookingsStorage->save(); 

        $_SESSION['success'] = "Booking canceled successfully.";
    } else {
        $_SESSION['error'] = "You are not authorized to cancel this booking.";
    }
} else {
    $_SESSION['error'] = "Invalid request.";
}

header("Location: profile.php");
exit;
