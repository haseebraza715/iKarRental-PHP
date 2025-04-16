<?php
require_once 'storage.php';
session_start();

$carsStorage = new Storage(new JsonIO(__DIR__ . '/../data/cars.json'));
$bookingsStorage = new Storage(new JsonIO(__DIR__ . '/../data/bookings.json'));

$cars = $carsStorage->findAll();

$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;
$seats = isset($_GET['seats']) && $_GET['seats'] !== '' ? (int)$_GET['seats'] : null;
$gearType = $_GET['gear_type'] ?? null;
$minPrice = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (int)$_GET['min_price'] : null;
$maxPrice = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (int)$_GET['max_price'] : null;

if ($startDate || $endDate || $seats !== null || $gearType || $minPrice !== null || $maxPrice !== null) {
    $filteredCars = [];
    foreach ($cars as $car) {
        $isAvailable = true;

        if ($startDate && $endDate) {
            $bookings = $bookingsStorage->findMany(function ($booking) use ($car) {
                return (string)$booking['car_id'] === (string)$car['id'];
            });

            foreach ($bookings as $booking) {
                if (
                    strtotime($booking['start_date']) <= strtotime($endDate) &&
                    strtotime($booking['end_date']) >= strtotime($startDate)
                ) {
                    $isAvailable = false;
                    break;
                }
            }
        }

        if ($seats !== null && $car['passengers'] < $seats) {
            $isAvailable = false;
        }

        if ($gearType && strtolower($car['transmission']) !== strtolower($gearType)) {
            $isAvailable = false;
        }

        if ($minPrice !== null && $car['daily_price_huf'] < $minPrice) {
            $isAvailable = false;
        }
        if ($maxPrice !== null && $car['daily_price_huf'] > $maxPrice) {
            $isAvailable = false;
        }

        if ($isAvailable) {
            $filteredCars[] = $car;
        }
    }

    $cars = $filteredCars;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iKarRental - Browse Cars</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

</head>
<body>
<header class="index-header">
    <div class="container">
        <div class="logo">
            <h1>iKarRental</h1>
        </div>
        <nav class="nav">
            <?php if (isset($_SESSION['user'])): ?>
                <a href="profile.php">Welcome, <?= htmlspecialchars($_SESSION['user']['full_name']) ?></a>
                <a href="logout.php" class="btn btn-primary">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php" class="btn btn-primary">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main>
    <h2>Available Cars</h2>

    <form method="GET" action="" class="filter-form">
        <div class="filter-container">
            <div class="filter-item">
                <label for="start_date">From:</label>
                <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($_GET['start_date'] ?? '') ?>">
            </div>
            <div class="filter-item">
                <label for="end_date">Until:</label>
                <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($_GET['end_date'] ?? '') ?>">
            </div>

            <div class="filter-item">
                <label for="seats">Seats:</label>
                <input type="number" id="seats" name="seats" value="<?= htmlspecialchars($_GET['seats'] ?? '') ?>" min="1">
            </div>

            <div class="filter-item">
                <label for="gear_type">Gear Type:</label>
                <select id="gear_type" name="gear_type">
                    <option value="">Any</option>
                    <option value="Manual" <?= (isset($_GET['gear_type']) && $_GET['gear_type'] === 'Manual') ? 'selected' : '' ?>>Manual</option>
                    <option value="Automatic" <?= (isset($_GET['gear_type']) && $_GET['gear_type'] === 'Automatic') ? 'selected' : '' ?>>Automatic</option>
                </select>
            </div>

            <div class="filter-item">
                <label for="min_price">Price:</label>
                <input type="number" id="min_price" name="min_price" placeholder="Min" value="<?= htmlspecialchars($_GET['min_price'] ?? '') ?>">
                <span> - </span>
                <input type="number" id="max_price" name="max_price" placeholder="Max" value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>">
            </div>

            <div class="filter-item">
                <button type="submit" class="filter-btn">Filter</button>
            </div>
        </div>
    </form>

    <div class="car-list">
        <?php if (empty($cars)): ?>
            <p style="text-align: center; color: #555;">No cars are available for the selected criteria.</p>
        <?php else: ?>
            <?php foreach ($cars as $car): ?>
                <div class="car-item">
                    <img src="<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>">
                    <h3><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></h3>
                    <p>Transmission: <?= htmlspecialchars($car['transmission']) ?></p>
                    <p>Passengers: <?= htmlspecialchars($car['passengers']) ?></p>
                    <p>Daily Price: <?= htmlspecialchars($car['daily_price_huf']) ?> HUF</p>
                    <a href="car-details.php?id=<?= $car['id'] ?>">View Details</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
