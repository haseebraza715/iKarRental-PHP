<?php
require_once 'storage.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$carId = $_POST['car_id'] ?? null;
$startDate = $_POST['start_date'] ?? null;
$endDate = $_POST['end_date'] ?? null;
$userId = $_SESSION['user']['id'] ?? null;

if (!$carId || !$startDate || !$endDate || strtotime($startDate) > strtotime($endDate)) {
    header("Location: car-details.php?id={$carId}&error=Invalid booking details");
    exit;
}

$carsStorage = new Storage(new JsonIO(__DIR__ . '/../data/cars.json'));
$bookingsStorage = new Storage(new JsonIO(__DIR__ . '/../data/bookings.json'));

$car = $carsStorage->findById($carId);
if (!$car) {
    header("Location: car-details.php?error=Car not found");
    exit;
}

$conflict = false;
foreach ($bookingsStorage->findAll() as $booking) {
    if (
        $booking['car_id'] === $carId &&
        !(strtotime($endDate) < strtotime($booking['start_date']) ||
          strtotime($startDate) > strtotime($booking['end_date']))
    ) {
        $conflict = true;
        break;
    }
}

if ($conflict) {
    $error = "This car is already booked for the selected dates.";
} else {
    // Save booking
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

    $success = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>iKarRental</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <main>
        <div class="booking-confirmation">
            <?php if (isset($success)): ?>
                <h2>Booking Confirmed!</h2>
                <div class="success-message">
                    <p><strong>Car:</strong> <?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></p>
                    <p><strong>From:</strong> <?= htmlspecialchars($startDate) ?></p>
                    <p><strong>To:</strong> <?= htmlspecialchars($endDate) ?></p>
                    <p><strong>Total Days:</strong> <?= $totalDays ?></p>
                    <p><strong>Total Price:</strong> <?= number_format($totalPrice) ?> HUF</p>
                </div>
                <a href="profile.php" class="btn">View Your Bookings</a>
            <?php elseif (isset($error)): ?>
                <h2>Booking Failed</h2>
                <p class="error-message"><?= htmlspecialchars($error) ?></p>
                <a href="car-details.php?id=<?= htmlspecialchars($carId) ?>" class="btn">Back to Car Details</a>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
