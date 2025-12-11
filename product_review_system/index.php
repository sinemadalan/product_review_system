<?php
session_start();
require 'db.php';

// Search Logic
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$params = [];

if ($search_query) {
    // Search Mode
    $sql = "SELECT p.*, 
            (SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.id) as review_count,
            (SELECT AVG(rating) FROM reviews r WHERE r.product_id = p.id) as avg_rating
            FROM products p 
            WHERE p.name LIKE ? OR p.description LIKE ? OR p.category LIKE ?
            ORDER BY p.created_at DESC";
    $params = ["%$search_query%", "%$search_query%", "%$search_query%"];
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Home Page Mode
    
    // 1. Hero Product (Most Reviewed)
    $hero_sql = "SELECT p.*, 
                 (SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.id) as review_count
                 FROM products p 
                 ORDER BY review_count DESC, p.created_at DESC 
                 LIMIT 1";
    $stmt = $pdo->query($hero_sql);
    $hero_product = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Top Reviewed Products
    $top_sql = "SELECT p.*, 
                (SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.id) as review_count,
                (SELECT AVG(rating) FROM reviews r WHERE r.product_id = p.id) as avg_rating
                FROM products p 
                ORDER BY review_count DESC, avg_rating DESC 
                LIMIT 4";
    $stmt = $pdo->query($top_sql);
    $top_reviewed_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Featured Items (Newest)
    $featured_sql = "SELECT p.*, 
                     (SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.id) as review_count,
                     (SELECT AVG(rating) FROM reviews r WHERE r.product_id = p.id) as avg_rating
                     FROM products p 
                     ORDER BY p.created_at DESC 
                     LIMIT 4";
    $stmt = $pdo->query($featured_sql);
    $featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vetta | Home</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- HEADER: LOGO + SEARCH + ACCOUNT -->
    <header class="main-header">
        <div class="logo">
            <a href="index.php" style="text-decoration:none; color:inherit;">Vetta</a>
        </div>

        <div class="search-box">
            <form action="index.php" method="GET" style="display:flex; width:100%;">
                <input type="text" name="q" placeholder="Search for a product, category, or brand" value="<?= htmlspecialchars($search_query) ?>">
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
                    <a href="login.php" style="text-decoration:none; color:inherit; display:flex; align-items:center; gap:8px;">
                        <span class="icon">👤</span>
                        <div class="text">
                            <small>Hello</small>
                            <strong>Log In / Sign Up</strong>
                        </div>
                    </a>
                </div>
            <?php endif; ?>
        </div>

    </header>

    <!-- CATEGORY NAVBAR -->
    <nav class="category-nav">
        <a href="index.php">All Categories</a>
        <a href="index.php?q=Supermarket">Supermarket</a>
        <a href="index.php?q=Cosmetics">Cosmetics</a>
        <a href="index.php?q=Baby">Baby & Mom</a>
        <a href="index.php?q=Sports">Sports & Outdoor</a>
        <a href="index.php?q=Books">Books</a>
        <a href="index.php?q=Auto">Auto & DIY</a>
        <a href="index.php?q=Home">Home & Living</a>
    </nav>

    <main>

        <?php if ($search_query): ?>
            <!-- SEARCH RESULTS -->
            <section class="product-section">
                <div class="section-header">
                    <h2>Search Results for "<?= htmlspecialchars($search_query) ?>"</h2>
                    <a href="index.php">View All</a>
                </div>

                <div class="product-grid">
                    <?php if (count($search_results) > 0): ?>
                        <?php foreach ($search_results as $product): ?>
                            <article class="product-card">
                                <a href="product.php?id=<?= $product['id'] ?>" style="text-decoration:none; color:inherit;">
                                    <div class="product-img">
                                        <?php if ($product['image_data']): ?>
                                            <img src="data:image/jpeg;base64,<?= base64_encode($product['image_data']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="width:100%; height:100%; object-fit:cover; border-radius:12px;">
                                        <?php else: ?>
                                            <div style="width:100%; height:100%; background:#eee; display:flex; align-items:center; justify-content:center; color:#999; border-radius:12px;">No Image</div>
                                        <?php endif; ?>
                                    </div>
                                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                                    <p class="brand"><?= htmlspecialchars($product['category']) ?></p>
                                    <p class="reviews">⭐ <?= number_format($product['avg_rating'], 1) ?> · <?= $product['review_count'] ?> reviews</p>
                                    <p class="price">₺<?= number_format($product['price'], 2) ?></p>
                                </a>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No products found.</p>
                    <?php endif; ?>
                </div>
            </section>

        <?php else: ?>

            <!-- HERO: TOP REVIEWED PRODUCT OF THE DAY -->
            <?php if ($hero_product): ?>
            <section class="hero-section">
                <div class="hero-content">
                    <div class="badge">Most Reviewed</div>
                    <h1>Today's Top Reviewed Product</h1>
                    <p>
                        See the product that received the most reviews today and read what people really think.
                    </p>
                    <div class="hero-buttons">
                        <a href="product.php?id=<?= $hero_product['id'] ?>"><button class="btn primary">Read Top Reviews</button></a>
                        <a href="#featured"><button class="btn ghost">Browse Categories</button></a>
                    </div>
                </div>
                <div class="hero-image-placeholder" style="background:none; border:none; overflow:hidden;">
                    <?php if ($hero_product['image_data']): ?>
                        <img src="data:image/jpeg;base64,<?= base64_encode($hero_product['image_data']) ?>" alt="<?= htmlspecialchars($hero_product['name']) ?>" style="width:100%; height:100%; object-fit:cover; border-radius:20px;">
                    <?php else: ?>
                        <div style="width:100%; height:100%; background:#eee; display:flex; align-items:center; justify-content:center; color:#999; border-radius:20px; min-height:200px;">No Image</div>
                    <?php endif; ?>
                </div>
            </section>
            <?php endif; ?>


            <!-- TOP REVIEWED PRODUCTS SECTION -->
            <section class="product-section">
                <div class="section-header">
                    <h2>Top Reviewed Products</h2>
                    <a href="#">View All Top Reviews</a>
                </div>

                <div class="product-grid">
                    <?php foreach ($top_reviewed_products as $product): ?>
                    <article class="product-card">
                        <a href="product.php?id=<?= $product['id'] ?>" style="text-decoration:none; color:inherit;">
                            <div class="product-img">
                                <?php if ($product['image_data']): ?>
                                    <img src="data:image/jpeg;base64,<?= base64_encode($product['image_data']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="width:100%; height:100%; object-fit:cover; border-radius:12px;">
                                <?php else: ?>
                                    <div style="width:100%; height:100%; background:#eee; display:flex; align-items:center; justify-content:center; color:#999; border-radius:12px;">No Image</div>
                                <?php endif; ?>
                            </div>
                            <h3><?= htmlspecialchars($product['name']) ?></h3>
                            <p class="brand"><?= htmlspecialchars($product['category']) ?></p>
                            <p class="reviews">⭐ <?= number_format($product['avg_rating'], 1) ?> · <?= $product['review_count'] ?> reviews</p>
                            <p class="price">₺<?= number_format($product['price'], 2) ?></p>
                        </a>
                    </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- FEATURED SECTION -->
            <section class="product-section" id="featured">
                <div class="section-header">
                    <h2>Featured Items</h2>
                    <a href="#">View All</a>
                </div>

                <div class="product-grid">
                    <?php foreach ($featured_products as $product): ?>
                    <article class="product-card">
                        <a href="product.php?id=<?= $product['id'] ?>" style="text-decoration:none; color:inherit;">
                            <div class="product-img">
                                <?php if ($product['image_data']): ?>
                                    <img src="data:image/jpeg;base64,<?= base64_encode($product['image_data']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="width:100%; height:100%; object-fit:cover; border-radius:12px;">
                                <?php else: ?>
                                    <div style="width:100%; height:100%; background:#eee; display:flex; align-items:center; justify-content:center; color:#999; border-radius:12px;">No Image</div>
                                <?php endif; ?>
                            </div>
                            <h3><?= htmlspecialchars($product['name']) ?></h3>
                            <p class="brand"><?= htmlspecialchars($product['category']) ?></p>
                            <p class="price">₺<?= number_format($product['price'], 2) ?></p>
                        </a>
                    </article>
                    <?php endforeach; ?>
                </div>
            </section>

        <?php endif; ?>

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
