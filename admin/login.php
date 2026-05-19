<?php
session_start();

// Database connection
require_once __DIR__ . '/../config/db.php';

// Check if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$login_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if ($result->num_rows === 1) {
        
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $username;
            header('Location: index.php');
            exit;
        } else {
            $login_error = 'Invalid username or password';
        }
    } else {
        $login_error = 'Invalid username or password';
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.pink.min.css">
    <title>Admin Login</title>
</head>
<body class="container">
    <main>
        <article>
            <form method="POST" action="">
                <label for="username">Admin Username</label>
                <input type="text" id="username" name="username" required>
                
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                
                <?php if ($login_error): ?>
                    <p style="color: red;"><?php echo htmlspecialchars($login_error); ?></p>
                <?php endif; ?>
                
                <button type="submit">Login</button>
            </form>
        </article>
    </main>
</body>
</html>