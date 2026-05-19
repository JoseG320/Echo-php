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
    <title>About 🎸 Echo</title>
  </head>
  <body class="container">

    <header>
        <?php include "./pages/header.php" ?>
    </header>

    <main>
        <h1>About Echo 🎸</h1>

        <article>
            <header>What is Echo?</header>
            <p>
                Echo is a personal music library and social sharing platform built for music lovers.
                Manage your songs, build playlists, and discover what you have in common with friends.
                All in one place.
            </p>
            <p>
                Originally built in 2024 by Jose and Mustafa as a project for CSC335.
            </p>
        </article>

        <article>
            <header>✨ Features</header>
            <div class="grid">
                <div>
                    <article>
                        <header>🎤 Your Library</header>
                        <p>Add songs by title, artist, and album. Your personal collection, always at hand.</p>
                    </article>
                </div>
                <div>
                    <article>
                        <header>📑 Playlists</header>
                        <p>Create custom playlists, name them, give them a vibe, and fill them with your favourite tracks.</p>
                    </article>
                </div>
            </div>
            <div class="grid">
                <div>
                    <article>
                        <header>📡 Connections</header>
                        <p>Follow other users and explore their libraries. Music is better when it's shared.</p>
                    </article>
                </div>
                <div>
                    <article>
                        <header>🌈 Playlist Comparator</header>
                        <p>Compare your playlists with a friend's and find the songs you both love, or the ones only you know.</p>
                    </article>
                </div>
            </div>
        </article>

        <article>
            <header>🛠️ Built With</header>
            <ul>
                <li>PHP 8.2 + Apache</li>
                <li>MySQL 8.0</li>
                <li><a href="https://picocss.com" target="_blank" class="contrast">PicoCSS</a> for styling</li>
                <li>Docker</li>
            </ul>
        </article>

        <article>
            <header>👥 The Team</header>
            <p>Made with 🎶 by <strong>Jose</strong> and <strong>Mustafa</strong>.</p>
        </article>

    </main>

    <footer>
        <p>Made by Jose and Mustafa</p>
        <a href="auth/register.php" class="button">🛵 Join Echo</a>
    </footer>

  </body>
</html>
