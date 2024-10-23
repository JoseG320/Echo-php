<?php
require __DIR__ . '/vendor/autoload.php';
$db = new PDO('mysql:host=localhost;dbname=echo-db;charset=utf8', 'root', '');
$auth = new \Delight\Auth\Auth($db);

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $auth->login($email, $password);
        // Redirect to dashboard after successful login
        header("Location: home.php");
    }
    catch (\Delight\Auth\InvalidEmailException $e) {
        echo 'Wrong email address';
    }
    catch (\Delight\Auth\InvalidPasswordException $e) {
        echo 'Wrong password';
    }
    catch (\Delight\Auth\EmailNotVerifiedException $e) {
        echo 'Email not verified';
    }
    catch (\Delight\Auth\TooManyRequestsException $e) {
        echo 'Too many login attempts. Try again later.';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark" />
    <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.pink.min.css"
    >
    <title>Login</title>
</head>
<body class="container">
    <header>
        <?php include "./pages/header.php"?>
    </header>
    <h2>Login</h2>
    <form method="POST" action="">
        Email: <input type="email" name="email" required><br>
        Password: <input type="password" name="password" required><br>
        <button type="submit">Login</button>
        <a href="register.php" class="secondary">Not a member? Register an account!</a>
    </form>
</body>
</html>
