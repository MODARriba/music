<?php
session_start();
$conn = new mysqli("localhost", "root", "", "music_app");

$user_id = $_SESSION['user_id'] ?? 0;
$song_id = intval($_POST['song_id']);

$result = $conn->query("SELECT liked FROM songs WHERE id = $song_id AND user_id = $user_id");
if ($row = $result->fetch_assoc()) {
    $newStatus = $row['liked'] ? 0 : 1;
    $conn->query("UPDATE songs SET liked = $newStatus WHERE id = $song_id AND user_id = $user_id");
    echo json_encode(['liked' => $newStatus]);
}
