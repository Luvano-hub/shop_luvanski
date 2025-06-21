<?php
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("Product ID missing.");
}

$productId = $_GET['id'];

// Fetch product
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    die("Product not found.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $category = $_POST['category'];

    $imagePath = $product['image'];

    // If new image uploaded
    if (!empty($_FILES["image"]["name"])) {
        $targetDir = "uploads/";
        $fileName = basename($_FILES["image"]["name"]);
        $targetFilePath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
            $imagePath = $targetFilePath;
        }
    }

    $updateStmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, quantity = ?, category = ?, image = ? WHERE id = ?");
    $updateStmt->execute([$name, $description, $price, $quantity, $category, $imagePath, $productId]);

    header("Location: manage_inventory.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(to right, #c471f5, #fa71cd);
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
            max-width: 600px;
            margin: 0 auto;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            color: #333;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 8px 10px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }

        textarea {
            resize: vertical;
        }

        input[type="file"] {
            margin-top: 8px;
        }

        .image-preview {
            margin-top: 10px;
        }

        .image-preview img {
            max-width: 150px;
            border-radius: 8px;
        }

        .btn {
            background-color: #7b2cbf;
            color: white;
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            margin-top: 20px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn:hover {
            background-color: #5a189a;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #fff;
            font-weight: 500;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <h2>Edit Product</h2>

    <div class="panel">
        <form method="post" enctype="multipart/form-data">
            <label>Product Name:</label>
            <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>

            <label>Description:</label>
            <textarea name="description" rows="3" required><?= htmlspecialchars($product['description']) ?></textarea>

            <label>Price (R):</label>
            <input type="number" name="price" step="0.01" value="<?= $product['price'] ?>" required>

            <label>Quantity:</label>
            <input type="number" name="quantity" value="<?= $product['quantity'] ?>" required>

            <label>Category:</label>
            <input type="text" name="category" value="<?= htmlspecialchars($product['category']) ?>" required>

            <label>Product Image:</label>
            <input type="file" name="image">

            <?php if (!empty($product['image']) && file_exists($product['image'])): ?>
                <div class="image-preview">
                    <strong>Current Image:</strong><br>
                    <img src="<?= htmlspecialchars($product['image']) ?>" alt="Current Image">
                </div>
            <?php endif; ?>

            <button type="submit" class="btn">Update Product</button>
        </form>

        <a href="manage_inventory.php" class="back-link">← Back to Inventory</a>
    </div>

</body>
</html>
