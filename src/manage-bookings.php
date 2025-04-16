<?php
require_once 'storage.php';
session_start();

if (!isset($_SESSION['user']) || !$_SESSION['user']['is_admin']) {
    header("Location: login.php");
    exit;
}

$bookingsStorage = new Storage(new JsonIO(__DIR__ . '/../data/bookings.json'));
$carsStorage = new Storage(new JsonIO(__DIR__ . '/../data/cars.json'));
$usersStorage = new Storage(new JsonIO(__DIR__ . '/../data/users.json'));

$success = null;

if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'delete') {
    $id = $_GET['id'];
    if ($bookingsStorage->findById($id)) {
        $bookingsStorage->delete($id);
        $success = "Booking deleted successfully.";
    } else {
        $success = "Booking not found.";
    }
}

$bookings = $bookingsStorage->findAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iKarRental - Manage Bookings</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Manage Bookings</h1>
        <nav>
            <a href="admin.php">Admin Dashboard</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <main>
        <?php if ($success): ?>
            <p class="success"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <section>
            <h2>Booking List</h2>
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <?php
                            $car = $carsStorage->findById($booking['car_id']);
                            $user = isset($booking['user_id']) ? $usersStorage->findById($booking['user_id']) : null;
                            ?>
                            <tr>
                                <td><?= htmlspecialchars(($car['brand'] ?? 'Deleted') . ' ' . ($car['model'] ?? 'Car')) ?></td>
                                <td><?= htmlspecialchars($user['full_name'] ?? 'Guest/Deleted User') ?></td>
                                <td><?= htmlspecialchars($booking['start_date'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($booking['end_date'] ?? 'N/A') ?></td>
                                <td>
                                    <a href="?action=delete&id=<?= urlencode($booking['id']) ?>" 
                                       onclick="return confirm('Are you sure?')">Delete</a>
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
