<?php
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT DISTINCT
            o.id AS order_id,
            o.created_at AS order_date,
            o.delivery_method,
            o.delivery_date,
            o.address,
            o.status,
            oi.product_id,
            oi.quantity,
            oi.product_name,
            oi.price,
            u.username AS buyer_name
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.id
        JOIN users u ON o.user_id = u.id
        WHERE oi.seller_id = ?
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$seller_id]);
    $orders = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Error fetching orders: " . $e->getMessage());
}

// Group orders by order ID
$groupedOrders = [];
foreach ($orders as $order) {
    $orderId = $order['order_id'];
    if (!isset($groupedOrders[$orderId])) {
        $groupedOrders[$orderId] = [
            'details' => [
                'order_date' => $order['order_date'],
                'delivery_method' => $order['delivery_method'],
                'delivery_date' => $order['delivery_date'],
                'address' => $order['address'],
                'status' => $order['status'],
                'buyer_name' => $order['buyer_name']
            ],
            'items' => []
        ];
    }
    $groupedOrders[$orderId]['items'][] = $order;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Orders</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        h2 {
            color: white;
            font-size: 2.5rem;
            margin-bottom: 2rem;
            text-align: center;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
        }

        .back-btn button {
            background: #4a5568;
            color: white;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
            margin-bottom: 2rem;
        }

        .back-btn button:hover {
            background: #2d3748;
        }

        .no-orders {
            text-align: center;
            color: white;
            font-size: 1.2rem;
            padding: 2rem;
        }

        .order-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .order-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .order-header h3 {
            font-size: 1.5rem;
            color: #2d3748;
            margin-bottom: 1rem;
        }

        .order-header small {
            font-size: 1rem;
            color: #718096;
        }

        .order-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            padding: 1.5rem;
        }

        .detail-item {
            background: #f7fafc;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .detail-item strong {
            color: #4a5568;
            display: block;
            margin-bottom: 0.5rem;
        }

        .status-select {
            padding: 0.5rem;
            border: 1px solid #cbd5e0;
            border-radius: 6px;
            width: 100%;
            max-width: 200px;
            margin-top: 0.5rem;
            background: white;
            transition: all 0.3s ease;
        }

        .status-select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        .products-table th,
        .products-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .products-table th {
            background: #f7fafc;
            color: #4a5568;
            font-weight: 500;
        }

        .products-table tr:last-child td {
            border-bottom: none;
        }

        .products-table td:nth-child(3),
        .products-table td:nth-child(4) {
            text-align: right;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .order-details {
                grid-template-columns: 1fr;
            }

            .products-table th,
            .products-table td {
                padding: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Manage Orders</h2>
        <a href="seller_dashboard.php" class="back-btn">
            <button>← Back to Dashboard</button>
        </a>

        <?php if (empty($groupedOrders)): ?>
            <p class="no-orders">No orders found</p>
        <?php else: ?>
            <?php foreach ($groupedOrders as $orderId => $orderData): ?>
                <div class="order-card">
                    <div class="order-header">
                        <h3>Order #<?= $orderId ?> 
                            <small>(<?= date('M j, Y', strtotime($orderData['details']['order_date'])) ?>)</small>
                        </h3>
                        <div class="order-details">
                            <div class="detail-item">
                                <strong>Buyer:</strong> <?= htmlspecialchars($orderData['details']['buyer_name']) ?><br>
                                <strong>Status:</strong>
                                <select class="status-select" data-order="<?= $orderId ?>">
                                    <option value="pending" <?= $orderData['details']['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="processing" <?= $orderData['details']['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                                    <option value="shipped" <?= $orderData['details']['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                    <option value="delivered" <?= $orderData['details']['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                </select>
                            </div>
                            <div class="detail-item">
                                <strong>Delivery Method:</strong> <?= ucfirst($orderData['details']['delivery_method']) ?><br>
                                <strong>Delivery Date:</strong> <?= $orderData['details']['delivery_date'] ?>
                            </div>
                            <div class="detail-item">
                                <strong>Address:</strong> <?= nl2br(htmlspecialchars($orderData['details']['address'])) ?>
                            </div>
                        </div>
                    </div>

                    <table class="products-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderData['items'] as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td>R<?= number_format($item['price'], 2) ?></td>
                                    <td>R<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

<script>
// Attach change event to all dropdowns with class 'status-select'
document.querySelectorAll('.status-select').forEach(select => {
    select.addEventListener('change', async function() {
        const orderId = this.dataset.order;       // Get order ID from data attribute
        const newStatus = this.value;             // Get selected status
        const originalStatus = this.value;        // Backup in case of error

        try {
            // Send updated status to server using POST
            const response = await fetch('update_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    order_id: orderId,
                    status: newStatus
                })
            });

            const data = await response.json();

            // If server responds with error or failure
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Status update failed');
            }

            // Highlight dropdown green on success
            this.style.borderColor = '#48bb78';
            setTimeout(() => this.style.borderColor = '', 2000);

        } catch (error) {
            console.error('Update error:', error);
            this.value = originalStatus; // Revert to old status
            alert(error.message);        // Show error message
        }
    });
});
</script>

</body>
</html>