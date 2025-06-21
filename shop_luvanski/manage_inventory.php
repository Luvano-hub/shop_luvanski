<?php
require 'config.php';

// Restrict access to sellers only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

$sellerId = $_SESSION['user_id'];

// Fetch the store ID belonging to this seller
$stmt = $pdo->prepare("SELECT id FROM stores WHERE seller_id = ?");
$stmt->execute([$sellerId]);
$store = $stmt->fetch();

if (!$store) {
    die("Store not found.");
}

// Get all products from the store
$productsStmt = $pdo->prepare("SELECT * FROM products WHERE store_id = ?");
$productsStmt->execute([$store['id']]);
$products = $productsStmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Inventory</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <style>
        /* (Same styling kept as before) */
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(to right,rgb(116, 112, 247),rgb(244, 98, 96));
            margin: 0;
            padding: 40px;
            font-size: 14px;
        }
        h2 {
            text-align: center;
            color: #fff;
            margin-bottom: 30px;
            font-size: 24px;
        }
        .panel {
            background-color: white;
            max-width: 1100px;
            margin: 0 auto;
            padding: 25px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }
        .top-buttons {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .button {
            background-color: #7b2cbf;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            transition: background-color 0.3s ease;
        }
        .button:hover {
            background-color: #5a189a;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px 14px;
            text-align: left;
        }
        th {
            background-color: #eee;
            font-weight: 600;
        }
        img {
            max-width: 80px;
            max-height: 80px;
            border-radius: 4px;
        }
        .actions {
            display: flex;
            gap: 6px;
        }
        .btn-edit, .btn-delete {
            padding: 6px 10px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            font-size: 12px;
        }
        .btn-edit {
            background-color: #4CAF50;
            color: white;
        }
        .btn-delete {
            background-color: #e63946;
            color: white;
        }
        p.no-products {
            text-align: center;
            font-style: italic;
            color: #555;
        }
    </style>
</head>
<body>
    <h2>Manage Inventory</h2>

    <div class="panel">
        <!-- Top navigation buttons -->
        <div class="top-buttons">
            <a href="seller_dashboard.php" class="button">← Back to Dashboard</a>
            <a href="add_product.php" class="button">+ Add New Product</a>
        </div>

        <!-- If products exist, display them in a table -->
        <?php if (count($products) > 0): ?>
            <table>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price (R)</th>
                    <th>Quantity</th>
                    <th>Category</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <?php
                            // Display product image if it exists
                            $imgPath = htmlspecialchars($product['image']);
                            if (!empty($imgPath) && file_exists($imgPath)) {
                                echo '<img src="' . $imgPath . '" alt="Product Image">';
                            } else {
                                echo '<span>No image</span>';
                            }
                            ?>
                        </td>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td><?= htmlspecialchars($product['description']) ?></td>
                        <td><?= number_format($product['price'], 2) ?></td>
                        <td><?= (int)$product['quantity'] ?></td>
                        <td><?= htmlspecialchars($product['category']) ?></td>
                        <td class="actions">
                            <!-- Edit and delete buttons -->
                            <a href="edit_product.php?id=<?= $product['id'] ?>"><button class="btn-edit">Edit</button></a>
                            <a href="delete_product.php?id=<?= $product['id'] ?>" onclick="return confirm('Are you sure you want to delete this product?');">
                                <button class="btn-delete">Delete</button>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <!-- No products message -->
            <p class="no-products">No products found in your inventory.</p>
        <?php endif; ?>
    </div>
</body>
</html>
