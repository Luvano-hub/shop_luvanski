<?php
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch favourite products
$stmt = $pdo->prepare("
    SELECT p.* 
    FROM favourites f 
    JOIN products p ON f.product_id = p.id 
    WHERE f.user_id = ?
");
$stmt->execute([$userId]);
$favouriteProducts = $stmt->fetchAll();

// Function to display product cards consistently
function displayFavouriteProduct($product) {
    $imagePath = htmlspecialchars($product['image'] ?? 'images/placeholder.png');
    ?>
    <div class="product-card">
        <img src="<?= $imagePath ?>" alt="Product Image">
        <div class="product-name"><?= htmlspecialchars($product['name']) ?></div>
        <div class="product-price">R<?= htmlspecialchars($product['price']) ?></div>
        <div class="rating">⭐⭐⭐⭐☆</div>
        <div class="product-description"><?= htmlspecialchars($product['description']) ?></div>
        
        <div class="button-group">
            <form method="POST" action="add_to_cart.php" style="display: inline-block;">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <button type="submit" class="add-to-cart-btn">Add to Cart</button>
            </form>
            
            <form method="POST" action="remove_favourite.php" style="display: inline-block;">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <button type="submit" class="remove-btn" title="Remove from Favourites">❤️</button>
            </form>
        </div>
    </div>
    <?php
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Favourites</title>
    <style>
        /* Match buyer dashboard styles */
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

        h2 {
            color: white;
            text-align: center;
            margin-bottom: 30px;
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

        .panel {
            background-color: white;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .product-list {
            display: flex;
            overflow-x: auto;
            gap: 15px;
            padding: 10px 0;
        }

        .product-card {
            min-width: 200px;
            background-color: #fff;
            border-radius: 10px;
            padding: 12px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.2);
            flex-shrink: 0;
            text-align: center;
            position: relative;
            transition: transform 0.3s ease;
        }

        .product-card:hover {
            transform: scale(1.05);
        }

        .product-card img {
            max-width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .product-name {
            font-weight: bold;
            margin: 5px 0;
            font-size: 16px;
        }

        .product-price {
            color: #444;
            font-size: 15px;
        }

        .rating {
            color: gold;
            margin-top: 5px;
            font-size: 14px;
        }

        .add-to-cart-btn {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 8px 12px;
            margin-top: 8px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        .add-to-cart-btn:hover {
            background-color: #0056b3;
        }

        .remove-btn {
            background: transparent;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: red;
            margin-left: 8px;
            transition: transform 0.2s ease;
            vertical-align: middle;
        }

        .remove-btn:hover {
            transform: scale(1.2);
        }

        .product-description {
            margin-top: 8px;
            font-size: 13px;
            color: #555;
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            transition: opacity 0.4s ease, max-height 0.4s ease;
        }

        .product-card:hover .product-description {
            max-height: 100px;
            opacity: 1;
        }

        .empty-message {
            text-align: center;
            color: white;
            font-size: 1.2em;
            padding: 40px 0;
        }
    </style>
</head>
<body>

<div class="top-links">
    <a href="buyer_dashboard.php">← Back to Dashboard</a>
    <div>
        <a href="cart.php">🛒 View Cart</a>
    </div>
</div>

<h2>Your Favourite Products</h2>

<div class="panel">
    <?php if (count($favouriteProducts) === 0): ?>
        <div class="empty-message">You haven't added any products to your favourites yet.</div>
    <?php else: ?>
        <div class="product-list">
            <?php foreach ($favouriteProducts as $product): ?>
                <?php displayFavouriteProduct($product); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>