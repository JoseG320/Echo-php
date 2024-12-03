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

// fetch the the playlistID from url parameter and add to sql query
$fetchPlaylist = $conn->prepare("SELECT * FROM playlists WHERE id = ?");
$fetchPlaylist->bind_param("i", $_GET['playlist_id']);
$fetchPlaylist->execute();

$playlist = $fetchPlaylist->get_result()->fetch_assoc();
$fetchPlaylist->close();

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

    <article>
        <header>Playlist Songs</header>
         
        <form action="" method="POST" >
            <fieldset class="grid">
                <input
                type="hidden"
                name="action"
                value="newPlaylist"
                />
                <input
                name="name"
                placeholder="Name"
                aria-label="Name"
                />
                <input
                name="description"
                placeholder="🤔 Whats the vibe?"
                aria-label="Description"
                />
                <input
                type="submit"
                value="Add Playlist"
                />
            </fieldset>
            <?php if (isset($error_message)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($playlist_error_message); ?></p>
            <?php endif; ?>
        </form>
        
        <footer>
            <!-- This form posts to /home -->
        <form action="" method="POST" >
            <fieldset class="grid">
                <input
                type="hidden"
                name="action"
                value="add"
                />
                <input 
                name="title"
                placeholder="Title"
                aria-label="Title"
                />
                <input
                name="artist"
                placeholder="Artist"
                aria-label="Artist"
                />
                <input
                name="album"
                placeholder="Album"
                aria-label="Album"
                />
                <input
                type="submit"
                value="Add Song"
                />
            </fieldset>
            <?php if (isset($error_message)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>
        </form>
            playlists songs go here
        </footer>
    </article>

    </main>
</body>
</html>
