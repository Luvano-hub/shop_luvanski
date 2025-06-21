<?php
session_start();
$conn = new mysqli("localhost", "root", "", "shop_luvanski");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$order_id = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
if (!$order_id) {
    echo "Invalid order ID.";
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch order
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order_result = $stmt->get_result();
if ($order_result->num_rows === 0) {
    echo "Order not found.";
    exit();
}
$order = $order_result->fetch_assoc();
$stmt->close();

// Fetch order items
$stmt_items = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$items_result = $stmt_items->get_result();
$order_items = [];
while ($row = $items_result->fetch_assoc()) {
    $order_items[] = $row;
}
$stmt_items->close();

$delivery_methods = ['courier' => 'Courier Delivery', 'collection' => 'Store Collection'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Order Complete</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 700px; margin: 2rem auto; padding: 1rem; }
        h1, h2, h3 { color: #2c3e50; }
        ul { list-style-type: disc; margin-left: 1.5rem; }
        p { line-height: 1.4; }
        a { color: #2980b9; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>Thank you for your order!</h1>
    <h2>Order Details</h2>
    <p><strong>Order ID:</strong> <?= htmlspecialchars($order['id']) ?></p>
    <p><strong>Order Date:</strong> <?= htmlspecialchars($order['created_at']) ?></p>
    <p><strong>Delivery Method:</strong> <?= htmlspecialchars($delivery_methods[$order['delivery_method']] ?? $order['delivery_method']) ?></p>
    <p><strong>Address:</strong><br /><?= nl2br(htmlspecialchars($order['address'])) ?></p>
    <p><strong>Delivery Speed:</strong> <?= htmlspecialchars(ucfirst($order['speed'])) ?></p>
    <p><strong>Delivery Date:</strong> <?= htmlspecialchars($order['delivery_date']) ?></p>
    <p><strong>Total Amount:</strong> R<?= number_format($order['total_amount'], 2) ?></p>

    <h3>Items</h3>
    <ul>
        <?php foreach ($order_items as $item): ?>
            <li><?= htmlspecialchars($item['product_name']) ?> x <?= $item['quantity'] ?> @ R<?= number_format($item['price'], 2) ?></li>
        <?php endforeach; ?>
    </ul>

    <p><a href="orders.php">View My Orders</a> | <a href="index.php">Return to Home</a></p>
</body>
</html>
