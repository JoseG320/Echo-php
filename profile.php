<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'echo-db');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Get the user ID from the query string
if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
    echo "Invalid user.";
    exit;
}
$profile_user_id = intval($_GET['user_id']);

// Fetch profile user info (optional, for display)
$profileUserStmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$profileUserStmt->bind_param("i", $profile_user_id);
$profileUserStmt->execute();
$profileUserResult = $profileUserStmt->get_result();
$profileUser = $profileUserResult->fetch_assoc();
$profileUserStmt->close();

if (!$profileUser) {
    echo "User not found.";
    exit;
}
$profile_username = htmlspecialchars($profileUser['username']);

// Fetch the user's songs
$songsStmt = $conn->prepare("SELECT * FROM songs WHERE owner_id = ? ORDER BY id DESC");
$songsStmt->bind_param("i", $profile_user_id);
$songsStmt->execute();
$songsResult = $songsStmt->get_result();
$songs = $songsResult->fetch_all(MYSQLI_ASSOC);
$songsStmt->close();

// Fetch the user's playlists
$playlistsStmt = $conn->prepare("SELECT * FROM playlists WHERE owner_id = ? ORDER BY id DESC");
$playlistsStmt->bind_param("i", $profile_user_id);
$playlistsStmt->execute();
$playlistsResult = $playlistsStmt->get_result();
$playlists = $playlistsResult->fetch_all(MYSQLI_ASSOC);
$playlistsStmt->close();

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
    <title>Profile of <?= $profile_username ?></title>
</head>
<body class="container">
    <header>
        <?php include "./pages/header.php" ?>
    </header>

    <nav>
        <ul>
            <li><h2>Profile of <?= $profile_username ?> 🌟</h2></li>
        </ul>
        <ul>
            <li><a href="friends.php" class="contrast">Back to Connections</a></li>
        </ul>
    </nav>

    <article>
        <header><?= $profile_username ?>'s Songs</header>
        <section>
            <?php if (empty($songs)): ?>
                <p>No songs found for this user.</p>
            <?php else: ?>
                    <article class="pico-background-pink-600">
                        <div class="grid">
                            <div style="text-decoration: underline;">Title</div>
                            <div style="text-decoration: underline;">Artist</div>
                            <div style="text-decoration: underline;">Album</div>
                        </div> 
                    </article>
                <?php foreach ($songs as $song): ?>
                    <article class="pico-background-pink-600">
                        <div class="grid">
                            <div><?= htmlspecialchars($song['title']) ?></div>
                            <div><?= htmlspecialchars($song['artist']) ?></div>
                            <div><?= htmlspecialchars($song['album']) ?></div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </article>

    <article>
        <header><?= $profile_username ?>'s Playlists</header>
        <section>
            <?php if (empty($playlists)): ?>
                <p>No playlists found for this user.</p>
            <?php else: ?>
                <?php foreach ($playlists as $playlist): ?>
                    <article>
                        <h3><?= htmlspecialchars($playlist['name']) ?></h3>
                        <p><?= htmlspecialchars($playlist['description']) ?></p>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </article>
</body>
</html>
