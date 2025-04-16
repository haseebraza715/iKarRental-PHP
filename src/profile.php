<?php
require_once 'storage.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$user['is_admin'] = $user['is_admin'] ?? false;
$user['id'] = $user['id'] ?? null;

$bookingsStorage = new Storage(new JsonIO(__DIR__ . '/../data/bookings.json'));
$carsStorage = new Storage(new JsonIO(__DIR__ . '/../data/cars.json'));

if ($user['is_admin']) {
    $bookings = $bookingsStorage->findAll();
} else {
    $bookings = $bookingsStorage->findMany(function ($booking) use ($user) {
        return $booking['user_id'] === $user['id'];
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - iKarRental</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>iKarRental</h1>
        <nav>
            <a href="index.php">Home</a>
            <?php if ($user['is_admin']): ?>
                <a href="admin.php">Admin Dashboard</a>
            <?php endif; ?>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <main>
        <div class="profile-container">
            <div class="profile-header">
                <h2>Welcome, <?= htmlspecialchars($user['full_name']) ?>!</h2>
                <p>Your reservations and account details are listed below.</p>
            </div>

            <div class="reservations-container">
                <h3>Your Reservations</h3>
                <?php if (empty($bookings)): ?>
                    <p>No reservations found.</p>
                <?php else: ?>
                    <div class="reservations-list">
                        <?php foreach ($bookings as $key => $booking): 
                            $car = $carsStorage->findById($booking['car_id']);
                            $bookingId = $booking['id'] ?? $key; // Use the key as ID if "id" is not present
                            ?>
                            <div class="reservation-item">
                                <h4><?= htmlspecialchars($car['brand'] . ' ' . $car['model'] ?? 'Deleted Car') ?></h4>
                                <p>Booking ID: <?= htmlspecialchars($bookingId) ?></p>
                                <p>From: <?= htmlspecialchars($booking['start_date'] ?? 'N/A') ?></p>
                                <p>To: <?= htmlspecialchars($booking['end_date'] ?? 'N/A') ?></p>
                                <p>Price per day: <?= htmlspecialchars($car['daily_price_huf'] ?? 'N/A') ?> HUF</p>
                                <form method="POST" action="delete-reservation.php">
                                    <input type="hidden" name="booking_id" value="<?= htmlspecialchars($bookingId) ?>">
                                    <button type="submit" class="delete-btn">Cancel Booking</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </main>
</body>
</html>
