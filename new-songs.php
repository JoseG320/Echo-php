<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

// Database connection
require_once __DIR__ . '/config/db.php';

// Endpoint for adding song from list to library
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'addSong') {
    $song_id = $_POST['song_id'];

    if ($song_id) {
        $addSong = $conn->prepare("INSERT IGNORE INTO user_library (user_id, song_id) VALUES (?, ?)");
        $addSong->bind_param("ii", $_SESSION['user_id'], $song_id);

        if ($addSong->execute()) {
            header("Location: {$_SERVER['PHP_SELF']}");
            exit;
        } else {
            $error_message = "Error adding song: " . $addSong->error;
        }
        $addSong->close();
    } else {
        $error_message = "Invalid song ID";
    }
}

try {
    // Fetch songs that are public and not already in the user's library
    $fetchSongs = $conn->prepare("
        SELECT 
            songs.id, 
            songs.title, 
            songs.artist, 
            songs.album, 
            users.username, 
            users.id as user_id
        FROM songs 
        JOIN users ON users.id = songs.owner_id
        WHERE 
            songs.is_public = true AND
            songs.id NOT IN (
                SELECT user_library.song_id
                FROM user_library
                WHERE user_library.user_id = ?
            )
        ORDER BY songs.id DESC
        LIMIT 20"  // Limit to 20 most recent songs
    );
    $fetchSongs->bind_param("i", $_SESSION['user_id']);
    $fetchSongs->execute();

    $result = $fetchSongs->get_result();
    $songs = $result->fetch_all(MYSQLI_ASSOC);

    $fetchSongs->close();

} catch (Exception $e) {
    $error_message = "Error fetching songs: " . $e->getMessage();
}

?>

<!doctype html>
<html lang="en" data-theme="dark">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.pink.min.css">
    <title>New Music 🎶</title>
  </head>
  <body class="container">
    <header>
        <?php include "./pages/header.php" ?>
    </header>

    <main>
      <article>
        <header style="display: flex; justify-content: space-between;">
          <div>
            Newly Added Songs across the site!
          </div>
          <div>
            <a href="new-songs.php" style="color: #007bff; text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">🔄 refresh</a>
          </div>
        </header>
        
        <?php if (!empty($error_message)): ?>
            <p style="color: red;"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>

        <?php if (empty($songs)): ?>
            <p>No new songs available.</p>
        <?php else: ?>
            <?php foreach ($songs as $song): ?>
                <article>
                    <div class="grid">
                        <div><?= htmlspecialchars($song['title']) ?></div>
                        <div><?= htmlspecialchars($song['artist']) ?></div>
                        <div><?= htmlspecialchars($song['album']) ?></div>
                        <div style="display: flex; flex-direction: row;">
                            <a 
                              href="profile.php?user_id=<?= $song['user_id'] ?>" 
                              class="button" 
                              >
                                <button>View User: <?= htmlspecialchars($song['username']) ?></button>
                            </a>
                            <form action="" method="POST">
                                <input type="hidden" name="action" value="addSong">
                                <input type="hidden" name="song_id" value="<?= htmlspecialchars($song['id']) ?>">
                                <input type="submit" value="Add" style="margin-left: 10px">
                            </form>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
      </article>
    </main>
  </body>
</html>