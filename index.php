<?php
session_start();

require_once __DIR__ . '/config/db.php';

?>

<!doctype html>
<html lang="en" data-theme="dark">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.pink.min.css">
    <title>Your Personal Music Dashboard 🎶</title>
  </head>
  <body class="container">

    <header>
        <?php include "./pages/header.php" ?>
    </header>

    <main>
        <h1>Welcome to Your Music Dashboard 🎵🎧</h1>

        <p>
            Welcome to your personal music library! 🚀 Here, you can manage your songs 🎶, 
            create custom playlists 🎼, and enjoy your favorite tracks all in one place. 🌟
        </p>

        <p>
            Here's what you can do:
        </p>
        
        <ul>
            <li>🎤 Add your favorite songs to your library</li>
            <li>📑 Create and organize your playlists</li>
            <li>❌ Delete any songs or playlists you no longer need</li>
            <li>🎶 Explore your personal collection anytime, anywhere!</li>
        </ul>

        <p>
            Ready to get started? Click below and let's get the playlist rolling! 🎉
        </p>
        
        <a href="auth/register.php" class="button">🛵 Register Now!</a>

    </main>

    <footer>
        <p>Made by Jose and Mustafa</p>
        <a href="admin/login.php" class="button">🛂 Admin Login</a>
    </footer>

  </body>
</html>
