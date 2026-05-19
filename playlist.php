<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

// Database connection
require_once __DIR__ . '/config/db.php';

// Fetch the playlist from url parameter
$fetchPlaylist = $conn->prepare("SELECT * FROM playlists WHERE id = ?");
$fetchPlaylist->bind_param("i", $_GET['playlist_id']);
$fetchPlaylist->execute();
$playlist = $fetchPlaylist->get_result()->fetch_assoc();
$fetchPlaylist->close();

// Handle playlist edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'editPlaylist') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $playlist_id = $_GET['playlist_id'];
    
    if ($playlist['id'] && $name && $description) {
        $addPlaylist = $conn->prepare("UPDATE playlists SET name = ?, description = ? WHERE id = ?");
        $addPlaylist->bind_param("ssi", $name, $description, $playlist_id);
        
        if ($addPlaylist->execute()) {
            header("Location: playlist.php?playlist_id=$playlist_id");
            exit;
        } else {
            $error_message = "Error: " . $addPlaylist->error;
        }
        $addPlaylist->close();
    } else {
        $playlistEdit_error_message = "All fields are required!";
    }
}

// Handle adding a song to the playlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'addSongToPlaylist') {
    $song_id = $_POST['song_id'];
    $playlist_id = $playlist['id'];
    
    // Check if the song is already in the playlist
    $checkDuplicate = $conn->prepare("SELECT COUNT(*) as count FROM playlist_songs WHERE playlist_id = ? AND song_id = ?");
    $checkDuplicate->bind_param("ii", $playlist_id, $song_id);
    $checkDuplicate->execute();
    $duplicateResult = $checkDuplicate->get_result()->fetch_assoc();
    $checkDuplicate->close();
    
    if ($duplicateResult['count'] > 0) {
        $song_error_message = "Song is already in the playlist.";
    } else {
        // Add song to playlist without specifying position
        $addSong = $conn->prepare("INSERT INTO playlist_songs (playlist_id, song_id) VALUES (?, ?)");
        $addSong->bind_param("ii", $playlist_id, $song_id);
        
        if ($addSong->execute()) {
            header("Location: playlist.php?playlist_id=$playlist_id");
            exit;
        } else {
            $song_error_message = "Error adding song: " . $addSong->error;
        }
        $addSong->close();
    }
}

// Endpoint for deleting a song from a playlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'deletePlaylistSong') {
    $pl_id = $_POST['pl_id'];
    $playlist_id = $_GET['playlist_id'];
    
    if ($pl_id) {
        $deletePlaylistSong = $conn->prepare("DELETE FROM playlist_songs WHERE id = ? AND playlist_id = ?");
        $deletePlaylistSong->bind_param("ii", $pl_id, $playlist_id);
        
        // execute() will return T or F whether SQL statement was successful
        if ($deletePlaylistSong->execute()) {
            // Redirect to the same page to refresh the playlist songs
            header("Location: playlist.php?playlist_id=$playlist_id");
            exit;
        } else {
            $song_error_message = "Error: " . $deletePlaylistSong->error;
        }
        $deletePlaylistSong->close();
    } else {
        $song_error_message = "There was an error deleting the song from the playlist";
    }
}

// Handle deleting the playlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'deletePlaylist') {
    $playlist_id = $playlist['id'];

    if ($playlist_id) {
        // Delete all songs in the playlist first
        $deletePlaylistSongs = $conn->prepare("DELETE FROM playlist_songs WHERE playlist_id = ?");
        $deletePlaylistSongs->bind_param("i", $playlist_id);
        $deletePlaylistSongs->execute();
        $deletePlaylistSongs->close();

        // then delete playlist 
        $deletePlaylist = $conn->prepare("DELETE FROM playlists WHERE id =  ?");
        $deletePlaylist->bind_param("i", $playlist_id);
        
        if ($deletePlaylist->execute()) {
            // Redirect to the same page to refresh the playlist songs
            header("Location: home.php");
            exit;
        } else {
            $playlistDelete_error_message = "Error deleting the playlist: " . $deletePlaylist->error;
        }
        $deletePlaylist->close();
    } else {
        $playlistDelete_error_message = "There was an error deleting the playlist.";
    }
}

// Fetch user's songs that are not in the playlist
$fetchSongs = $conn->prepare
("SELECT songs.id, songs.title, songs.artist
  FROM songs 
  JOIN user_library ON user_library.song_id = songs.id
  WHERE user_library.user_id = ? AND songs.id NOT IN (
    SELECT playlist_songs.song_id
    FROM playlist_songs
    WHERE playlist_songs.playlist_id = ?
  )
");
$fetchSongs->bind_param("ii", $_SESSION['user_id'], $_GET['playlist_id']);
$fetchSongs->execute();

$userSongsResult = $fetchSongs->get_result();
$userSongs = $userSongsResult->fetch_all(MYSQLI_ASSOC);
$fetchSongs->close();

// Fetch current playlist songs
$fetchPlaylistSongs = $conn->prepare("
  SELECT pl.id as pl_song_id, songs.id as song_id, songs.title, songs.artist, songs.album
  FROM playlist_songs pl 
  JOIN songs ON pl.song_id = songs.id
  WHERE pl.playlist_id = ?
");
$fetchPlaylistSongs->bind_param("i", $playlist['id']);
$fetchPlaylistSongs->execute();

$playlistResults = $fetchPlaylistSongs->get_result();
$playlistSongs = $playlistResults->fetch_all(MYSQLI_ASSOC);
$fetchPlaylistSongs->close();
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Playlist</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.pink.min.css">
</head>
<body class="container">
    <header>
        <?php include "./pages/header.php" ?>
    </header>
    
    <main>
        <article>
            <header>Playlist Songs</header>
            
            <form action="" method="POST">
                <fieldset class="grid">
                    <input type="hidden" name="action" value="editPlaylist"/>
                    <input name="name" placeholder="Name" aria-label="Name" value="<?= htmlspecialchars($playlist['name']) ?>"/>
                    <input name="description" placeholder="🤔 Whats the vibe?" aria-label="Description" value="<?= htmlspecialchars($playlist['description']) ?>"/>
                    <input type="submit" value="Edit Details"/>
                </fieldset>
                <?php if (isset($playlistEdit_error_message)): ?>
                    <p style="color: red;"><?php echo htmlspecialchars($playlistEdit_error_message); ?></p>
                <?php endif; ?>
            </form>
            
            <section>
                <h2>Current Playlist Songs</h2>
                <?php if (empty($playlistSongs)): ?>
                    <p>No Songs yet. Add a Song Below!</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Artist</th>
                                <th>Album</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($playlistSongs as $song): ?>
                                <tr>
                                    <td><?= htmlspecialchars($song['title']) ?></td>
                                    <td><?= htmlspecialchars($song['artist']) ?> <?= htmlspecialchars($song['song_id']) ?></td>
                                    <td><?= htmlspecialchars($song['album'] ?? 'N/A') ?></td>
                                    <td>
                                        <form action="" method="POST" style="padding-bottom: 0px; height: 70px; width: 100px">
                                            <input type="hidden" name="action" value="deletePlaylistSong">
                                            <input type="hidden" name="pl_id" value="<?= htmlspecialchars($song['pl_song_id']) ?>">
                                            <input type="submit" value="Delete">
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>
            
            <section>
                <h2>Add Song to Playlist</h2>
                <?php if ($song_error_message ?? false): ?>
                    <p style="color: red;"><?= htmlspecialchars($song_error_message) ?></p>
                <?php endif; ?>
                
                <form action="" method="POST">
                    <input type="hidden" name="action" value="addSongToPlaylist"/>
                    <fieldset>
                        <select name="song_id" required>
                            <option value="">Select a Song</option>
                            <?php 
                            // Reset the pointer to beginning of result set 
                            foreach ($userSongs as $song): 
                            ?>
                                <option value="<?= $song['id'] ?>">
                                    <?= htmlspecialchars($song['title']) ?> - <?= htmlspecialchars($song['artist']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="submit" value="Add to Playlist"/>
                    </fieldset>
                </form>
            </section>
        </article>
        <form action="" method="POST" style="width: 200px;">
            <input type="hidden" name="action" value="deletePlaylist"/>
            <input type="submit" value="Delete Playlist" style="background-color: red; color: white;"/>
        </form>

        <?php if (isset($playlist_error_message)): ?>
            <p style="color: red;"><?= htmlspecialchars($playlist_error_message) ?></p>
        <?php endif; ?>
    </main>
</body>
</html>