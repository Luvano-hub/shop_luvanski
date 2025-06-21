<?php
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit();
}

// Fetch orders for current user
$orderStmt = $pdo->prepare("
    SELECT o.*, GROUP_CONCAT(oi.product_name SEPARATOR ', ') AS items 
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$orderStmt->execute([$_SESSION['user_id']]);
$orders = $orderStmt->fetchAll();

$noOrdersMessage = null;
if (empty($orders)) {
    $noOrdersMessage = "<div class='panel' style='text-align: center;'>
        <h3>No orders found</h3>
        <p>You haven't placed any orders yet.</p>
    </div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Orders</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(-45deg, #2193b0, #6dd5ed);
            background-size: 400% 400%;
            animation: gradientMove 15s ease infinite;
            color: #333;
        }

        @keyframes gradientMove {
            0% {background-position: 0% 50%;}
            50% {background-position: 100% 50%;}
            100% {background-position: 0% 50%;}
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .top-links {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .top-links a {
            background-color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            color: #007BFF;
            font-weight: bold;
        }

        .order-panel {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .order-header {
            border-bottom: 2px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }

        .order-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .detail-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #eee;
        }

        .items-list {
            margin-top: 15px;
        }

        .item {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .total-amount {
            font-size: 1.2em;
            font-weight: bold;
            text-align: right;
            margin-top: 15px;
            color: #2c3e50;
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .status-processing {
            background: #cce5ff;
            color: #004085;
            border: 1px solid #b8daff;
        }

        .status-shipped {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-delivered {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="top-links">
            <a href="buyer_dashboard.php">← Back to Dashboard</a>
            <div>
                <a href="cart.php">🛒 Cart</a>
                <a href="favourites.php">❤️ Favourites</a>
            </div>
        </div>

        <h2 style="color: white; text-align: center;">Your Order History</h2>

        <?php if(isset($noOrdersMessage)) echo $noOrdersMessage; ?>

        <?php foreach ($orders as $order): ?>
        <div class="order-panel">
            <div class="order-header">
                <h3>Order #<?= htmlspecialchars($order['id']) ?> 
                    <span class="status-badge status-<?= htmlspecialchars($order['status']) ?>">
                        <?= ucfirst(htmlspecialchars($order['status'])) ?>
                    </span>
                    <span style="font-size: 0.8em; color: #666;">
                        (Placed on <?= date('M j, Y g:i a', strtotime($order['created_at'])) ?>)
                    </span>
                </h3>
                <div class="detail-box">
                    <strong>Delivery Method:</strong> <?= htmlspecialchars(ucfirst($order['delivery_method'])) ?><br>
                    <strong>Delivery Date:</strong> <?= htmlspecialchars($order['delivery_date']) ?><br>
                    <strong>Address:</strong> <?= htmlspecialchars($order['address']) ?>
                </div>
            </div>

            <div class="items-list">
                <?php 
                $itemsStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
                $itemsStmt->execute([$order['id']]);
                $items = $itemsStmt->fetchAll();
                ?>

                <?php foreach ($items as $item): ?>
                <div class="item">
                    <div>
                        <strong><?= htmlspecialchars($item['product_name']) ?></strong><br>
                        <span>Quantity: <?= $item['quantity'] ?></span>
                    </div>
                    <div style="text-align: right;">
                        R<?= number_format($item['price'], 2) ?><br>
                        <em style="font-size: 0.9em; color: #666;">
                            (R<?= number_format($item['price'] * $item['quantity'], 2) ?>)
                        </em>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="total-amount">
                Total Paid: R<?= number_format($order['total_amount'], 2) ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html>