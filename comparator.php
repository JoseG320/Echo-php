<?php
session_start();

$conn = new mysqli('localhost', 'root', '', 'echo-db');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    die('You must be logged in to compare libraries.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $compare_with = $_POST['compare_with'];

    // Get common songs
    $query = "
        SELECT s.title, s.artist, s.album
        FROM user_library ul1
        INNER JOIN user_library ul2 ON ul1.song_id = ul2.song_id
        INNER JOIN songs s ON ul1.song_id = s.id
        WHERE ul1.user_id = ? AND ul2.user_id = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $user_id, $compare_with);
    $stmt->execute();
    $common_songs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Get unique songs
    $query = "
        SELECT s.title, s.artist, s.album
        FROM user_library ul
        INNER JOIN songs s ON ul.song_id = s.id
        WHERE ul.user_id = ? AND ul.song_id NOT IN (
            SELECT song_id FROM user_library WHERE user_id = ?
        )
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $user_id, $compare_with);
    $stmt->execute();
    $unique_songs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    echo '<h2>Common Songs</h2>';
    foreach ($common_songs as $song) {
        echo htmlspecialchars($song['title'] . ' - ' . $song['artist']) . '<br>';
    }

    echo '<h2>Your Unique Songs</h2>';
    foreach ($unique_songs as $song) {
        echo htmlspecialchars($song['title'] . ' - ' . $song['artist']) . '<br>';
    }
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
    <title>Compare Libraries</title>
</head>
<body class="container">
    <header>
        <?php include "./pages/header.php"?>
    </header>
    <h2>Compare Music Libraries</h2>
    <form method="POST">
        <label for="compare_with">Compare with User ID:</label>
        <input type="number" name="compare_with" id="compare_with" required>
        <button type="submit">Compare</button>
    </form>
</body>
</html>
