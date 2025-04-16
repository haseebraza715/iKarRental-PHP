<?php
require_once 'storage.php';
session_start();

$carsStorage = new Storage(new JsonIO(__DIR__ . '/../data/cars.json'));

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Car ID is missing.");
}

$carId = $_GET['id'];
$car = $carsStorage->findById($carId);

if (!$car) {
    die("Car not found.");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Details</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>
<body>
    <header>
        <h1>iKarRental</h1>
        <nav>
            <a href="index.php">Home</a>
            <?php if (isset($_SESSION['user'])): ?>
                <a href="profile.php">Profile</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
        <div class="car-details">
            <img src="<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>">
            <div class="info">
                <h2><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></h2>
                <ul>
                    <li>Brand: <?= htmlspecialchars($car['brand']) ?></li>
                    <li>Model: <?= htmlspecialchars($car['model']) ?></li>
                    <li>Year: <?= htmlspecialchars($car['year']) ?></li>
                    <li>Transmission: <?= htmlspecialchars($car['transmission']) ?></li>
                    <li>Fuel Type: <?= htmlspecialchars($car['fuel_type']) ?></li>
                    <li>Passengers: <?= htmlspecialchars($car['passengers']) ?></li>
                    <li>Daily Price: <?= htmlspecialchars($car['daily_price_huf']) ?> HUF</li>
                </ul>
            </div>
        </div>

        <div class="booking-form">
            <h3>Book This Car</h3>
            <?php if (!isset($_SESSION['user'])): ?>
                <p>Please <a href="login.php">log in</a> to book this car.</p>
            <?php else: ?>
                <form id="bookingForm">
                    <input type="hidden" name="car_id" value="<?= htmlspecialchars($carId) ?>">
                    <label for="start_date">Start Date:</label>
                    <input type="text" id="start_date" name="start_date" required>
                    <label for="end_date">End Date:</label>
                    <input type="text" id="end_date" name="end_date" required>
                    <button type="submit">Book Now</button>
                </form>
            <?php endif; ?>
        </div>
        <a href="index.php" class="back-button">Back to Homepage</a>
    </main>

    <!-- Modal for feedback -->
    <div id="modal" class="modal hidden">
        <div class="modal-content">
            <span id="modal-close" class="close">&times;</span>
            <h2 id="modal-title"></h2>
            <p id="modal-message"></p>
            <button id="modal-action" class="btn">OK</button>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const carId = "<?= htmlspecialchars($carId) ?>";

            fetch(`get-unavailable-dates.php?car_id=${carId}`)
                .then((response) => response.json())
                .then((data) => {
                    if (data.error) {
                        console.error(data.error);
                        return;
                    }

                    const unavailableDates = data.unavailable_dates;

                    flatpickr("#start_date", {
                        disable: unavailableDates,
                        dateFormat: "Y-m-d",
                        locale: {
                            firstDayOfWeek: 1 
                        },
                        onChange: function (selectedDates) {
                            const minDate = selectedDates[0];
                            flatpickr("#end_date", {
                                disable: unavailableDates,
                                dateFormat: "Y-m-d",
                                minDate: minDate,
                            });
                        },
                    });

                    flatpickr("#end_date", {
                        disable: unavailableDates,
                        dateFormat: "Y-m-d",
                    });
                })
                .catch((error) => console.error("Error fetching unavailable dates:", error));

            const bookingForm = document.getElementById("bookingForm");
            const modal = document.getElementById("modal");
            const modalTitle = document.getElementById("modal-title");
            const modalMessage = document.getElementById("modal-message");
            const modalAction = document.getElementById("modal-action");
            const modalClose = document.getElementById("modal-close");

            bookingForm.addEventListener("submit", function (e) {
                e.preventDefault();

                const formData = new FormData(bookingForm);

                fetch("booking_ajax.php", {
                    method: "POST",
                    body: formData,
                })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.success) {
                            modalTitle.textContent = "Booking Confirmed!";
                            modalMessage.innerHTML = `
                                <p><strong>Car:</strong> ${data.car_name}</p>
                                <p><strong>From:</strong> ${data.start_date}</p>
                                <p><strong>To:</strong> ${data.end_date}</p>
                                <p><strong>Total Price:</strong> ${data.total_price} HUF</p>
                            `;
                        } else {
                            modalTitle.textContent = "Booking Failed";
                            modalMessage.textContent = data.error;
                        }

                        modal.classList.remove("hidden");
                    })
                    .catch((error) => {
                        modalTitle.textContent = "Error";
                        modalMessage.textContent = "Something went wrong. Please try again.";
                        modal.classList.remove("hidden");
                    });
            });

            modalAction.addEventListener("click", () => modal.classList.add("hidden"));
            modalClose.addEventListener("click", () => modal.classList.add("hidden"));
        });
    </script>

</body>
</html>
