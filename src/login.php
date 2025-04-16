<?php
require_once 'storage.php';
session_start();

$usersStorage = new Storage(new JsonIO(__DIR__ . '/../data/users.json'));

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "All fields are required.";
    } else {
        $user = $usersStorage->findOne(['email' => $email]);
        if (!$user || !password_verify($password, $user['password'])) {
            $error = "Invalid email or password.";
        } else {
            if (!isset($user['id']) || empty($user['id'])) {
                $user['id'] = uniqid();
                $usersStorage->update($user['email'], $user); 
                $usersStorage->save();
            }

            $_SESSION['user'] = [
                'id' => $user['id'],
                'full_name' => $user['full_name'],
                'email' => $user['email'],
                'is_admin' => $user['is_admin'] ?? false 
            ];

            header("Location: " . ($user['is_admin'] ? "admin.php" : "index.php"));
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - iKarRental</title>
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
        <div class="login-form-container">
            <h2>Login</h2>
            <p>Log in to access your account and book cars.</p>
            <?php if ($error): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <form action="" method="POST">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>

                <button type="submit">Log In</button>
            </form>
            <br>
            <p>Don't have an account? <a href="register.php">Register here</a>.</p>
        
        </div>
    </main>
</body>
</html>

