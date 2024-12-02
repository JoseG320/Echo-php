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
// Handle song creation (POST)
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


// fetch the the users song library
try {
    $fetchSongs = $conn->prepare("select * from songs where owner_id = ? order by id desc");
    $fetchSongs->bind_param("i", $current_user_id);
    $fetchSongs->execute();

    $result = $fetchSongs->get_result();
    $songs = $result->fetch_all(MYSQLI_ASSOC);

    $fetchSongs->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
};

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
    <title>Dashboard</title>
</head>
<body class="container">
    <header>
        <?php include "./pages/header.php"?>
    </header>
    <!-- Test Page to show who is logged in. To be changed into a new page. -->
    <h2>Welcome, <?php echo htmlspecialchars($current_username); ?>!</h2>

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
        </form>
        
        <footer style="max-height: 500px; overflow-y: auto;" >
        <?php if (isset($error_message)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        
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
                            <form action="" method="POST">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="song_id" value="<?= htmlspecialchars($song['id']) ?>">
                                <input type="submit" value="Edit">
                            </form>
                            <!-- DELETE BUTTON -->
                            <form action="" method="POST">
                                <input type="hidden" name="action" value="deleteSong">
                                <input type="hidden" name="song_id" value="<?= htmlspecialchars($song['id']) ?>">
                                <input type="submit" value="Delete">
                            </form>
                        </div>
                    </div>

                </article>
            <?php endforeach; ?>
        <?php endif; ?>
        </footer>
    </article>
    
</body>
</html>
