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

// fetch the the song from url parameter and add to sql query
$fetchSong = $conn->prepare("SELECT * FROM songs WHERE id = ?");
$fetchSong->bind_param("i", $_GET['song_id']);
$fetchSong->execute();

$song = $fetchSong->get_result()->fetch_assoc();
$fetchSong->close();

// endpoint to update song
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'update') {
    $title = $_POST['title'];
    $artist = $_POST['artist'];
    $album = $_POST['album'];

    $updateSong = $conn->prepare("UPDATE songs SET title = ?, artist = ?, album = ? WHERE id = ?");
    $updateSong->bind_param("sssi", $title, $artist, $album, $_GET['song_id']);

    if ($updateSong->execute()) {
        // redirect to home if successful
        header("Location: home.php"); 
        exit;
    } else {
        echo "Error updating song: " . $updateSong->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Song</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.pink.min.css">
</head>
<body class="container">
    <header>
        <?php include "./pages/header.php" ?>
    </header>

    <main>
        <h2>Edit Song</h2>
        <form action="" method="POST">
            <fieldset>
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="song_id" value="<?= htmlspecialchars($song['id']) ?>">

                <label for="title">Title</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($song['title']) ?>" required>

                <label for="artist">Artist</label>
                <input type="text" id="artist" name="artist" value="<?= htmlspecialchars($song['artist']) ?>" required>

                <label for="album">Album</label>
                <input type="text" id="album" name="album" value="<?= htmlspecialchars($song['album']) ?>" required>

                <button type="submit">Save Changes</button>
            </fieldset>
        </form>
    </main>
</body>
</html>
