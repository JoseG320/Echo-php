<?php
define('BASE_URL', '');
// Load .env file if it exists (for local/XAMPP development)
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

$conn = new mysqli(
    $_ENV['DB_HOST']     ?? getenv('DB_HOST')     ?: 'localhost',
    $_ENV['DB_USER']     ?? getenv('DB_USER')     ?: 'root',
    $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '',
    $_ENV['DB_NAME']     ?? getenv('DB_NAME')     ?: 'echo-db'
);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}