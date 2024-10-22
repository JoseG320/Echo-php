<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Connect to database
    $conn = new mysqli('localhost', 'root', '', 'echo-db');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fetch the user data from the database
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify the entered password with the hashed password
        if (password_verify($password, $user['password'])) {
            // Password matches, set session variables and redirect
            $_SESSION['username'] = $username;
            header("Location: dashboard.php");
        } else {
            echo "Invalid username or password.";
        }
    } else {
        echo "Invalid username or password.";
    }

    $conn->close();
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
<body>
    <h2>Register</h2>
    <form method="POST" action="">
        Username: <input type="text" name="username" required><br>
        Password: <input type="password" name="password" required><br>
        <button type="submit">Register</button>
    </form>
</body>
</html>
