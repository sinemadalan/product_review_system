<?php
session_start();
require 'db.php'; // Veritabanı bağlantısı

// ID kontrolü ve ürün çekme
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Product not found.");
}

// Handle Review Submission (Yorum Gönderme İşlemi)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review']) && isset($_SESSION['user_id'])) {
    
    // --- [GÜVENLİK KONTROLÜ BAŞLANGIÇ] ---
    // Önce içeride kaç yorum var bir sayalım
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE product_id = ?");
    $stmtCount->execute([$id]);
    $currentCount = $stmtCount->fetchColumn();

    // Eğer sayı 10 veya daha fazlaysa işlemi iptal et (Backend Koruması)
    if ($currentCount >= 10) {
        echo "<script>
            alert('Whoops! This product has reached its review limit (Max 10). No more room for more comments.');
            window.location.href='product.php?id=$id';
        </script>";
        exit;
    }
    // --- [GÜVENLİK KONTROLÜ BİTİŞ] ---

    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);
    
    if ($rating >= 1 && $rating <= 5 && !empty($comment)) {
        $stmt = $pdo->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id, $_SESSION['user_id'], $rating, $comment]);
        header("Location: product.php?id=$id"); // Refresh to show new review
        exit;
    }
}

// Fetch Reviews (Yorumları Listeleme)
$stmt = $pdo->prepare("SELECT r.*, u.name as user_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.created_at DESC");
$stmt->execute([$id]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> | Vetta</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Simple inline styles for product detail specific layout */
        .product-detail-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
        }
        .detail-image {
            text-align: center;
        }
        .detail-image img {
            width: 100%;
            max-width: 400px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .detail-info h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #333;
        }
        .detail-price {
            font-size: 2rem;
            color: #2c3e50;
            font-weight: bold;
            margin-bottom: 1.5rem;
        }
        .detail-desc {
            font-size: 1.1rem;
            line-height: 1.6;
            color: #666;
            margin-bottom: 2rem;
        }
        .reviews-section {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 1rem;
        }
        .review-card {
            background: #fff;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #eee;
        }
        .review-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        .star-rating { color: #f1c40f; }

        /* Interactive Star Rating Styles */
        .rate {
            float: left;
            height: 46px;
            padding: 0 10px;
        }
        .rate:not(:checked) > input {
            position:absolute;
            opacity: 0;
            pointer-events: none;
            width: 0;
            height: 0;
        }
        .rate:not(:checked) > label {
            float:right;
            width:1em;
            overflow:hidden;
            white-space:nowrap;
            cursor:pointer;
            font-size:30px;
            color:#ccc;
        }
        .rate:not(:checked) > label:before {
            content: '★ ';
        }
        .rate > input:checked ~ label {
            color: #ffc700;    
        }
        .rate:not(:checked) > label:hover,
        .rate:not(:checked) > label:hover ~ label {
            color: #deb217;  
        }
        .rate > input:checked + label:hover,
        .rate > input:checked + label:hover ~ label,
        .rate > input:checked ~ label:hover,
        .rate > input:checked ~ label:hover ~ label,
        .rate > label:hover ~ input:checked ~ label {
            color: #c59b08;
        }
        .delete-btn {
            background: none;
            border: none;
            color: #e74c3c;
            cursor: pointer;
            font-size: 1.1rem;
            padding: 0 5px;
            transition: color 0.2s;
        }
        .delete-btn:hover {
            color: #c0392b;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

    <header class="main-header">
        <div class="logo"><a href="index.php" style="text-decoration:none; color:inherit;">Vetta</a></div>
        <div class="search-box">
            <form action="index.php" method="GET" style="display:flex; width:100%;">
                <input type="text" name="q" placeholder="Search products, categories or brands">
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
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="header-link">
                    <a href="login.php" style="text-decoration:none; color:inherit;">
                        <span class="icon">👤</span>
                        <div class="text"><strong>Login</strong></div>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <main>
        <div class="product-detail-container">
            <div class="detail-image">
                <?php if ($product['image_data']): ?>
                    <img src="data:image/jpeg;base64,<?= base64_encode($product['image_data']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                <?php else: ?>
                    <div style="width:100%; height:400px; background:#eee; display:flex; align-items:center; justify-content:center; color:#999; border-radius:12px;">No Image</div>
                <?php endif; ?>
            </div>
            <div class="detail-info">
                <h1><?= htmlspecialchars($product['name']) ?></h1>
                <p class="detail-price">₺<?= number_format($product['price'], 2) ?></p>
                <div class="detail-desc">
                    <?= nl2br(htmlspecialchars($product['description'])) ?>
                </div>
                <p><strong>Category:</strong> <?= htmlspecialchars($product['category']) ?></p>
            </div>
        </div>

        <div class="reviews-section">
            <h2>Reviews (<?= count($reviews) ?>)</h2>
            
            <?php 
            // Yorum sayısını al
            $reviewCount = count($reviews); 
            ?>

            <?php if ($reviewCount >= 10): ?>
                <div style="background: #fff3cd; color: #856404; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem; border: 1px solid #ffeeba; text-align: center;">
                    <h3 style="margin-top:0;">⛔ Review Limit Reached!</h3>
                    <p>This product has reached it's review limit (10/10 Reviews). The review section is officially closed. You can look, but you can't make new reviews.</p>
                </div>

            <?php elseif (isset($_SESSION['user_id'])): ?>
                <div class="review-form" style="margin-bottom: 2rem; background: #f9f9f9; padding: 1.5rem; border-radius: 8px;">
                    <h3>Leave a Review</h3>
                    <form method="POST">
                        <div style="margin-bottom:10px; overflow:hidden;">
                            <label style="display:block; margin-bottom:5px;">Rating:</label>
                            <div class="rate">
                                <input type="radio" id="star5" name="rating" value="5" required />
                                <label for="star5" title="text">5 stars</label>
                                <input type="radio" id="star4" name="rating" value="4" />
                                <label for="star4" title="text">4 stars</label>
                                <input type="radio" id="star3" name="rating" value="3" />
                                <label for="star3" title="text">3 stars</label>
                                <input type="radio" id="star2" name="rating" value="2" />
                                <label for="star2" title="text">2 stars</label>
                                <input type="radio" id="star1" name="rating" value="1" />
                                <label for="star1" title="text">1 star</label>
                            </div>
                        </div>
                        <div style="clear:both;"></div>
                        <textarea name="comment" rows="3" placeholder="Write your review here..." required style="width:100%; padding:10px; margin-bottom:10px; border:1px solid #ddd; border-radius:4px;"></textarea>
                        <button type="submit" name="submit_review" class="btn small">Send</button>
                    </form>
                </div>

            <?php else: ?>
                <p><a href="login.php">Login</a> to leave a review.</p>
            <?php endif; ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review-card">
                    <div class="review-header">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <strong><?= htmlspecialchars($review['user_name']) ?></strong>
                            <span class="star-rating"><?= str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']) ?></span>
                        </div>
                        <?php if (isset($_SESSION['user_id']) && (
                            (isset($_SESSION['user_role']) && strtolower($_SESSION['user_role']) === 'admin') || 
                            $_SESSION['user_id'] == $review['user_id']
                        )): ?>
                            <button type="button" class="delete-btn" title="Delete Comment" onclick="confirmDelete(<?= $review['id'] ?>, <?= $product['id'] ?>)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16">
                                    <path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1H2.5zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5zM8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5zm3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0z"/>
                                </svg>
                            </button>
                        <?php endif; ?>
                    </div>
                    <p><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                    <small style="color:#999;"><?= date('d.m.Y H:i', strtotime($review['created_at'])) ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <script>
        function confirmDelete(reviewId, productId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you really want to delete this review? This action cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'delete_comment.php';
                    
                    const rInput = document.createElement('input');
                    rInput.type = 'hidden';
                    rInput.name = 'review_id';
                    rInput.value = reviewId;
                    form.appendChild(rInput);

                    const pInput = document.createElement('input');
                    pInput.type = 'hidden';
                    pInput.name = 'product_id';
                    pInput.value = productId;
                    form.appendChild(pInput);

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }


        // Close dropdown or modal when clicking outside
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