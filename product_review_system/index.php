<?php
session_start();
require 'db.php';

// Search Logic
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$sql = "SELECT * FROM products";
$params = [];

if ($search_query) {
    $sql .= " WHERE name LIKE ? OR description LIKE ? OR category LIKE ?";
    $params = ["%$search_query%", "%$search_query%", "%$search_query%"];
}

$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soft Market | Home</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- HEADER: LOGO + ARAMA + HESAP/SEPET -->
    <header class="main-header">
        <div class="logo"><a href="index.php" style="text-decoration:none; color:inherit;">soft<span>market</span></a></div>

        <div class="search-box">
            <form action="index.php" method="GET" style="display:flex; width:100%;">
                <input type="text" name="q" placeholder="Search products, categories or brands" value="<?= htmlspecialchars($search_query) ?>">
                <button type="submit">Search</button>
            </form>
        </div>

        <div class="header-actions">
            <?php if (isset($_SESSION['user_id'])): ?>
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
            <?php else: ?>
                <div class="header-link">
                    <a href="login.php" style="text-decoration:none; color:inherit; display:flex; align-items:center;">
                        <span class="icon">👤</span>
                        <div class="text">
                            <small>Hello</small>
                            <strong>Login / Register</strong>
                        </div>
                    </a>
                </div>
            <?php endif; ?>
            
            <div class="header-link">
                <span class="icon">💙</span>
                <div class="text">
                    <small>My Favorites</small>
                    <strong>My List</strong>
                </div>
            </div>
        </div>
    </header>

    <!-- KATEGORİ NAVBARI -->
    <nav class="category-nav">
        <a href="index.php">All Categories</a>
        <a href="index.php?q=Electronics">Electronics</a>
        <a href="index.php?q=Moda">Fashion</a>
        <a href="index.php?q=Supermarket">Supermarket</a>
        <a href="index.php?q=Home">Home & Living</a>
        <a href="index.php?q=Beauty">Beauty</a>
        <a href="index.php?q=Anne">Mother & Baby</a>
        <a href="index.php?q=Sport">Sport & Outdoor</a>
        <a href="index.php?q=Book">Book</a>
        <a href="index.php?q=Auto">Auto & Home Market</a>
    </nav>

    <main>

        <!-- ANA KAMPANYA BANNER -->
        <section class="hero-section">
            <div class="hero-content">
                <div class="badge">Günün Fırsatı</div>
                <h1>Soft ve Konforlu Alışveriş Deneyimi</h1>
                <p>
                    Yüzlerce kategori, binlerce ürün.  
                    Sade, modern ve göz yormayan bir tasarımla alışverişe başla.
                </p>
                <div class="hero-buttons">
                    <button class="btn primary">See All</button>
                    <button class="btn ghost">Most Popular</button>
                </div>
            </div>
            <div class="hero-image-placeholder">
                <span>Hero Image Placeholder</span>
            </div>
        </section>

        <!-- GÜNÜN FIRSATLARI -->
        <section class="product-section">
            <div class="section-header">
                <h2><?= $search_query ? 'Arama Sonuçları' : 'Günün Fırsatları' ?></h2>
                <a href="index.php">See All</a>
            </div>

            <div class="product-grid">
                <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $product): ?>
                        <article class="product-card">
                            <a href="product.php?id=<?= $product['id'] ?>" style="text-decoration:none; color:inherit;">
                                <div class="product-img">
                                    <?php if ($product['image_data']): ?>
                                        <img src="data:image/jpeg;base64,<?= base64_encode($product['image_data']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="width:100%; height:100%; object-fit:cover;">
                                    <?php else: ?>
                                        <div style="width:100%; height:100%; background:#eee; display:flex; align-items:center; justify-content:center; color:#999;">No Image</div>
                                    <?php endif; ?>
                                </div>
                                <span class="badge-discount">%10</span>
                                <h3><?= htmlspecialchars($product['name']) ?></h3>
                                <p class="brand"><?= htmlspecialchars($product['category']) ?></p>
                                <p class="price">₺<?= number_format($product['price'], 2) ?></p>
                            </a>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No products found.</p>
                <?php endif; ?>
            </div>
        </section>

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
