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
    $price = $_POST['price'];
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
    <title>Admin Panel | Soft Market</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Modal Styles (Reused) */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1000; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgba(0,0,0,0.5); 
            backdrop-filter: blur(4px);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto; 
            padding: 2rem;
            border: 1px solid #888;
            width: 90%;
            max-width: 400px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            text-align: center;
            animation: fadeIn 0.3s;
        }
        @keyframes fadeIn {
            from {opacity: 0; transform: translateY(-20px);}
            to {opacity: 1; transform: translateY(0);}
        }
        .modal-actions {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .btn-cancel {
            background-color: #ccc;
            color: #333;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-delete {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-cancel:hover { background-color: #bbb; }
        .btn-delete:hover { background-color: #c0392b; }

        .action-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            color: #e74c3c;
            transition: color 0.2s;
        }
        .action-btn:hover { color: #c0392b; }
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
        <a href="index.php?q=Elektronik">Electronics</a>
        <a href="index.php?q=Moda">Fashion</a>
        <a href="index.php?q=Süpermarket">Supermarket</a>
        <a href="index.php?q=Ev">Home & Living</a>
        <a href="index.php?q=Kozmetik">Beauty</a>
        <a href="index.php?q=Anne">Mother & Baby</a>
        <a href="index.php?q=Spor">Sports & Outdoor</a>
        <a href="index.php?q=Kitap">Books</a>
        <a href="index.php?q=Oto">Auto & Home Market</a>
    </nav>

    <main class="admin-container">
        <div class="section-header">
            <h2>Admin Panel</h2>
        </div>

        <?php if ($message): ?>
            <div style="padding: 1rem; background: #d4edda; color: #155724; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #c3e6cb;">
                <?= htmlspecialchars($message) ?>
            </div>
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
                    <label>Price (₺)</label>
                    <input type="number" step="0.01" name="price" required>
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
                                <button class="action-btn" onclick="openDeleteModal(<?= $product['id'] ?>)" title="Delete Product">
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

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3 style="margin-top:0;">Delete Product?</h3>
            <p>Are you sure you want to delete this product? This will also delete all associated reviews. This action cannot be undone.</p>
            <div class="modal-actions">
                <button class="btn-cancel" onclick="closeModal()">Cancel</button>
                <form method="POST" action="delete_product.php" style="margin:0;">
                    <input type="hidden" name="product_id" id="modal_product_id">
                    <button type="submit" class="btn-delete">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Modal functions
        var modal = document.getElementById("deleteModal");

        function openDeleteModal(productId) {
            document.getElementById('modal_product_id').value = productId;
            modal.style.display = "block";
        }

        function closeModal() {
            modal.style.display = "none";
        }

        // Close dropdown or modal when clicking outside
        window.onclick = function(event) {
            // Close Modal
            if (event.target == modal) {
                closeModal();
            }

            // Close Dropdown
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
