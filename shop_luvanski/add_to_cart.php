<?php
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'] ?? null;

if ($product_id) {
    // Check if item already in cart
    $check = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $check->execute([$user_id, $product_id]);
    $existing = $check->fetch();

    if ($existing) {
        // If it exists, increase quantity
        $update = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
        $update->execute([$user_id, $product_id]);
    } else {
        // Otherwise, insert new row
        $insert = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
        $insert->execute([$user_id, $product_id]);
    }
}

header("Location: buyer_dashboard.php");
exit();
?>
