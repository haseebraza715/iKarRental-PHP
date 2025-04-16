<?php
require_once 'storage.php';
session_start();

if (!isset($_SESSION['user']) || !$_SESSION['user']['is_admin']) {
    header("Location: login.php");
    exit;
}

$carsStorage = new Storage(new JsonIO(__DIR__ . '/../data/cars.json'));
$bookingsStorage = new Storage(new JsonIO(__DIR__ . '/../data/bookings.json'));
$usersStorage = new Storage(new JsonIO(__DIR__ . '/../data/users.json')); // For user details

if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'delete') {
    $id = $_GET['id'];
    $booking = $bookingsStorage->findById($id);
    if ($booking) {
        $bookingsStorage->delete($id);
        $bookingsStorage->save();
        $success = "Booking deleted successfully.";
    } else {
        $error = "Booking not found.";
    }
}

$cars = $carsStorage->findAll();
$bookings = $bookingsStorage->findAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iKarRental - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Admin Dashboard</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <main>
        <!-- Success/Error Messages -->
        <?php if (isset($success)): ?>
            <p class="success"><?= htmlspecialchars($success) ?></p>
        <?php elseif (isset($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <section>
            <h1>Manage Cars</h1>
            <br>
            <a href="manage-cars.php" class="btn">Add/Edit Cars</a>
            <?php if (empty($cars)): ?>
                <p>No cars available. Add some cars!</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Brand</th>
                            <th>Model</th>
                            <th>Year</th>
                            <th>Transmission</th>
                            <th>Passengers</th>
                            <th>Price (HUF)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cars as $car): ?>
                            <tr>
                                <td><?= htmlspecialchars($car['brand']) ?></td>
                                <td><?= htmlspecialchars($car['model']) ?></td>
                                <td><?= htmlspecialchars($car['year']) ?></td>
                                <td><?= htmlspecialchars($car['transmission']) ?></td>
                                <td><?= htmlspecialchars($car['passengers']) ?></td>
                                <td><?= htmlspecialchars($car['daily_price_huf']) ?></td>
                                <td>
                                    <a href="manage-cars.php?action=edit&id=<?= urlencode($car['id']) ?>">Edit</a>
                                    <a href="manage-cars.php?action=delete&id=<?= urlencode($car['id']) ?>" onclick="return confirm('Are you sure?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <section>
            <h2>Manage Bookings</h2>
            <?php if (empty($bookings)): ?>
                <p>No bookings available.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Car</th>
                            <th>User</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Total Price (HUF)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $id => $booking): ?>
                            <?php
                            $car = $carsStorage->findById($booking['car_id']);
                            $user = $usersStorage->findById($booking['user_id'] ?? null);
                            $days = (strtotime($booking['end_date']) - strtotime($booking['start_date'])) / (60 * 60 * 24);
                            $totalPrice = isset($car['daily_price_huf']) ? $days * $car['daily_price_huf'] : 'N/A';
                            ?>
                            <tr>
                                <td><?= htmlspecialchars(($car['brand'] ?? 'Deleted') . ' ' . ($car['model'] ?? 'Car')) ?></td>
                                <td><?= htmlspecialchars($user['full_name'] ?? 'Guest/Deleted User') ?></td>
                                <td><?= htmlspecialchars($booking['start_date'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($booking['end_date'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($totalPrice) ?></td>
                                <td>
                                    <a href="?action=delete&id=<?= urlencode($id) ?>" onclick="return confirm('Are you sure?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
