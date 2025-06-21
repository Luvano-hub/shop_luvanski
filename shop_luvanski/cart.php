<?php
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle quantity update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_qty'])) {
        $cart_id = $_POST['cart_id'];
        $new_qty = max(1, (int)$_POST['quantity']);

        $updateStmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $updateStmt->execute([$new_qty, $cart_id, $user_id]);
    }

    if (isset($_POST['remove_item'])) {
        $cart_id = $_POST['cart_id'];
        $removeStmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $removeStmt->execute([$cart_id, $user_id]);
    }
}

// Get cart items
$stmt = $pdo->prepare("
    SELECT c.id AS cart_id, p.id, p.name, p.price, p.image, c.quantity
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll();

$total = 0;
foreach ($items as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Your Cart | Shop Luvanski</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(-45deg, #ffecd2, #fcb69f);
            background-size: 400% 400%;
            animation: gradientMove 15s ease infinite;
        }

        @keyframes gradientMove {
            0% {background-position: 0% 50%;}
            50% {background-position: 100% 50%;}
            100% {background-position: 0% 50%;}
        }

        .back-top {
            position: absolute;
            top: 20px;
            left: 20px;
        }

        .back-btn {
            background-color: #6c757d;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        h2 {
            text-align: center;
            margin-top: 60px;
        }

        .cart-container {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .cart-item {
            display: flex;
            align-items: center;
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }

        .cart-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            margin-right: 20px;
            border-radius: 8px;
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-actions {
            display: flex;
            gap: 5px;
        }

        .quantity-input {
            width: 60px;
            padding: 5px;
            text-align: center;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        .update-btn {
            background-color: #28a745;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .remove-btn {
            background-color: #dc3545;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .checkout-btn {
            background-color: #007BFF;
            color: white;
            font-weight: bold;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .total {
            font-size: 18px;
            text-align: right;
            margin-top: 20px;
            font-weight: bold;
        }

        .actions {
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>
<body>

<div class="back-top">
    <a href="buyer_dashboard.php"><button class="back-btn">⬅ Back to Dashboard</button></a>
</div>

<div class="cart-container">
    <h2>Your Cart</h2>

    <?php if (empty($items)): ?>
        <p>Your cart is empty.</p>
    <?php else: ?>
        <?php foreach ($items as $item): ?>
            <div class="cart-item">
                <img src="<?= htmlspecialchars($item['image'] ?? 'images/placeholder.png') ?>" alt="Product Image">
                <div class="cart-item-details">
                    <div><strong><?= htmlspecialchars($item['name']) ?></strong></div>
                    <div>R<?= number_format($item['price'], 2) ?> each</div>
                </div>

                <form method="POST" class="cart-actions">
                    <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                    <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" class="quantity-input">
                    <button type="submit" name="update_qty" class="update-btn">Update</button>
                    <button type="submit" name="remove_item" class="remove-btn">Remove</button>
                </form>
            </div>
        <?php endforeach; ?>

        <div class="total">Total: R<?= number_format($total, 2) ?></div>
        <div class="actions">
            <a href="checkout.php"><button class="checkout-btn">Proceed to Checkout</button></a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
