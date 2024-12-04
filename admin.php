<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin-login.php');
    exit;
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'echo-db');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Handle deletion actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['id'])) {
        switch ($_POST['action']) {
            case 'delete_song':
                $stmt = $conn->prepare("DELETE FROM songs WHERE id = ?");
                $stmt->bind_param("i", $_POST['id']);
                $stmt->execute();
                $conn->close();
                break;

            case 'delete_playlist':
                $stmt = $conn->prepare("DELETE FROM playlists WHERE id = ?");
                $stmt->bind_param("i", $_POST['id']);
                $stmt->execute();
                $conn->close();
                break;

            case 'delete_user':
                $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $stmt->bind_param("i", $_POST['id']);
                $stmt->execute();
                $conn->close();
                break;

            case 'delete_admin':
                // Prevent deleting the current logged-in admin
                if ($_POST['id'] != $_SESSION['admin_id']) {
                    $stmt = $conn->prepare("DELETE FROM admins WHERE id = ?");
                    $stmt->bind_param("i", $_POST['id']);
                    $stmt->execute();
                    $conn->close();
                }
                break;
        }
        
        // Redirect to prevent form resubmission
        header('Location: admin.php');
        exit;
    }
}

$songs_query = 
    "SELECT songs.id, songs.title, songs.album, songs.artist, users.username 
    FROM songs 
    JOIN users ON songs.owner_id = users.id 
    ";
$songs_result = $conn->query($songs_query);
$songs = $songs_result ? $songs_result->fetch_all(MYSQLI_ASSOC) : [];


$playlists_query = 
    "SELECT playlists.id, playlists.name, playlists.description, users.username
    FROM playlists 
    JOIN users ON playlists.owner_id = users.id 
    ";

$playlists_result = $conn->query($playlists_query);
$playlists = $playlists_result ? $playlists_result->fetch_all(MYSQLI_ASSOC) : [];

// Fetch users
$users_query = "SELECT * FROM users";
$users_result = $conn->query($users_query);
$users = $users_result ? $users_result->fetch_all(MYSQLI_ASSOC) : [];
$conn->close();

?>

<!DOCTYPE html>
<html data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.pink.min.css">
    <title>Admin Dashboard</title>
    <style>
        .scrollable-table {
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
</head>
<body class="container">
    <header>
        <h1>Admin Dashboard</h1>
        <a href="index.php">Back To Home</a>
    </header>

    <main>
        <article>
            <header>Recent Songs</header>
            <div class="scrollable-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Artist</th>
                            <th>Album</th>
                            <th>User</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($songs as $song): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($song['id']); ?></td>
                                <td><?php echo htmlspecialchars($song['title']); ?></td>
                                <td><?php echo htmlspecialchars($song['artist']); ?></td>
                                <td><?php echo htmlspecialchars($song['album']); ?></td>
                                <td><?php echo htmlspecialchars($song['username']); ?></td>
                                <td>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="delete_song">
                                        <input type="hidden" name="id" value="<?php echo $song['id']; ?>">
                                        <button type="submit" class="contrast">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </article>

        <article>
            <header>Recent Playlists</header>
            <div class="scrollable-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>User</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($playlists as $playlist): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($playlist['id']); ?></td>
                                <td><?php echo htmlspecialchars($playlist['name']); ?></td>
                                <td><?php echo htmlspecialchars($playlist['description']); ?></td>
                                <td><?php echo htmlspecialchars($playlist['username']); ?></td>
                                <td>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="delete_playlist">
                                        <input type="hidden" name="id" value="<?php echo $playlist['id']; ?>">
                                        <button type="submit" class="contrast">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </article>

        <article>
            <header>Users</header>
            <div class="scrollable-table">
                <table>
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <form method="POST">
                                    <input type="hidden"  name="action" value="delete_user">
                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="contrast" >Delete</button>
                                    </form>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </article>

    </main>
</body>
</html>
