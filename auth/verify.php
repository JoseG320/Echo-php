<?php
require __DIR__ . '/../vendor/autoload.php';

$host     = $_ENV['DB_HOST']     ?? getenv('DB_HOST')     ?: 'localhost';
$dbname   = $_ENV['DB_NAME']     ?? getenv('DB_NAME')     ?: 'echo-db';
$user     = $_ENV['DB_USER']     ?? getenv('DB_USER')     ?: 'root';
$password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '';
$db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);

$auth = new \Delight\Auth\Auth($db);

if (isset($_GET['selector']) && isset($_GET['token'])) {
    try {
        $auth->confirmEmail($_GET['selector'], $_GET['token']);
        echo 'Email address has been verified';
    }
    catch (\Delight\Auth\InvalidSelectorTokenPairException $e) {
        echo 'Invalid token';
    }
    catch (\Delight\Auth\TokenExpiredException $e) {
        echo 'Token expired';
    }
    catch (\Delight\Auth\UserAlreadyExistsException $e) {
        echo 'Email address already verified';
    }
    catch (\Delight\Auth\TooManyRequestsException $e) {
        echo 'Too many requests';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.pink.min.css">
</head>
<body class="container">
    <header>
        <?php include "../pages/header.php"?>
    </header>
    <header>
        <h1>Email Verification</h1>
    </header>

    <main>
        <article>
            <p><?php echo $message; ?></p>
            <a href="login.php" class="button">Go to Login</a>
        </article>
    </main>

</body>
</html>