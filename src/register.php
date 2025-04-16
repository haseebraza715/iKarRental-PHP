<?php
require_once 'storage.php';
session_start();


$usersStorage = new Storage(new JsonIO(__DIR__ . '/../data/users.json'));

$error = null;
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($fullName) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($usersStorage->findOne(['email' => $email])) {
        $error = "Email is already registered.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $usersStorage->add([
            'id' => uniqid(), 
            'full_name' => $fullName,
            'email' => $email,
            'password' => $hashedPassword,
            'is_admin' => false
        ]);
        $success = true; 
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - iKarRental</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>iKarRental</h1>
    <nav>
        <a href="index.php">Home</a>
    </nav>
    </header>
    <main>
        <div class="register-form-container">
            <h2>Register</h2>
            <p>Create an account to book cars and manage your profile.</p>
            <?php if ($success): ?>
                <p class="success">Registration successful! <a href="login.php">Log in here</a>.</p>
            <?php else: ?>
                <?php if ($error): ?>
                    <p class="error"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>
                <form action="" method="POST">
                    <label for="full_name">Full Name:</label>
                    <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>

                    <label for="email">Email Address:</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>

                    <button type="submit">Register</button>
                </form>
                <br>
                <p>Already have an account? <a href="login.php">Log in here</a>.</p>

            <?php endif; ?>
        </div>
    </main>
</body>
</html>
