<?php

// Database connection
require_once __DIR__ . '/config/db.php';

// Get the HTTP method
$method = $_SERVER['REQUEST_METHOD'];

// Route based on HTTP method
switch ($method) {
    case 'POST': // CREATE
        createSong($conn);
        break;
    case 'PUT': // UPDATE
        parse_str(file_get_contents("php://input"), $_PUT); // Parse PUT data
        updateSong($conn, $_PUT);
        break;
    case 'DELETE': // DELETE
        parse_str(file_get_contents("php://input"), $_DELETE); // Parse DELETE data
        deleteSong($conn, $_DELETE);
        break;
    default:
        echo json_encode(["error" => "Unsupported HTTP method"]);
        break;
}

$conn->close();

// Create a new song
function createSong($conn) {
    $owner_id = $_POST['owner_id'] ?? null;
    $title = $_POST['title'] ?? null;
    $artist = $_POST['artist'] ?? null;
    $album = $_POST['album'] ?? null;

    if (!$owner_id || !$title || !$artist || !$album) {
        http_response_code(400); // Bad request
        echo json_encode([
            "error" => "All fields are required!",
            "data" => [
                "owner_id" => $owner_id,
                "title" => $title,
                "artist" => $artist,
                "album" => $album
            ]
        ]);
        return;
    }

    $stmt = $conn->prepare("INSERT INTO songs (owner_id, title, artist, album) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $owner_id, $title, $artist, $album);

    if ($stmt->execute()) {
        http_response_code(201); // Internal server error
        echo json_encode(["success" => true, "song_id" => $stmt->insert_id]);
    } else {
        http_response_code(500); // Internal server error
        echo json_encode(["error" => $stmt->error]);
    }

    $stmt->close();
}

// Update an existing song
function updateSong($conn, $data) {
    $id = $data['id'] ?? null;
    $title = $data['title'] ?? null;
    $artist = $data['artist'] ?? null;
    $album = $data['album'] ?? null;

    if (!$id || !$title || !$artist) {
        http_response_code(400); // Bad request
        echo json_encode(["error" => "Missing required fields"]);
        return;
    }

    $stmt = $conn->prepare("UPDATE songs SET title = ?, artist = ?, album = ? WHERE id = ?");
    $stmt->bind_param("sssi", $title, $artist, $album, $id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        http_response_code(500); // Internal server error
        echo json_encode(["error" => $stmt->error]);
    }

    $stmt->close();
}

// Delete a song
function deleteSong($conn, $data) {
    $id = $data['id'] ?? null;

    if (!$id) {
        http_response_code(400); // Bad request
        echo json_encode(["error" => "Missing song ID"]);
        return;
    }

    $stmt = $conn->prepare("DELETE FROM songs WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        http_response_code(500); // Internal server error
        echo json_encode(["error" => $stmt->error]);
    }

    $stmt->close();
}
?>
