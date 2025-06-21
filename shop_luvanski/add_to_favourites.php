<?php
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $userId = $_SESSION['user_id'];
    $productId = $_POST['product_id'];

    try {
        // Check if already favorited
        $checkStmt = $pdo->prepare("SELECT id FROM favourites WHERE user_id = ? AND product_id = ?");
        $checkStmt->execute([$userId, $productId]);
        
        if ($checkStmt->rowCount() === 0) {
            // Insert new favourite
            $insertStmt = $pdo->prepare("INSERT INTO favourites (user_id, product_id) VALUES (?, ?)");
            $insertStmt->execute([$userId, $productId]);
            $_SESSION['success'] = "Product added to favourites!";
        } else {
            $_SESSION['error'] = "Product already in favourites!";
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

// Redirect back to previous page
header("Location: " . $_SERVER['HTTP_REFERER']);
exit();