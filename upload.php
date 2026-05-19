<?php
session_start();

require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    die('You must be logged in to upload a library.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];

    // Handle file upload
    if (isset($_FILES['library_file']) && $_FILES['library_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['library_file']['tmp_name'];
        $file_data = file_get_contents($file_tmp);
        $songs = json_decode($file_data, true);

        if (is_array($songs)) {
            foreach ($songs as $song) {
                $title = $song['title'];
                $artist = $song['artist'];
                $album = $song['album'] ?? null;

                // Insert song into the `songs` table
                $stmt = $conn->prepare("INSERT IGNORE INTO songs (owner_id, title, artist, album) VALUES (?, ?, ?, ?)");
                $stmt->bind_param('isss', $user_id, $title, $artist, $album);
                $stmt->execute();

                $song_id = $stmt->insert_id ?: $conn->query("SELECT id FROM songs WHERE owner_id = $user_id AND title = '$title' AND artist = '$artist'")->fetch_assoc()['id'];

                // Insert into user_library
                $stmt = $conn->prepare("INSERT IGNORE INTO user_library (user_id, song_id) VALUES (?, ?)");
                $stmt->bind_param('ii', $user_id, $song_id);
                $stmt->execute();
            }
            echo 'Library uploaded successfully!';
        } else {
            echo 'Invalid file format. Please upload a JSON file.';
        }
    } else {
        echo 'File upload failed.';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark" />
    <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.pink.min.css"
    >
    <title>Upload Library</title>
</head>
<body class="container">
    <header>
        <?php include "./pages/header.php"?>
    </header>
    <h2>Upload Your Music Library</h2>
    <form method="POST" enctype="multipart/form-data">
        <label for="library_file">Upload JSON File:</label>
        <input type="file" name="library_file" id="library_file" accept=".json" required>
        <button type="submit">Upload</button>
    </form>
</body>
</html>
