<?php
session_start();
$conn = new mysqli("localhost", "root", "", "music_app");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Profile</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .profile-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 400px;
            margin: 40px auto;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .profile-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .profile-container p {
            margin: 10px 0;
            font-size: 16px;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <a href="index.php">Home</a>
    <a href="logout.php">Logout</a>
    <a href="#">About</a>
</div>

<!-- Main -->
<div class="main">
    <div class="profile-container">
        <h2>Your Profile</h2>
        <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Account Created:</strong> <?= htmlspecialchars($user['created_at']) ?></p>
    </div>
</div>

</body>
</html>
