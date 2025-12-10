<?php
session_start();
require 'db.php';

// Check Admin Access
if (!isset($_SESSION['user_role']) || strtolower($_SESSION['user_role']) !== 'admin') {
    die("Access Denied.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

    if ($product_id) {
        try {
            $pdo->beginTransaction();

            // 1. Delete associated reviews first (Cascading delete manually to be safe)
            $stmt = $pdo->prepare("DELETE FROM reviews WHERE product_id = ?");
            $stmt->execute([$product_id]);

            // 2. Delete the product
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$product_id]);

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            // In a real app, you might log this error
        }
    }
}

// Redirect back to the admin panel
header("Location: admin.php");
exit;
