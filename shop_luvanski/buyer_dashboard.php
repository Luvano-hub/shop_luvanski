<?php
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header("Location: login.php");
    exit();
}

// Handle order confirmation message
$confirmationMessage = '';
if (isset($_SESSION['order_success']) && $_SESSION['order_success']) {
    $orderId = htmlspecialchars($_SESSION['order_id'] ?? '');
    $confirmationMessage = "<div class='order-success-message'>
        <div class='success-icon'>🎉</div>
        <div>
            <h4>Order Confirmed!</h4>
            <p>Order #$orderId has been placed successfully</p>
        </div>
        <a href='view_orders.php' class='view-orders-btn'>View Orders</a>
    </div>";
    unset($_SESSION['order_success'], $_SESSION['order_id']);
}

// Handle category filtering
$selectedCategory = $_GET['category'] ?? '';
$filteredProducts = [];

if ($selectedCategory) {
    $filteredStmt = $pdo->prepare("SELECT * FROM products WHERE category = ?");
    $filteredStmt->execute([$selectedCategory]);
    $filteredProducts = $filteredStmt->fetchAll();
}

// Fetch all products for vertical grid with 12-item limit
$allProductsStmt = $pdo->prepare("SELECT * FROM products ORDER BY RAND() LIMIT 12");
$allProductsStmt->execute();
$allProducts = $allProductsStmt->fetchAll();

// Fetch recently added products
$recentStmt = $pdo->prepare("SELECT * FROM products ORDER BY created_at DESC LIMIT 10");
$recentStmt->execute();
$recentProducts = $recentStmt->fetchAll();

// Get categories for dropdown
$categoryStmt = $pdo->query("SELECT DISTINCT category FROM products");
$categories = $categoryStmt->fetchAll(PDO::FETCH_COLUMN);

// Favorites check function
function isProductFavorited($pdo, $userId, $productId) {
    $checkStmt = $pdo->prepare("SELECT id FROM favourites WHERE user_id = ? AND product_id = ?");
    $checkStmt->execute([$userId, $productId]);
    return $checkStmt->rowCount() > 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Dashboard | ShopLuvanski</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4a6cf7;
            --primary-dark: #3a57d4;
            --secondary: #ff6b6b;
            --accent: #0ea5e9;
            --dark: #1e293b;
            --light: #f8fafc;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-600: #475569;
            --gray-700: #334155;
            --border: #e2e8f0;
            --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        body {
            background-color: var(--gray-100);
            color: var(--gray-700);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.6;
        }

        /* Header Styles */
        header {
            background: linear-gradient(120deg, var(--primary), var(--accent));
            color: white;
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 0.8rem 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.7rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
        }

        .logo-icon {
            background: rgba(255, 255, 255, 0.2);
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }

        .user-nav {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .welcome-message {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            font-size: 1.05rem;
            background: rgba(255, 255, 255, 0.15);
            padding: 8px 16px;
            border-radius: 50px;
        }

        .top-links {
            display: flex;
            gap: 10px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.15);
            padding: 8px 18px;
            border-radius: 50px;
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            font-size: 0.95rem;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
        }

        .nav-link.logout-btn {
            background: rgba(239, 68, 68, 0.2);
        }

        .nav-link.logout-btn:hover {
            background: rgba(239, 68, 68, 0.3);
        }

        /* Main Content */
        main {
            flex: 1;
            padding: 2.5rem 2rem;
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
        }

        .container {
            display: flex;
            flex-direction: column;
            gap: 2.5rem;
        }

        /* Category Filter */
        .category-filter {
            max-width: 500px;
            margin: 0 auto 1.5rem;
            width: 100%;
        }

        .category-select {
            padding: 14px 24px;
            border-radius: 12px;
            border: 2px solid var(--gray-200);
            font-size: 1.05rem;
            width: 100%;
            background: white;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%234b5563' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 20px center;
            background-size: 20px;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            font-weight: 500;
        }

        .category-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(74, 108, 247, 0.15);
        }

        /* Order Success Message */
        .order-success-message {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin: 15px auto 30px;
            max-width: 800px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: var(--card-shadow);
            border-left: 4px solid var(--success);
        }

        .success-icon {
            font-size: 2.5rem;
            color: var(--success);
        }

        .order-success-message h4 {
            font-size: 1.4rem;
            margin-bottom: 5px;
            color: var(--dark);
        }

        .order-success-message p {
            color: var(--gray-600);
            margin-bottom: 10px;
        }

        .view-orders-btn {
            background: var(--success);
            color: white;
            padding: 8px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            margin-left: auto;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .view-orders-btn:hover {
            background: #0da271;
            transform: translateY(-2px);
        }

        /* Product Panels */
        .panel {
            background-color: white;
            padding: 2.2rem;
            border-radius: 18px;
            box-shadow: var(--card-shadow);
        }

        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.8rem;
        }

        .panel h3 {
            margin: 0;
            font-size: 1.7rem;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .panel h3 i {
            color: var(--primary);
            background: rgba(74, 108, 247, 0.1);
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .view-all {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: var(--transition);
        }

        .view-all:hover {
            color: var(--primary-dark);
            gap: 8px;
        }

        /* Product Grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 28px;
            align-items: stretch;
        }

        /* Product Cards */
        .product-card {
            background-color: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            height: 100%;
            position: relative;
            border: 1px solid var(--gray-200);
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
        }

        .product-image-container {
            position: relative;
            overflow: hidden;
        }

        .product-image {
            width: 100%;
            height: 240px;
            object-fit: cover;
            transition: var(--transition);
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }

        .product-badges {
            position: absolute;
            top: 15px;
            left: 15px;
            display: flex;
            gap: 8px;
            z-index: 2;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            color: white;
        }

        .badge-new {
            background: var(--success);
        }

        .badge-sale {
            background: var(--secondary);
        }

        .product-content {
            padding: 1.6rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .product-category {
            color: var(--primary);
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .product-name {
            font-weight: 700;
            margin: 0 0 10px 0;
            font-size: 1.25rem;
            color: var(--dark);
        }

        .product-description {
            color: var(--gray-600);
            font-size: 0.95rem;
            margin-bottom: 15px;
            flex: 1;
        }

        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            padding-top: 15px;
            border-top: 1px solid var(--border);
        }

        .product-price {
            color: var(--dark);
            font-size: 1.35rem;
            font-weight: 700;
        }

        .product-actions {
            display: flex;
            gap: 10px;
        }

        .add-to-cart-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: var(--transition);
        }

        .add-to-cart-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .fav-btn {
            background: var(--gray-100);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .fav-btn:hover {
            background: #fee2e2;
            color: var(--danger);
        }

        .fav-btn.favorited {
            background: #fee2e2;
            color: var(--danger);
        }

        .recently-added {
            margin-top: 1rem;
        }

        .product-list-container {
            position: relative;
        }

        .scroll-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: white;
            border: none;
            width: 46px;
            height: 46px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 10;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            transition: var(--transition);
            color: var(--dark);
            font-size: 1.2rem;
        }

        .scroll-arrow:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-50%) scale(1.1);
        }

        .scroll-arrow.left {
            left: -23px;
        }

        .scroll-arrow.right {
            right: -23px;
        }

        .product-list {
            display: flex;
            overflow-x: auto;
            gap: 25px;
            padding: 15px 5px;
            scroll-behavior: smooth;
            scrollbar-width: none;
            position: relative;
        }

        .product-list::-webkit-scrollbar {
            display: none;
        }

        .product-list .product-card {
            min-width: 280px;
            flex: 0 0 auto;
            scroll-snap-align: start;
        }

        .product-list .product-image {
            height: 220px;
        }

        /* Footer Styles */
        footer {
            background: var(--dark);
            color: white;
            padding: 3.5rem 2rem 2rem;
            margin-top: auto;
        }

        .footer-container {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2.5rem;
        }

        .footer-section h4 {
            margin-bottom: 1.5rem;
            font-size: 1.3rem;
            position: relative;
            padding-bottom: 0.8rem;
        }

        .footer-section h4::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--primary);
            border-radius: 3px;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.9rem;
        }

        .footer-links a {
            color: #cbd5e1;
            text-decoration: none;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .footer-links a:hover {
            color: white;
            gap: 12px;
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transition: var(--transition);
        }

        .social-links a:hover {
            background: var(--primary);
            transform: translateY(-3px);
        }

        .copyright {
            text-align: center;
            padding-top: 2.5rem;
            margin-top: 3rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #94a3b8;
            font-size: 0.95rem;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .header-container {
                flex-wrap: wrap;
                gap: 15px;
            }
            
            .user-nav {
                flex-wrap: wrap;
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            header {
                padding: 0.8rem 1.5rem;
            }
            
            main {
                padding: 1.5rem 1.2rem;
            }
            
            .panel {
                padding: 1.8rem;
            }
            
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
                gap: 20px;
            }
            
            .product-list .product-card {
                min-width: 260px;
            }
            
            .scroll-arrow {
                display: none;
            }
        }

        @media (max-width: 576px) {
            .order-success-message {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .view-orders-btn {
                margin-left: 0;
            }
            
            .panel-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .view-all {
                align-self: flex-start;
            }
            
            .product-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <a href="buyer_dashboard.php" class="logo">
                <div class="logo-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <span>ShopLuvanski</span>
            </a>
            
            <div class="user-nav">
                <div class="welcome-message">
                    <i class="fas fa-user"></i>
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </div>
                
                <div class="top-links">
                    <a href="cart.php" class="nav-link">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Cart</span>
                    </a>
                    <a href="favourites.php" class="nav-link">
                        <i class="fas fa-heart"></i>
                        <span>Favourites</span>
                    </a>
                    <a href="view_orders.php" class="nav-link">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Orders</span>
                    </a>
                    <a href="logout.php" class="nav-link logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="category-filter">
                <form method="GET" action="">
                    <select class="category-select" name="category" onchange="this.form.submit()">
                        <option value="">Browse All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>"
                                <?= $selectedCategory === $cat ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <?= $confirmationMessage ?>

            <?php if (!$selectedCategory): ?>
                <!-- Recently Added Products Panel -->
                <div class="panel recently-added">
                    <div class="panel-header">
                        <h3><i class="fas fa-clock"></i> Recently Added Products</h3>
                    </div>
                    <div class="product-list-container">
                        <button class="scroll-arrow left" onclick="scrollProducts('left')">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <div class="product-list" id="productList">
                            <?php foreach ($recentProducts as $product): ?>
                                <div class="product-card">
                                    <?php displayProductCard($pdo, $product, $_SESSION['user_id']); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="scroll-arrow right" onclick="scrollProducts('right')">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- All Products Panel -->
            <div class="panel">
                <div class="panel-header">
                    <h3>
                        <i class="fas fa-box-open"></i>
                        <?php if ($selectedCategory): ?>
                            <?= htmlspecialchars($selectedCategory) ?> Products
                        <?php else: ?>
                            All Products
                        <?php endif; ?>
                    </h3>
                </div>
                <div class="product-grid">
                    <?php 
                    $productsToShow = $selectedCategory ? $filteredProducts : $allProducts;
                    foreach ($productsToShow as $product): 
                    ?>
                        <div class="product-card">
                            <?php displayProductCard($pdo, $product, $_SESSION['user_id']); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h4>ShopLuvanski</h4>
                <p>Your one-stop destination for all your shopping needs. Quality products at affordable prices with fast delivery.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-pinterest"></i></a>
                </div>
            </div>
            
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="buyer_dashboard.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Shopping Cart</a></li>
                    <li><a href="favourites.php"><i class="fas fa-heart"></i> Favorites</a></li>
                    <li><a href="view_orders.php"><i class="fas fa-clipboard-list"></i> My Orders</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Customer Service</h4>
                <ul class="footer-links">
                    <li><a href="#"><i class="fas fa-phone"></i> Contact Us</a></li>
                    <li><a href="#"><i class="fas fa-question-circle"></i> FAQ</a></li>
                    <li><a href="#"><i class="fas fa-sync-alt"></i> Return Policy</a></li>
                    <li><a href="#"><i class="fas fa-lock"></i> Privacy Policy</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Newsletter</h4>
                <p>Subscribe to get special offers, free giveaways, and new product updates.</p>
                <form class="newsletter-form">
                    <input type="email" placeholder="Enter your email" class="newsletter-input">
                    <button type="submit" class="newsletter-btn">Subscribe</button>
                </form>
            </div>
        </div>
        
        <div class="copyright">
            &copy; <?= date('Y') ?> ShopLuvanski. All rights reserved.
        </div>
    </footer>

    <script>
        function scrollProducts(direction) {
            const container = document.getElementById('productList');
            const scrollAmount = 320;
            
            if (direction === 'left') {
                container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
            } else {
                container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            }
        }
        
        document.addEventListener('DOMContentLoaded', () => {
            const successMsg = document.querySelector('.order-success-message');
            if (successMsg) {
                setTimeout(() => {
                    successMsg.style.opacity = '1';
                    successMsg.style.transform = 'translateY(0)';
                }, 300);
            }
        });
    </script>
</body>
</html>

<?php
function displayProductCard($pdo, $product, $userId) {
    $imagePath = htmlspecialchars($product['image'] ?? 'images/placeholder.png');
    $isFavorited = isProductFavorited($pdo, $userId, $product['id']);
    $createdAt = new DateTime($product['created_at']);
    $now = new DateTime();
    $interval = $now->diff($createdAt);
    $isNew = $interval->days < 7;
    
    echo '<div class="product-image-container">';
    echo '<img class="product-image" src="' . $imagePath . '" alt="' . htmlspecialchars($product['name']) . '">';
    
    echo '<div class="product-badges">';
    if ($isNew) {
        echo '<div class="badge badge-new">New</div>';
    }
    if ($product['price'] < 50) {
        echo '<div class="badge badge-sale">Sale</div>';
    }
    echo '</div>';
    
    echo '</div>';
    
    echo '<div class="product-content">';
    echo '<div class="product-category">' . htmlspecialchars($product['category']) . '</div>';
    echo '<h4 class="product-name">' . htmlspecialchars($product['name']) . '</h4>';
    echo '<p class="product-description">' . htmlspecialchars($product['description']) . '</p>';
    echo '<div class="product-footer">';
    echo '<div class="product-price">R' . number_format($product['price'], 2) . '</div>';
    
    echo '<div class="product-actions">';
    
    $formAction = $isFavorited ? 'remove_favourite.php' : 'add_to_favourites.php';
    $buttonIcon = $isFavorited ? 'fas fa-heart' : 'far fa-heart';
    $buttonClass = $isFavorited ? 'favorited' : '';
    $buttonTitle = $isFavorited ? 'Remove from Favorites' : 'Add to Favorites';
    
    echo '<form method="POST" action="' . $formAction . '">';
    echo '<input type="hidden" name="product_id" value="' . htmlspecialchars($product['id']) . '">';
    echo '<button type="submit" class="fav-btn ' . $buttonClass . '" title="' . $buttonTitle . '">';
    echo '<i class="' . $buttonIcon . '"></i>';
    echo '</button>';
    echo '</form>';

    echo '<form method="POST" action="add_to_cart.php">';
    echo '<input type="hidden" name="product_id" value="' . htmlspecialchars($product['id']) . '">';
    echo '<button type="submit" class="add-to-cart-btn">';
    echo '<i class="fas fa-cart-plus"></i>';
    echo '</button>';
    echo '</form>';
    
    echo '</div>';
    echo '</div>';
    echo '</div>';
}