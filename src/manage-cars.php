<?php
require_once 'storage.php';
session_start();

if (!isset($_SESSION['user']) || !$_SESSION['user']['is_admin']) {
    header("Location: login.php");
    exit;
}

$carsStorage = new Storage(new JsonIO(__DIR__ . '/../data/cars.json'));

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $brand = trim($_POST['brand']);
    $model = trim($_POST['model']);
    $year = intval($_POST['year']);
    $transmission = trim($_POST['transmission']);
    $fuel_type = trim($_POST['fuel_type']);
    $passengers = intval($_POST['passengers']);
    $daily_price_huf = intval($_POST['daily_price_huf']);
    $image = trim($_POST['image']);

    if (empty($brand) || empty($model) || empty($year) || empty($transmission) || 
        empty($fuel_type) || empty($passengers) || empty($daily_price_huf) || empty($image)) {
        $error = "All fields are required.";
    } elseif (!filter_var($image, FILTER_VALIDATE_URL)) {
        $error = "Invalid image URL.";
    } else {
        $carData = [
            'brand' => $brand,
            'model' => $model,
            'year' => $year,
            'transmission' => $transmission,
            'fuel_type' => $fuel_type,
            'passengers' => $passengers,
            'daily_price_huf' => $daily_price_huf,
            'image' => $image,
        ];

        if ($id) {
            $existingCar = $carsStorage->findById($id);
            if ($existingCar) {
                $carsStorage->update($id, array_merge($existingCar, $carData));
                $success = "Car updated successfully.";
            } else {
                $error = "Car not found.";
            }
        } else {
            $carsStorage->add($carData);
            $success = "Car added successfully.";
        }
    }
}

if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'delete') {
    $id = $_GET['id'];
    if ($carsStorage->findById($id)) {
        $carsStorage->delete($id);
        $success = "Car deleted successfully.";
    } else {
        $error = "Car not found.";
    }
}

$editCar = null;
if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'edit') {
    $id = $_GET['id'];
    $editCar = $carsStorage->findById($id);
}

$cars = $carsStorage->findAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iKarRental - Manage Cars</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Manage Cars</h1>
        <nav>
            <a href="admin.php">Admin Dashboard</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <main>
        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php elseif ($success): ?>
            <p class="success"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <section class="form-section">
            <h2><?= $editCar ? "Edit Car" : "Add New Car" ?></h2>
            <form action="" method="POST" class="form-container">
                <input type="hidden" name="id" value="<?= htmlspecialchars($editCar['id'] ?? '') ?>">
                <label for="brand">Brand:</label>
                <input type="text" id="brand" name="brand" value="<?= htmlspecialchars($editCar['brand'] ?? '') ?>" required>

                <label for="model">Model:</label>
                <input type="text" id="model" name="model" value="<?= htmlspecialchars($editCar['model'] ?? '') ?>" required>

                <label for="year">Year:</label>
                <input type="number" id="year" name="year" value="<?= htmlspecialchars($editCar['year'] ?? '') ?>" required>

                <label for="transmission">Transmission:</label>
                <input type="text" id="transmission" name="transmission" value="<?= htmlspecialchars($editCar['transmission'] ?? '') ?>" required>

                <label for="fuel_type">Fuel Type:</label>
                <input type="text" id="fuel_type" name="fuel_type" value="<?= htmlspecialchars($editCar['fuel_type'] ?? '') ?>" required>

                <label for="passengers">Passengers:</label>
                <input type="number" id="passengers" name="passengers" value="<?= htmlspecialchars($editCar['passengers'] ?? '') ?>" required>

                <label for="daily_price_huf">Daily Price (HUF):</label>
                <input type="number" id="daily_price_huf" name="daily_price_huf" value="<?= htmlspecialchars($editCar['daily_price_huf'] ?? '') ?>" required>

                <label for="image">Image URL:</label>
                <input type="url" id="image" name="image" value="<?= htmlspecialchars($editCar['image'] ?? '') ?>" required>

                <button type="submit"><?= $editCar ? "Update Car" : "Add Car" ?></button>
            </form>
        </section>

        <section>
            <h2>Car List</h2>
            <?php if (empty($cars)): ?>
                <p>No cars available.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Brand</th>
                            <th>Model</th>
                            <th>Year</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cars as $car): ?>
                            <tr>
                                <td><?= htmlspecialchars($car['brand']) ?></td>
                                <td><?= htmlspecialchars($car['model']) ?></td>
                                <td><?= htmlspecialchars($car['year']) ?></td>
                                <td>
                                    <a href="?action=edit&id=<?= urlencode($car['id']) ?>">Edit</a>
                                    <a href="?action=delete&id=<?= urlencode($car['id']) ?>" onclick="return confirm('Are you sure?')">Delete</a>
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
