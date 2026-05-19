<?php
session_start();

require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user's playlists for the dropdown
$fetchPlaylists = $conn->prepare("
    SELECT id, name 
    FROM playlists 
    WHERE owner_id = ?
");
$fetchPlaylists->bind_param("i", $user_id);
$fetchPlaylists->execute();
$playlistsResult = $fetchPlaylists->get_result();
$playlists = $playlistsResult->fetch_all(MYSQLI_ASSOC);
$fetchPlaylists->close();

// Fetch user's connections for the dropdown
$fetchConnections = $conn->prepare("
    SELECT DISTINCT users.id, users.username
    FROM users
    JOIN user_connections uc ON uc.following_id = users.id
    WHERE uc.follower_id = ?
");
$fetchConnections->bind_param("i", $user_id);
$fetchConnections->execute();
$connectionsResult = $fetchConnections->get_result();
$connections = $connectionsResult->fetch_all(MYSQLI_ASSOC);
$fetchConnections->close();

$friendPlaylists = [];
$common_songs = [];
$unique_songs = [];

// AJAX handler for fetching friend's playlists
if (isset($_GET['get_friend_playlists'])) {
    $friend_id = intval($_GET['friend_id']);
    
    $fetchFriendPlaylists = $conn->prepare("
        SELECT id, name 
        FROM playlists 
        WHERE owner_id = ? AND is_public = true
    ");
    $fetchFriendPlaylists->bind_param("i", $friend_id);
    $fetchFriendPlaylists->execute();
    $friendPlaylistsResult = $fetchFriendPlaylists->get_result();
    $friendPlaylists = $friendPlaylistsResult->fetch_all(MYSQLI_ASSOC);
    $fetchFriendPlaylists->close();
    
    header('Content-Type: application/json');
    echo json_encode($friendPlaylists);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $compare_with_playlist = $_POST['compare_with_playlist'];
    $selected_playlist = $_POST['playlist_id'];

    // Get common songs
    $commonQuery = $conn->prepare("
        SELECT s.title, s.artist, s.album
        FROM playlist_songs ps1
        JOIN playlist_songs ps2 ON ps1.song_id = ps2.song_id
        JOIN songs s ON ps1.song_id = s.id
        WHERE ps1.playlist_id = ? AND ps2.playlist_id = ?
    ");
    $commonQuery->bind_param("ii", $selected_playlist, $compare_with_playlist);
    $commonQuery->execute();
    $common_songs = $commonQuery->get_result()->fetch_all(MYSQLI_ASSOC);
    $commonQuery->close();

    // Get unique songs
    $uniqueQuery = $conn->prepare("
        SELECT s.title, s.artist, s.album
        FROM playlist_songs ps
        JOIN songs s ON ps.song_id = s.id
        WHERE ps.playlist_id = ? AND ps.song_id NOT IN (
        SELECT song_id FROM playlist_songs WHERE playlist_id = ?
        )
    ");
    $uniqueQuery->bind_param("ii", $selected_playlist, $compare_with_playlist);
    $uniqueQuery->execute();
    $unique_songs = $uniqueQuery->get_result()->fetch_all(MYSQLI_ASSOC);
    $uniqueQuery->close();
}
?>

<!DOCTYPE html>
<html data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark" />
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.pink.min.css"
    >
    <title>Compare Playlists</title>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const friendSelect = document.getElementById('compare_with_friend');
        const friendPlaylistSelect = document.getElementById('compare_with_playlist');

        friendSelect.addEventListener('change', function() {
            const friendId = this.value;
            
            // Clear previous options
            friendPlaylistSelect.innerHTML = '<option value="">Select a Playlist</option>';
            
            // If a friend is selected, fetch their playlists
            if (friendId) {
                fetch(`?get_friend_playlists=1&friend_id=${friendId}`)
                    .then(response => response.json())
                    .then(playlists => {
                        playlists.forEach(playlist => {
                            const option = document.createElement('option');
                            option.value = playlist.id;
                            option.textContent = playlist.name;
                            friendPlaylistSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to load playlists');
                    });
            }
        });
    });
    </script>
</head>
<body class="container">
    <header>
        <?php include "./pages/header.php" ?>
    </header>
    <h2>Compare Playlists</h2>
    <form method="POST">
        <label for="playlist_id">Your Playlist:</label>
        <select name="playlist_id" id="playlist_id" required>
            <option value="">Select a Playlist</option>
            <?php foreach ($playlists as $playlist): ?>
                <option value="<?= htmlspecialchars($playlist['id']) ?>">
                    <?= htmlspecialchars($playlist['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="compare_with_friend">Select a Friend:</label>
        <select name="compare_with_friend" id="compare_with_friend" required>
            <option value="">Select a Friend</option>
            <?php foreach ($connections as $connection): ?>
                <option value="<?= htmlspecialchars($connection['id']) ?>">
                    <?= htmlspecialchars($connection['username']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="compare_with_playlist">Friend's Playlist:</label>
        <select name="compare_with_playlist" id="compare_with_playlist" required>
            <option value="">Select a Friend's Playlist</option>
        </select>

        <button type="submit">Compare</button>
    </form>

    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <h2>Common Songs</h2>
        <?php if (empty($common_songs)): ?>
            <p>No common songs found.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($common_songs as $song): ?>
                    <li><?= htmlspecialchars($song['title'] . ' - ' . $song['artist']) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <h2>Your Unique Songs</h2>
        <?php if (empty($unique_songs)): ?>
            <p>You have no unique songs.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($unique_songs as $song): ?>
                    <li><?= htmlspecialchars($song['title'] . ' - ' . $song['artist']) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>