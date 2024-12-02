<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
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
    <title>Dashboard</title>
</head>
<body class="container">
    <header>
        <?php include "./pages/header.php"?>
    </header>
    <!-- Test Page to show who is logged in. To be changed into a new page. -->
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
</body>
</html>
