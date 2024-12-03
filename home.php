<?php
session_start();

// If session is not active with a userID, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Setting user data as variables
$current_user_id = $_SESSION['user_id'];
$current_username = $_SESSION['username'];

// Database connection
$conn = new mysqli('localhost', 'root', '', 'echo-db');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Endpoint for adding new song
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'add') {
    $title = $_POST['title'];
    $artist = $_POST['artist'];
    $album = $_POST['album'];
    $user_id = $current_user_id;

    if ($title && $artist && $album && $user_id) {
        $stmt = $conn->prepare("INSERT INTO songs (owner_id, title, artist, album) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $title, $artist, $album);

        // execute() will return T or F wether SQL statement was successful
        if ($stmt->execute()) {
            // if good SQL execution redirect to the same exact page. This will redo the query for 'songs' and update the table
            header("Location: {$_SERVER['PHP_SELF']}");
            exit;
        } else {
            $error_message = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_message = "All fields are required!";
    }
}

// Endpoint for deleting a song
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'delete') {
    $song_id = $_POST['song_id'];

    if ($song_id) {
        $deleteSong = $conn->prepare("DELETE FROM songs WHERE id = ?");
        $deleteSong->bind_param("i", $song_id);

        // execute() will return T or F wether SQL statement was successful
        if ($deleteSong->execute()) {
            // if good SQL execution redirect to the same exact page. This will redo the query for 'songs' and update the table
            header("Location: {$_SERVER['PHP_SELF']}");
            exit;
        } else {
            $error_message = "Error: " . $deleteSong->error;
        }
        $deleteSong->close();
    } else {
        $error_message = "There was an error deleting the song";
    }
}

// Endpoint for adding new Playlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'newPlaylist') {
    $name = $_POST['name'];
    $description = $_POST['description'];

    if ($_SESSION['user_id'] && $name && $description) {
        $addPlaylist = $conn->prepare("INSERT INTO playlists (owner_id, name, description) VALUES (?, ?, ?)");
        $addPlaylist->bind_param("iss", $_SESSION['user_id'], $name, $description);

        // execute() will return T or F wether SQL statement was successful
        if ($addPlaylist->execute()) {
            // if good SQL execution redirect to the same exact page. This will redo the query for 'songs' and update the table
            echo("executed playlist add");
            header("Location: {$_SERVER['PHP_SELF']}");
            exit;
        } else {
            echo("error in playlist add : execute()");
            $error_message = "Error: " . $addPlaylist->error;
        }
        $addPlaylist->close();
    } else {
        $playlist_error_message = "All fields are required!";
    }
}

// fetch the the users song library
try {
    $fetchSongs = $conn->prepare("
        select * 
        from songs where owner_id = ? 
        order by id desc"
    );
    $fetchSongs->bind_param("i", $current_user_id);
    $fetchSongs->execute();

    $result = $fetchSongs->get_result();
    $songs = $result->fetch_all(MYSQLI_ASSOC);

    $fetchSongs->close();

    $fetchPlaylists = $conn->prepare("select * from playlists where owner_id = ? order by id desc");
    $fetchPlaylists->bind_param("i", $current_user_id);
    $fetchPlaylists->execute();

    $playlistsResults = $fetchPlaylists->get_result();
    $playlists = $playlistsResults->fetch_all(MYSQLI_ASSOC);

    $fetchPlaylists->close();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
};

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
    <title>Dashboard</title>
</head>
<body class="container">
    <header>
        <?php include "./pages/header.php"?>
    </header>

    <nav>
    <ul>
        <li><h2>Welcome, <?php echo htmlspecialchars($current_username); ?>  🌊</h2></li>
    </ul>
    <ul>
        <li><a href="friends.php" class="contrast">📡 Add Friends</a></li>
        <li><a href="new-songs.php" class="contrast">🏍️ Newly Added Songs</a></li>
        <li><a href="comparisons.php" class="contrast">🌈 Find New Music!</a></li>

    </ul>
    </nav>
    <article>
        <header>Your Song Library</header>

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
        
        <footer style="max-height: 500px; overflow-y: auto;" >

        <?php if (empty($songs)): ?>
            <p>No Songs yet. Add a Song Below!</p>
        <?php else: ?>
            <?php foreach ($songs as $song): ?>
                <article>
                    <div class="grid">
                        <div><?= htmlspecialchars($song['title']) ?></div>
                        <div><?= htmlspecialchars($song['artist']) ?></div>
                        <div><?= htmlspecialchars($song['album']) ?></div>
                        <div class="grid">
                            <!-- EDIT BUTTON -->
                            <form action="edit.php" method="get">
                                <input type="hidden" name="song_id" value="<?= htmlspecialchars($song['id']) ?>">
                                <input type="submit" value="Edit">
                            </form>
                            <!-- DELETE BUTTON -->
                            <form action="" method="POST">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="song_id" value="<?= htmlspecialchars($song['id']) ?>">
                                <input type="submit" value="Delete" style="height: 70px">
                            </form>
                        </div>
                    </div>

                </article>
            <?php endforeach; ?>
        <?php endif; ?>
        </footer>
    </article>

    <article>
        <header>Your Playlists</header>
         
        <?php if (empty($playlists)): ?>
            <p>C'mon... no playlists? 🔭</p>
        <?php else: ?>
            <?php foreach ($playlists as $pl): ?>

                <form action="playlist.php" method="get">
                    <input type="hidden" name="playlist_id" value="<?= htmlspecialchars($pl['id']) ?>">
                    <input type="submit" value="<?= htmlspecialchars($pl['name']) ?>">
                </form>

            <?php endforeach; ?>
        <?php endif; ?>

        <footer>
            <!-- Form for adding playlist -->
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
        </footer>
    </article>
    
</body>
</html>
