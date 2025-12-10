<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $review_id = isset($_POST['review_id']) ? (int)$_POST['review_id'] : 0;
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

    if ($review_id && $product_id) {
        // Fetch the review to check ownership
        $stmt = $pdo->prepare("SELECT user_id FROM reviews WHERE id = ?");
        $stmt->execute([$review_id]);
        $review = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($review) {
            $is_admin = isset($_SESSION['user_role']) && strtolower($_SESSION['user_role']) === 'admin';
            $is_owner = $review['user_id'] == $_SESSION['user_id'];

            if ($is_admin || $is_owner) {
                $deleteStmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
                $deleteStmt->execute([$review_id]);
            }
        }
    }
    
    // Redirect back to the product page
    header("Location: product.php?id=$product_id");
    exit;
} else {
    // If accessed directly or without login, redirect to home
    header("Location: index.php");
    exit;
}
