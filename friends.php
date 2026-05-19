<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

// Database connection
require_once __DIR__ . '/config/db.php';

$user_id = $_SESSION['user_id'];

// Handle adding a connection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'addConnection') {
    $follow_user_id = $_POST['follow_user_id'];

    if ($follow_user_id && $follow_user_id != $user_id) {
        $addConnection = $conn->prepare("INSERT IGNORE INTO user_connections (follower_id, following_id) VALUES (?, ?)");
        $addConnection->bind_param("ii", $user_id, $follow_user_id);
        if ($addConnection->execute()) {
            header("Location: friends.php");
            exit;
        } else {
            $connection_error_message = "Error adding connection.";
        }
        $addConnection->close();
    } else {
        $connection_error_message = "Invalid user or you cannot follow yourself.";
    }
}

// Handle deleting a connection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'deleteConnection') {
    $connection_id = $_POST['connection_id'];

    if ($connection_id) {
        $deleteConnection = $conn->prepare("DELETE FROM user_connections WHERE id = ? AND (follower_id = ? OR following_id = ?)");
        $deleteConnection->bind_param("iii", $connection_id, $user_id, $user_id);
        if ($deleteConnection->execute()) {
            header("Location: friends.php");
            exit;
        } else {
            $connection_error_message = "Error deleting connection.";
        }
        $deleteConnection->close();
    } else {
        $connection_error_message = "Invalid connection.";
    }
}

// Fetch user's connections (followers and followings)
$fetchConnections = $conn->prepare("
    SELECT uc.id as uc_id, users.id, users.username
    FROM users
    JOIN user_connections uc ON uc.following_id = users.id
    WHERE uc.follower_id = ? OR uc.following_id = ?
");
$fetchConnections->bind_param("ii", $user_id, $user_id);
$fetchConnections->execute();
$connectionsResult = $fetchConnections->get_result();
$connections = $connectionsResult->fetch_all(MYSQLI_ASSOC);
$fetchConnections->close();

// Fetch all users excluding the logged-in user
$fetchUsers = $conn->prepare
("SELECT id, username 
  FROM users 
  WHERE id != ? AND id NOT IN (
    SELECT user_connections.following_id
    FROM user_connections
    WHERE user_connections.follower_id = ?
  )
");
$fetchUsers->bind_param("ii", $user_id, $user_id);
$fetchUsers->execute();
$usersResult = $fetchUsers->get_result();
$users = $usersResult->fetch_all(MYSQLI_ASSOC);
$fetchUsers->close();
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connections</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.pink.min.css">
</head>
<body class="container">
    <header>
        <?php include "./pages/header.php" ?>
    </header>

    <main>
        <article>
            <header>Manage Your Connections</header>

            <!-- Add Connection Form -->
            <section>
                <h2>Add New Connection</h2>
                <?php if (isset($connection_error_message)): ?>
                    <p style="color: red;"><?= htmlspecialchars($connection_error_message) ?></p>
                <?php endif; ?>

                <form action="" method="POST">
                    <input type="hidden" name="action" value="addConnection"/>
                    <fieldset>
                        <select name="follow_user_id" required>
                            <option value="">Select a User</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['id'] ?>">
                                    <?= htmlspecialchars($user['username']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="submit" value="Add Connection"/>
                    </fieldset>
                </form>
            </section>

            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td>
                                <a 
                                href="profile.php?user_id=<?= $user['id'] ?>" 
                                class="button" 
                                >
                                    <button>View Profile</button>
                                </a>
                            </td>
                            <td>
                                <form action="" method="POST" style="padding-bottom: 0px; height: 70px; width: 100px">
                                    <input type="hidden" name="action" value="addConnection"/>
                                    <input type="hidden" name="follow_user_id" value="<?= $user['id'] ?>">
                                    <input type="submit" value="Add"/>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Display Connections -->
            <section>
                <h2>Your Connections</h2>
                <?php if (empty($connections)): ?>
                    <p>You have no connections.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($connections as $connection): ?>
                                <tr>
                                    <td><?= htmlspecialchars($connection['username']) ?></td>
                                    <td>
                                        <a 
                                        href="profile.php?user_id=<?= $connection['id'] ?>" 
                                        class="button" 
                                        >
                                            <button>View Profile</button>
                                        </a>
                                    </td>
                                    <td>
                                        <form action="" method="POST" style="padding-bottom: 0px; height: 70px; width: 100px">
                                            <input type="hidden" name="action" value="deleteConnection"/>
                                            <input type="hidden" name="connection_id" value="<?= $connection['uc_id'] ?>">
                                            <input type="submit" value="Delete"/>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>
        </article>
    </main>
</body>
</html>
