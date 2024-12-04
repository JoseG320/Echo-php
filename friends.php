<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'echo-db');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}



// Handle adding a connection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'addConnection') {
    $follow_user_id = $_POST['userid'];

    if ($follow_user_id && $follow_user_id != $_SESSION['user_id']) {
        $addConnection = $conn->prepare("INSERT INTO user_connections (follower_id, following_id) VALUES (?, ?)");
        $addConnection->bind_param("ii", $_SESSION['user_id'], $follow_user_id);
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

// Endpoint for deleting connection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'deleteConnection') {
    $connection_id = $_POST['connection_id'];

    if ($connection_id) {
        $deleteConnection = $conn->prepare("DELETE FROM user_connections WHERE id = ?");
        $deleteConnection->bind_param("i", $connection_id);
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

$fetchConnections = $conn->prepare
("SELECT user_connections.id as connection_id, users.id, users.username, count(*) as echo_score
  FROM users
  JOIN user_connections ON user_connections.following_id = users.id
  JOIN user_library ON user_library.user_id = users.id
  WHERE user_connections.follower_id = ? 
    AND user_library.song_id IN (
        SELECT user_library.song_id
        FROM user_library
        WHERE user_id = ?
    )
  GROUP BY user_connections.id, users.id, users.username
");
$fetchConnections->bind_param("ii", $_SESSION['user_id'], $_SESSION['user_id']);
$fetchConnections->execute();
$connectionsResult = $fetchConnections->get_result();
$connections = $connectionsResult->fetch_all(MYSQLI_ASSOC);
$fetchConnections->close();

$fetchUsers = $conn->prepare
("SELECT users.id as userid, username, COUNT(*) as echo_score
  FROM users 
  JOIN user_library ON user_library.user_id = users.id
  WHERE users.id != ? AND users.id NOT IN (
    SELECT user_connections.following_id
    FROM user_connections
    WHERE user_connections.follower_id = ?
  ) AND user_library.song_id IN (
    SELECT user_library.song_id
    FROM user_library
    WHERE user_id = ?
  )
  GROUP BY users.id, username
");
$fetchUsers->bind_param("iii", $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']);
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
            <header>Manage Your Connections (Echo Score = Songs in common) </header>

            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>🔥 Echo Score 🔥</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['echo_score']) ?></td>
                            <td>
                                <a 
                                href="profile.php?user_id=<?= $user['userid'] ?>" 
                                class="button" 
                                >
                                    <button>View Profile</button>
                                </a>
                                </td>
                            <td>
                                <form action="" method="POST" style="padding-bottom: 0px; height: 70px; width: 100px">
                                    <input type="hidden" name="action" value="addConnection"/>
                                    <input type="hidden" name="userid" value="<?= $user['userid'] ?>">
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
                                <th>🔥 Echo Score 🔥</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($connections as $connection): ?>
                                <tr>
                                    <td><?= htmlspecialchars($connection['username']) ?></td>
                                    <td><?= htmlspecialchars($connection['echo_score']) ?></td>
                                    <td>
                                        <a 
                                        href="profile.php?user_id=<?= $user['userid'] ?>" 
                                        class="button" 
                                        >
                                            <button>View Profile</button>
                                        </a>
                                        </td>
                                    <td>
                                    <td>
                                        <form action="" method="POST" style="padding-bottom: 0px; height: 70px; width: 100px">
                                            <input type="hidden" name="action" value="deleteConnection"/>
                                            <input type="hidden" name="connection_id" value="<?= $connection['connection_id'] ?>">
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
