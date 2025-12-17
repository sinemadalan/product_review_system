<?php
session_start();
require 'db.php';

// Check Admin Access
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Access Denied. You must be an admin to view this page. <a href='index.php'>Go Home</a>");
}

$message = '';


// Handle Product Addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = preg_replace('/[^\d.]/', '', $_POST['price']); // Sanitize price: remove anything that is not a digit or dot.
    $category = trim($_POST['category']);
    
    // Image Upload Handling
    $image_data = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image_data = file_get_contents($_FILES['image']['tmp_name']);
    }

    if (!empty($name) && !empty($price)) {
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, category, image_data) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $description, $price, $category, $image_data])) {
            $message = "Product added successfully!";
        } else {
            $message = "Error adding product.";
        }
    } else {
        $message = "Name and Price are required.";
    }
}

// Fetch Products for List
$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | Vetta</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>


    <!-- HEADER (Reused) -->
    <header class="main-header">
        <div class="logo"><a href="index.php" style="text-decoration:none; color:inherit;">Vetta</a></div>

        <div class="search-box">
            <form action="index.php" method="GET" style="display:flex; width:100%;">
                <input type="text" name="q" placeholder="Search for products, categories or brands">
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
        </div>
    </header>

    <!-- KATEGORİ NAVBARI -->
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

    <main class="admin-container">
        <div class="section-header">
            <h2>Admin Panel</h2>
        </div>

        <?php if ($message): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'info',
                        title: 'Notification',
                        text: '<?= htmlspecialchars($message) ?>',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#28a745'
                    });
                });
            </script>
        <?php endif; ?>

        <div class="admin-card">
            <h2>Add New Product</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="add_product" value="1">
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <input type="text" name="category">
                </div>
                <div class="form-group">
                    <label>Price</label>
                    <input type="text" name="price" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="4"></textarea>
                </div>
                <div class="form-group">
                    <label>Product Image</label>
                    <input type="file" name="image" accept="image/*">
                </div>
                <button type="submit" class="btn primary">Add Product</button>
            </form>
        </div>

        <div class="admin-card">
            <h2>Product List</h2>
            <table class="product-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Date Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <?php if ($product['image_data']): ?>
                                    <img src="data:image/jpeg;base64,<?= base64_encode($product['image_data']) ?>" class="thumbnail">
                                <?php else: ?>
                                    <div class="thumbnail" style="display:flex; align-items:center; justify-content:center; color:#ccc; font-size:10px;">No Img</div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td><?= htmlspecialchars($product['category']) ?></td>
                            <td>₺<?= number_format($product['price'], 2) ?></td>
                            <td><?= date('d.m.Y', strtotime($product['created_at'])) ?></td>
                            <td style="text-align:center;">
                                <button class="action-btn" onclick="confirmDelete(<?= $product['id'] ?>)" title="Delete Product" style="background: none; border: none; cursor: pointer; color: #e74c3c;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16">
                                        <path d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1H2.5zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5zM8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5zm3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0z"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        </div>
    </main>

    <script>
        function confirmDelete(productId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this! All associated reviews will also be deleted.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create a form programmatically and submit it
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'delete_product.php';
                    
                    const hiddenField = document.createElement('input');
                    hiddenField.type = 'hidden';
                    hiddenField.name = 'product_id';
                    hiddenField.value = productId;
                    
                    form.appendChild(hiddenField);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }


        // Close dropdown or modal when clicking outside
        window.onclick = function(event) {
            // Close Modal
            // Close Dropdown (only logic left after removing modal)
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
