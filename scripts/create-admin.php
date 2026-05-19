<?php
require __DIR__ . '/../config/db.php';

if ($argc < 3) {
    echo "Usage: php create-admin.php <username> <password>\n";
    exit(1);
}

$username = $argv[1];
$password = password_hash($argv[2], PASSWORD_BCRYPT);

$stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
$stmt->bind_param("ss", $username, $password);

if ($stmt->execute()) {
    echo "Admin '$username' created successfully.\n";
} else {
    echo "Error: " . $stmt->error . "\n";
}

$stmt->close();
$conn->close();