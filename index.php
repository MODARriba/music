<?php
session_start();
$conn = new mysqli("localhost", "root", "", "music_app");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

$user_result = $conn->query("SELECT username FROM users WHERE id = $user_id");
$user_data = $user_result->fetch_assoc();
$username = $user_data['username'] ?? 'User';

$search_query = $_GET['search'] ?? '';
$pinned_id = $_GET['pinned_id'] ?? null;
$playlist_id = $_GET['playlist_id'] ?? null;
$escaped_query = $conn->real_escape_string($search_query);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_playlist'])) {
        $playlist_name = $conn->real_escape_string($_POST['playlist_name']);
        $conn->query("INSERT INTO playlists (user_id, name) VALUES ($user_id, '$playlist_name')");
    }

    if (isset($_POST['delete_playlist_id'])) {
        $delete_id = intval($_POST['delete_playlist_id']);
        $conn->query("DELETE FROM playlists WHERE id = $delete_id AND user_id = $user_id");
        $conn->query("DELETE FROM playlist_songs WHERE playlist_id = $delete_id");
    }

    if (isset($_POST['pin_song_id'])) {
        $pin_id = intval($_POST['pin_song_id']);
        $conn->query("UPDATE songs SET pinned = 0 WHERE user_id = $user_id");
        $conn->query("UPDATE songs SET pinned = 1 WHERE id = $pin_id AND user_id = $user_id");
        
        $redirectUrl = "index.php?pinned_id=$pin_id&search=" . urlencode($search_query);
        if ($playlist_id) {
            $redirectUrl .= "&playlist_id=" . intval($playlist_id);
        }
        header("Location: $redirectUrl");
        exit();
    }

    if (isset($_POST['delete_song_id'])) {
        $delete_id = intval($_POST['delete_song_id']);
        $result = $conn->query("SELECT cover_image, music_file FROM songs WHERE id = $delete_id AND user_id = $user_id AND pinned = 0");
        if ($result && $result->num_rows > 0) {
            $song = $result->fetch_assoc();
            if (file_exists($song['cover_image'])) unlink($song['cover_image']);
            if (file_exists($song['music_file'])) unlink($song['music_file']);
            $conn->query("DELETE FROM songs WHERE id = $delete_id AND user_id = $user_id AND pinned = 0");
        }
    }

    if (isset($_POST['add_song'])) {
        $title = $_POST['title'];
        $artist = $_POST['artist'];
        $length = $_POST['length'] ?? '3:45';
        $cover_path = '';
        $music_path = '';

        if (isset($_FILES['cover']) && $_FILES['cover']['error'] === 0) {
            $cover_name = basename($_FILES['cover']['name']);
            $cover_path = "uploads/" . uniqid() . "_" . $cover_name;
            move_uploaded_file($_FILES['cover']['tmp_name'], $cover_path);
        }

        if (isset($_FILES['music']) && $_FILES['music']['error'] === 0) {
            $music_name = basename($_FILES['music']['name']);
            $music_path = "uploads/" . uniqid() . "_" . $music_name;
            move_uploaded_file($_FILES['music']['tmp_name'], $music_path);
        }

        $stmt = $conn->prepare("INSERT INTO songs (user_id, title, artist, length, cover_image, music_file, created_at, pinned) VALUES (?, ?, ?, ?, ?, ?, NOW(), 0)");
        $stmt->bind_param("isssss", $user_id, $title, $artist, $length, $cover_path, $music_path);
        $stmt->execute();
    }

    if (isset($_POST['target_song_id']) && isset($_POST['playlist_ids'])) {
        $song_id = intval($_POST['target_song_id']);
        $conn->query("DELETE FROM playlist_songs WHERE song_id = $song_id");
        foreach ($_POST['playlist_ids'] as $pl_id) {
            $pl_id = intval($pl_id);
            $conn->query("INSERT INTO playlist_songs (playlist_id, song_id) VALUES ($pl_id, $song_id)");
        }
    }
}

if ($playlist_id) {
    $songs_result = $conn->query("SELECT s.* FROM songs s JOIN playlist_songs ps ON s.id = ps.song_id WHERE ps.playlist_id = $playlist_id AND s.user_id = $user_id ORDER BY s.created_at DESC");
    $playlist_name_query = $conn->query("SELECT name FROM playlists WHERE id = $playlist_id AND user_id = $user_id");
    $playlist_name_row = $playlist_name_query->fetch_assoc();
    $selected_playlist_name = $playlist_name_row['name'] ?? 'Playlist Songs';
} elseif ($search_query) {
    $songs_result = $conn->query("SELECT * FROM songs WHERE user_id = $user_id AND (title LIKE '%$escaped_query%' OR artist LIKE '%$escaped_query%') ORDER BY created_at DESC");
} else {
    $songs_result = $conn->query("SELECT * FROM songs WHERE user_id = $user_id ORDER BY created_at DESC");
}

$songs = $songs_result->fetch_all(MYSQLI_ASSOC);

$pinned_song = null;
foreach ($songs as $i => $song) {
    if ($pinned_id && $song['id'] == $pinned_id) {
        $pinned_song = $song;
        unset($songs[$i]);
        break;
    } elseif ($song['pinned']) {
        $pinned_song = $song;
        unset($songs[$i]);
        break;
    }
}
$other_songs = array_values($songs);
$playlists = $conn->query("SELECT * FROM playlists WHERE user_id = $user_id ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

function songPlaylists($conn, $song_id) {
    $res = $conn->query("SELECT playlist_id FROM playlist_songs WHERE song_id = $song_id");
    return array_column($res->fetch_all(MYSQLI_ASSOC), 'playlist_id');
}
?>
<!--html-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MELOFY</title>
    <style>
        html, body { margin: 0; padding: 0; height: 100%; overflow: hidden; font-family: sans-serif; }
        .topbar { background: #2c3e50; color: white; padding: 25px; display: flex; justify-content: space-between; align-items: center; }
        .topbar-left { display: flex; align-items: center; gap: 10px; }
        .topbar-right { font-size: 16px; display: flex; align-items: center; gap: 15px; }
        .topbar a { color: white; text-decoration: none; }
        .topbar a:hover { text-decoration: underline; }
        .logo-icon { width: 30px; height: 30px; }

        .search-box input[type="text"] {
            padding: 6px 10px;
            border-radius: 6px;
            border: none;
            margin-left: 20px;
            width: 200px;
        }

        .left-sidebar { width: 200px; background: #ecf0f1; padding: 20px; height: 100%; box-sizing: border-box; }
        .main-content { flex: 1; padding: 20px; background: #f4f4f4; display: flex; flex-direction: column; height: 100%; overflow: hidden; box-sizing: border-box; }
        .pinned-card { background: white; padding: 15px; margin-bottom: 20px; border-radius: 10px; }
        .pinned-info { display: flex; align-items: center; }
        .pinned-info img { width: 60px; height: 60px; margin-right: 15px; border-radius: 8px; object-fit: cover; }

        .song-list-container { flex: 1; overflow-y: auto; }
        .song-card { display: flex; align-items: center; justify-content: space-between; background: white; padding: 10px; margin-bottom: 15px; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.05); cursor: pointer; }
        .song-info { display: flex; align-items: center; gap: 15px; }
        .song-info img { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; }

        .add-button { position: fixed; bottom: 30px; right: 30px; width: 50px; height: 50px; font-size: 28px; background: #3498db; color: white; border-radius: 50%; border: none; }

        .modal-bg { display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 999; }
        .modal-window { background: white; padding: 40px; border-radius: 16px; width: 500px; position: relative; box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15); }
        .modal-window .close { position: absolute; top: 10px; right: 15px; font-size: 24px; font-weight: bold; color: #aaa; cursor: pointer; }
        .modal-window .close:hover { color: #000; }
        .modal-window input[type="text"],
        .modal-window input[type="file"] {
            width: 100%; padding: 10px; margin: 10px 0; border-radius: 8px; border: 1px solid #ccc;
        }
        .modal-window button { margin-top: 12px; width: 100%; padding: 12px; background: #0072ff; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; }
        .modal-window button:hover { background: #0056cc; }
    </style>
</head>
<body>

<div class="topbar">
    <div class="topbar-left">
        <img src="logo.png" alt="Logo" class="logo-icon">
        <strong>MELOFY</strong>
        <form class="search-box" method="get" id="searchForm" onsubmit="return false;">
            <input type="hidden" name="pinned_id" value="<?= $pinned_song['id'] ?? '' ?>">
            <input type="text" id="searchInput" name="search" placeholder="Search..." value="<?= htmlspecialchars($search_query ?? '') ?>">
        </form>

    </div>
    <div class="topbar-right">
        <span>Hello, <?= htmlspecialchars($username) ?></span> |
        <a href="about.php">About</a> |
        <a href="logout.php">Logout</a>
    </div>
</div>
<!--left side - playlist,liked song,suggestion-->
<div style="display:flex; height: calc(100vh - 60px);">
<div class="left-sidebar">
    <?php if ($playlist_id): ?>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0;"><?= htmlspecialchars($selected_playlist_name) ?></h3>
            <a href="index.php" style="text-decoration: none; color: #333; font-size: 16px;">←</a>
        </div>
    <?php else: ?>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0;">Playlists</h3>
            <span style="cursor: pointer;" onclick="document.getElementById('playlistAddModal').style.display='flex'">⋮</span>
        </div>

        <div style="max-height: 250px; overflow-y: auto; margin-bottom: 20px;">
            <ul class="playlist-list">
                <?php foreach ($playlists as $pl): ?>
                    <li onclick="window.location.href='?playlist_id=<?= $pl['id'] ?>'">🎵 <?= htmlspecialchars($pl['name']) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <h3>Liked Songs</h3>
<div style="max-height: 240px; overflow-y: auto; margin-bottom: 20px;">
    <ul class="playlist-list">
        <?php
        $liked = $conn->query("SELECT * FROM songs WHERE user_id = $user_id AND liked = 1 ORDER BY created_at DESC");
        while ($liked_song = $liked->fetch_assoc()):
        ?>
            <li onclick="playSong(<?= $liked_song['id'] ?>)">💖 <?= htmlspecialchars($liked_song['title']) ?></li>
        <?php endwhile; ?>
    </ul>
</div>


    <h3>Suggestions</h3>
    <div style="max-height: 240px; overflow-y: auto;">
        <ul class="playlist-list">
            <?php 
            $suggestions = array_slice($other_songs, 0, 10);
            foreach ($suggestions as $suggest): ?>
                <li onclick="playSong(<?= $suggest['id'] ?>)">🎧 <?= htmlspecialchars($suggest['title']) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<!--main -->
<div class="main-content">
<?php if ($pinned_song): ?> 
    <div class="pinned-card">
        <div class="pinned-info">
            <img src="<?= htmlspecialchars($pinned_song['cover_image']) ?>" alt="">
            <div>
                <strong><?= htmlspecialchars($pinned_song['title']) ?></strong><br>
                <?= htmlspecialchars($pinned_song['artist']) ?> | <?= htmlspecialchars($pinned_song['length']) ?>
            </div>
        </div>

        <!-- Custom controls -->
        <div class="player-controls" style="margin-bottom: 10px; display: flex; justify-content: center; gap: 20px;">
            <button onclick="navigateSong('prev')" title="Previous" style="padding: 14px 20px; font-size: 28px; border-radius: 12px; background: #0072ff; color: white; border: none; cursor: pointer;">&#9198;</button>
            <button onclick="togglePlayPause()" id="playPauseBtn" title="Play/Pause" style="padding: 14px 20px; font-size: 28px; border-radius: 12px; background: #0072ff; color: white; border: none; cursor: pointer;">⏯️</button>
            <button onclick="navigateSong('next')" title="Next" style="padding: 14px 20px; font-size: 28px; border-radius: 12px; background: #0072ff; color: white; border: none; cursor: pointer;">&#9197;</button>
        </div>

        <!-- ✅ Progress bar goes here -->
        <progress id="progressBar" value="0" max="100" style="width: 100%; height: 10px; border-radius: 8px; margin-bottom: 8px;"></progress>

        <!-- Audio player (no native controls) -->
        <audio id="mainPlayer" autoplay style="width: 100%;">
            <source src="<?= htmlspecialchars($pinned_song['music_file']) ?>" type="audio/mpeg">
        </audio>
    </div>
<?php endif; ?>
    <form method="post" id="navForm" style="display: none;">
        <input type="hidden" name="pin_song_id" id="pinSongId">
    </form>
    <?php if (!$playlist_id): ?>
    <h3>Song List</h3>
    <?php endif; ?>
    <div class="song-list-container" id="songListContainer">
    <ul class="song-list">
    <?php foreach ($other_songs as $index => $song): ?>
<li class="song-card" data-id="<?= $song['id'] ?>">
  <div class="song-info">
    <img src="<?= htmlspecialchars($song['cover_image']) ?>" alt="Cover">
    <div>
      <strong><?= htmlspecialchars($song['title']) ?></strong><br>
      <?= htmlspecialchars($song['artist']) ?> | <?= htmlspecialchars($song['length']) ?>
    </div>
  </div>

  <div class="song-actions">
    <button class="like-btn" data-id="<?= $song['id'] ?>" style="color:<?= $song['liked'] ? 'red' : '#aaa' ?>;">❤️</button>

    <!-- 3-dot playlist button (⋮) and delete form here -->
    <button onclick="openPlaylistModal(<?= $song['id'] ?>)">⋮</button>
    <form method="post" onsubmit="return confirm('Delete this song?');">
      <input type="hidden" name="delete_song_id" value="<?= $song['id'] ?>">
      <button type="submit" title="Delete" style="color: red;">🗑️</button>
    </form>
  </div>
</li>
<?php endforeach; ?>
        </ul>
    </div>
</div>
</div>
<!-- Playlist Modal HTML -->
<div id="playlistAddModal" class="modal-bg">
  <div class="modal-window">
    <span class="close" onclick="document.getElementById('playlistAddModal').style.display='none'">&times;</span>
    <form method="POST">
      <input type="hidden" name="target_song_id" id="targetSongId">
      <h3>Select Playlists</h3>
      <?php foreach ($playlists as $pl): ?>
        <div style="margin: 8px 0;">
          <input type="checkbox" name="playlist_ids[]" value="<?= $pl['id'] ?>" id="pl_<?= $pl['id'] ?>">
          <label for="pl_<?= $pl['id'] ?>"><?= htmlspecialchars($pl['name']) ?></label>
        </div>
      <?php endforeach; ?>
      <button type="submit">Add to Playlist</button>
    </form>
  </div>
</div>
<button class="add-button" onclick="document.getElementById('modalBg').style.display = 'flex';">+</button>
<div id="modalBg" class="modal-bg">
    <div class="modal-window">
        <span class="close" onclick="document.getElementById('modalBg').style.display='none'">&times;</span>xml_error_string    
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="add_song" value="1">
            <input type="text" name="title" placeholder="Song Title" required><br>
            <input type="text" name="artist" placeholder="Artist Name" required><br>
            <label>Cover Image:</label>
            <input type="file" name="cover" accept="image/*" required><br>
            <label>Audio File:</label>
            <input type="file" name="music" accept="audio/*" required><br>
            <button type="submit">Add Song</button>
        </form>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", () => {
  // Add 3-dot menu and clean layout
  document.querySelectorAll(".song-card").forEach(card => {
    const form = card.querySelector("form");
    const songId = card.dataset.id;

    if (form && form.parentElement.classList.contains("song-actions")) return;

    const actionArea = document.createElement("div");
    actionArea.className = "song-actions";
    actionArea.style.display = "flex";
    actionArea.style.gap = "8px";
    actionArea.style.alignItems = "center";

    const moreBtn = document.createElement("button");
    moreBtn.innerHTML = "⋮";
    moreBtn.type = "button";
    moreBtn.title = "Add to Playlist";
    moreBtn.style.cursor = "pointer";
    moreBtn.onclick = (e) => {
      e.stopPropagation();
      openPlaylistModal(songId);
    };

    actionArea.appendChild(moreBtn);
    actionArea.appendChild(form);
    card.appendChild(actionArea);
  });

  // ✅ Search live filtering
  const searchInput = document.getElementById("searchInput");
  if (searchInput) {
    searchInput.addEventListener("input", () => {
      const query = searchInput.value;
      fetch("search_songs.php?q=" + encodeURIComponent(query))
        .then(res => res.text())
        .then(html => {
          const container = document.getElementById("songListContainer");
          container.innerHTML = html;

          // Re-bind like buttons
          document.querySelectorAll(".like-btn").forEach(btn => {
            btn.addEventListener("click", e => {
              e.stopPropagation();
              const songId = btn.dataset.id;
              fetch("like_toggle.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `song_id=${songId}`
              })
              .then(res => res.json())
              .then(data => {
                btn.style.color = data.liked ? 'red' : '#aaa';
              });
            });
          });

          // Re-bind ⋮ playlist modals
          document.querySelectorAll(".song-card").forEach(card => {
            const songId = card.dataset.id;
            const moreBtn = card.querySelector("button[title='Add to Playlist']");
            if (moreBtn) {
              moreBtn.onclick = (e) => {
                e.stopPropagation();
                openPlaylistModal(songId);
              };
            }
          });

          // Re-bind delete confirmation
          document.querySelectorAll(".song-card form").forEach(form => {
            form.onsubmit = () => confirm('Delete this song?');
          });
        });
    });
  }

  // ✅ Player progress and click-to-seek
  const audio = document.getElementById('mainPlayer');
  const progress = document.getElementById('progressBar');
  const playBtn = document.getElementById('playPauseBtn');

  if (audio && playBtn && progress) {
    playBtn.innerHTML = audio.paused ? '▶️' : '⏸️';

    audio.addEventListener('timeupdate', () => {
      const percent = (audio.currentTime / audio.duration) * 100;
      progress.value = percent || 0;
    });

    progress.addEventListener('click', (e) => {
      const width = progress.clientWidth;
      const clickX = e.offsetX;
      const duration = audio.duration;
      if (!isNaN(duration)) {
        audio.currentTime = (clickX / width) * duration;
      }
    });
  }
});

function openPlaylistModal(songId) {
  const modal = document.getElementById("playlistAddModal");
  const songInput = document.getElementById("targetSongId");
  songInput.value = songId;
  modal.style.display = "flex";
}
</script>

<script>
    // ✅ Custom play/pause toggle function
    function togglePlayPause() {
        const player = document.getElementById('mainPlayer');
        const btn = document.getElementById('playPauseBtn');
        if (player.paused) {
            player.play();
            btn.innerHTML = '⏸️'; // show pause icon
        } else {
            player.pause();
            btn.innerHTML = '▶️'; // show play icon
        }
    }
</script>
<script>
    const songIds = <?= json_encode(array_column($other_songs, 'id')) ?>;
    let currentPinnedId = <?= $pinned_song['id'] ?? 'null' ?>;

    // Init history and queue from localStorage
    let songHistory = JSON.parse(localStorage.getItem('songHistory') || '[]');
    let songQueue = JSON.parse(localStorage.getItem('songQueue') || '[]');

    // First load: initialize queue if empty
    if (!Array.isArray(songQueue) || songQueue.length === 0) {
        songQueue = songIds.filter(id => id !== currentPinnedId);
        localStorage.setItem('songQueue', JSON.stringify(songQueue));
    }

    // Remove current if still in queue (to avoid replaying it)
    songQueue = songQueue.filter(id => id !== currentPinnedId);

    function navigateSong(direction) {
        console.log("Current:", currentPinnedId, "| Queue:", songQueue, "| History:", songHistory);

        if (direction === 'next') {
            if (songQueue.length > 0) {
                songHistory.push(currentPinnedId);
                const nextId = songQueue.shift();
                localStorage.setItem('songHistory', JSON.stringify(songHistory));
                localStorage.setItem('songQueue', JSON.stringify(songQueue));
                playSong(nextId);
            } else {
                alert("End of queue!");
            }
        } else if (direction === 'prev') {
            if (songHistory.length > 0) {
                const prevId = songHistory.pop();
                songQueue.unshift(currentPinnedId);
                localStorage.setItem('songHistory', JSON.stringify(songHistory));
                localStorage.setItem('songQueue', JSON.stringify(songQueue));
                playSong(prevId);
            } else {
                alert("No history yet.");
            }
        }
    }

    function playSong(songId) {
    // Only update current, no history push here
    currentPinnedId = songId;

    // Refresh queue with unplayed songs
    const unplayed = songIds.filter(id =>
        id !== currentPinnedId &&
        !songHistory.includes(id) &&
        !songQueue.includes(id)
    );
    songQueue = [...songQueue, ...unplayed];

    localStorage.setItem('songHistory', JSON.stringify(songHistory));
    localStorage.setItem('songQueue', JSON.stringify(songQueue));

    document.getElementById('pinSongId').value = songId;
    document.getElementById('navForm').submit();
}
</script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".like-btn").forEach(btn => {
    btn.addEventListener("click", e => {
      e.stopPropagation(); // prevent triggering playSong
      const songId = btn.dataset.id;

      fetch("like_toggle.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `song_id=${songId}`
      })
      .then(res => res.json())
      .then(data => {
        btn.style.color = data.liked ? 'red' : '#aaa';
      });
    });
  });
});

</script>
</body>
</html>
