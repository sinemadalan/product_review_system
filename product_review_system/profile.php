<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($name) || empty($email)) {
        $error = "Name and Email are required.";
    } else {
        // Check if email is taken by another user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            $error = "Email already in use.";
        } else {
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
                $stmt->execute([$name, $email, $hashed_password, $user_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                $stmt->execute([$name, $email, $user_id]);
            }
            $_SESSION['user_name'] = $name; // Update session name
            $success = "Profile updated successfully.";
        }
    }
}

// Fetch User Data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch User Reviews
$stmt = $pdo->prepare("SELECT r.*, p.name as product_name FROM reviews r JOIN products p ON r.product_id = p.id WHERE r.user_id = ? ORDER BY r.created_at DESC");
$stmt->execute([$user_id]);
$my_reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | Soft Market</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .profile-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 2rem;
        }
        .profile-section {
            background: #fff;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            border: 1px solid #e3e7f5;
        }
        .profile-section h2 {
            margin-top: 0;
            margin-bottom: 1.5rem;
            font-size: 1.2rem;
            color: #1f2940;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-size: 0.9rem;
            color: #666;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }
        .alert {
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        .alert.success { background: #e6fffa; color: #2c7a7b; }
        .alert.error { background: #fff5f5; color: #c53030; }
        
        .review-item {
            border-bottom: 1px solid #eee;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }
        .review-item:last-child { border-bottom: none; margin-bottom: 0; }
        .review-item h4 { margin: 0 0 5px; font-size: 1rem; }
        .review-item .meta { font-size: 0.8rem; color: #999; margin-bottom: 5px; }
        .review-item p { font-size: 0.95rem; color: #444; line-height: 1.5; margin: 0; }
        .star-rating { color: #f1c40f; font-size: 0.9rem; }
    </style>
</head>
<body>

    <!-- HEADER (Reused) -->
    <header class="main-header">
        <div class="logo"><a href="index.php" style="text-decoration:none; color:inherit;">soft<span>market</span></a></div>
        <div class="search-box">
            <form action="index.php" method="GET" style="display:flex; width:100%;">
                <input type="text" name="q" placeholder="Search for products, categories or brands">
                <button type="submit">Search</button>
            </form>
        </div>
        <div class="header-actions">
             <div class="header-link user-dropdown" onclick="this.querySelector('.user-dropdown-menu').classList.toggle('show')">
                <span class="icon">👤</span>
                <div class="text">
                    <small>Welcome,</small>
                    <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong>
                </div>
                <div class="user-dropdown-menu">
                    <a href="profile.php">Profile</a>
                    <?php if (isset($_SESSION['user_role']) && strtolower($_SESSION['user_role']) === 'admin'): ?>
                        <a href="admin.php">Admin Panel</a>
                    <?php endif; ?>
                    <a href="logout.php">Log Out</a>
                </div>
            </div>
            <div class="header-link">
                <span class="icon">💙</span>
                <div class="text">
                    <small>My Favorites</small>
                    <strong>My List</strong>
                </div>
            </div>
        </div>
    </header>

    <main>
        <div class="profile-container">
            <!-- LEFT COLUMN: Account Settings -->
            <div class="profile-section">
                <h2>Profile Settings</h2>
                <?php if ($success): ?>
                    <div class="alert success"><?= $success ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert error"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label>Name Surname</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>New Password (Leave blank if you don't want to change)</label>
                        <input type="password" name="password" placeholder="******">
                    </div>
                    <button type="submit" name="update_profile" class="btn primary" style="width:100%;">Update</button>
                </form>
            </div>

            <!-- RIGHT COLUMN: My Comments -->
            <div class="profile-section">
                <h2>My Reviews (<?= count($my_reviews) ?>)</h2>
                <?php if (count($my_reviews) > 0): ?>
                    <?php foreach ($my_reviews as $review): ?>
                        <div class="review-item">
                            <h4><a href="product.php?id=<?= $review['product_id'] ?>" style="text-decoration:none; color:inherit;"><?= htmlspecialchars($review['product_name']) ?></a></h4>
                            <div class="meta">
                                <span class="star-rating"><?= str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']) ?></span>
                                &bull; <?= date('d.m.Y H:i', strtotime($review['created_at'])) ?>
                            </div>
                            <p><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color:#777;">You have not made any reviews yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        // Close dropdown when clicking outside
        window.onclick = function(event) {
            if (!event.target.closest('.user-dropdown')) {
                var dropdowns = document.getElementsByClassName("user-dropdown-menu");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }
    </script>
</body>
</html>
