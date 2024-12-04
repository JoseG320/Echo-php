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

// Endpoint for adding song from list to library
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'addSong') {
  $song_id = $_POST['song_id'];

  if ($song_id) {



      $addSong = $conn->prepare("INSERT INTO user_library (user_id, song_id) VALUES (?, ?)");
      $addSong->bind_param("ii", $_SESSION['user_id'], $song_id);

      // execute() will return T or F wether SQL statement was successful
      if ($addSong->execute()) {
          // if good SQL execution redirect to the same exact page. This will redo the query for 'songs' and update the table
          header("Location: {$_SERVER['PHP_SELF']}");
          exit;
      } else {
          $error_message = "Error: " . $addSong->error;
      }
      $addSong->close();
  } else {
      $error_message = "There was an error deleting the song";
  }
}

try {
  $fetchSongs = $conn->prepare("
      select songs.id, songs.title, songs.artist, songs.album, users.username, users.id as user_id
      from songs 
      JOIN users ON users.id = songs.owner_id
      WHERE songs.id NOT IN (
        SELECT user_library.song_id
        FROM user_library
        WHERE user_library.user_id = ?
      )
      order by id desc"
  );
  $fetchSongs->bind_param("i", $_SESSION['user_id']);
  $fetchSongs->execute();

  $result = $fetchSongs->get_result();
  $songs = $result->fetch_all(MYSQLI_ASSOC);

  $fetchSongs->close();

} catch (Exception $e) {
  echo "Error: " . $e->getMessage();
};

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
        
        <?php if (empty($songs)): ?>
            <p></p>
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