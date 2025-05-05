<?php
session_start();
$conn = new mysqli("localhost", "root", "", "music_app");

$user_id = $_SESSION['user_id'] ?? 0;
$q = $_GET['q'] ?? '';
$q_escaped = $conn->real_escape_string($q);

$songs = $conn->query("SELECT * FROM songs WHERE user_id = $user_id AND (title LIKE '%$q_escaped%' OR artist LIKE '%$q_escaped%') ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

foreach ($songs as $song): ?>
  <li class="song-card" onclick="playSong(<?= $song['id'] ?>)">
    <div class="song-info">
      <img src="<?= htmlspecialchars($song['cover_image']) ?>" alt="Cover">
      <div>
        <strong><?= htmlspecialchars($song['title']) ?></strong><br>
        <?= htmlspecialchars($song['artist']) ?> | <?= htmlspecialchars($song['length']) ?>
      </div>
    </div>
    <form method="post" onsubmit="return confirm('Delete this song?');">
      <input type="hidden" name="delete_song_id" value="<?= $song['id'] ?>">
      <button type="submit" title="Delete" style="color: red;">🗑️</button>
    </form>
  </li>
<?php endforeach; ?>
