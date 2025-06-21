<?php

require 'config.php';

// Force seller authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

$seller_id = $_SESSION['user_id']; // Direct ID from users table

// Get the seller's store
try {
    $store_stmt = $pdo->prepare("SELECT id FROM stores WHERE seller_id = ?");
    $store_stmt->execute([$seller_id]);
    $store = $store_stmt->fetch();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

if (!$store) {
    die("You need to create a store first before adding products.");
}

$categories = [
    "Auto", "Baby & Toddler", "Beauty", "Books", "Camping", "Cell Phone", "Skin Care",
    "Clothing & Accessories", "Computers", "Gaming", "Garden", "Groceries", "Home & Kitchen",
    "Cleaning", "Large Appliances", "Liquor", "Luggage", "Luxury", "Music", "Office",
    "Pets", "Photography", "Small Appliances", "Sports", "Toys", "Wearable Tech", "TV & Audio"
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Process form data
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = (float)$_POST['price'];
        $quantity = (int)$_POST['quantity'];
        $category = $_POST['category'];

        // Handle file upload
        $image_path = '';
        if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $filename = uniqid() . '_' . basename($_FILES['image']['name']);
            $target_path = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                $image_path = $target_path;
            }
        }

        // Insert product with explicit seller ID
        $stmt = $pdo->prepare("
            INSERT INTO products (
                store_id, 
                seller_id, 
                name, 
                description, 
                price, 
                quantity, 
                image, 
                category
            ) VALUES (
                :store_id,
                :seller_id,
                :name,
                :description,
                :price,
                :quantity,
                :image,
                :category
            )
        ");

        $stmt->execute([
            ':store_id' => $store['id'],
            ':seller_id' => $seller_id, // Direct from users.id
            ':name' => $name,
            ':description' => $description,
            ':price' => $price,
            ':quantity' => $quantity,
            ':image' => $image_path,
            ':category' => $category
        ]);

        header("Location: manage_inventory.php");
        exit;

    } catch (PDOException $e) {
        die("Error saving product: " . $e->getMessage());
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Product</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #6e43ef, #e7cb5b);
            margin: 0;
            padding: 2rem;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: white;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2.5rem;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }
        input, textarea, select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #7b2cbf;
            box-shadow: 0 0 0 2px rgba(123, 44, 191, 0.2);
        }
        .btn {
            background: #7b2cbf;
            color: white;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s ease;
        }
        .btn:hover {
            background: #5a189a;
        }
        .back-link {
            display: inline-block;
            margin-top: 1.5rem;
            color: white;
            text-decoration: none;
            font-weight: 500;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Add New Product</h1>
    <div class="container">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Product Name</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4" required></textarea>
            </div>

            <div class="form-group">
                <label for="price">Price (R)</label>
                <input type="number" id="price" name="price" step="0.01" min="0" required>
            </div>

            <div class="form-group">
                <label for="quantity">Quantity</label>
                <input type="number" id="quantity" name="quantity" min="0" required>
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category" required>
                    <option value="">Select a Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category) ?>">
                            <?= htmlspecialchars($category) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="image">Product Image</label>
                <input type="file" id="image" name="image" accept="image/*">
            </div>

            <button type="submit" class="btn">Add Product</button>
        </form>
        <a href="manage_inventory.php" class="back-link">← Back to Inventory</a>
    </div>
</body>
</html>