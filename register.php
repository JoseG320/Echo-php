<?php
require __DIR__ . '/vendor/autoload.php';
$db = new PDO('mysql:host=localhost;dbname=echo-db;charset=utf8', 'root', '');
$auth = new \Delight\Auth\Auth($db);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $username = $_POST['username'];

    try {
        $userId = $auth->register($email, $password, $username, function ($selector, $token) {
            // Send the verification email
            // For example, using PHP's mail() function or an external service
            echo 'Please confirm your email by visiting the following URL: ';
            echo '<a href="verify.php?selector=' . $selector . '&token=' . $token . '">Verify your email</a>';
        });

        echo 'We have signed up a new user with the ID ' . $userId;
    }
    catch (\Delight\Auth\InvalidEmailException $e) {
        echo 'Invalid email address';
    }
    catch (\Delight\Auth\InvalidPasswordException $e) {
        echo 'Invalid password';
    }
    catch (\Delight\Auth\UserAlreadyExistsException $e) {
        echo 'User or email already exists';
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark" />
    <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.pink.min.css"
    >
</head>
<body class="container">
    <header>
        <?php include "./pages/header.php"?>
    </header>
    <h2>Register</h2>
    <form method="POST" action="">
        Email: <input type="email" name="email" required><br>
        Username: <input type="text" name="username" required><br>
        Password: <input type="password" name="password" required><br>
        <button type="submit">Register</button>
    </form>
</body>
</html>
