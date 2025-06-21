<?php
require 'config.php';

// Enhanced admin access control

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if user is admin using role-based system
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Function to get status badge
function getStatusBadge($status) {
    switch (strtolower($status)) {
        case 'completed':
        case 'delivered':
            return '<span class="badge badge-success">'.ucfirst($status).'</span>';
        case 'pending':
        case 'processing':
            return '<span class="badge badge-warning">'.ucfirst($status).'</span>';
        case 'cancelled':
            return '<span class="badge badge-danger">Cancelled</span>';
        default:
            return '<span class="badge badge-secondary">'.htmlspecialchars($status).'</span>';
    }
}

// Fetch website analytics with error handling
try {
    // Basic counts
    $users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $sellers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'seller'")->fetchColumn();
    $products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $stores = $pdo->query("SELECT COUNT(*) FROM stores")->fetchColumn();
    
    // Financial metrics
    $revenue = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status = 'delivered'")->fetchColumn();
    $revenue = $revenue ? $revenue : 0;
    
    // Fetch recent users
    $userList = $pdo->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 10")->fetchAll();
    
    // Fetch recent products with store info
    $productList = $pdo->query("SELECT p.id, p.name, p.price, s.store_name, p.created_at, p.quantity 
                               FROM products p 
                               JOIN stores s ON p.store_id = s.id
                               ORDER BY p.created_at DESC LIMIT 10")->fetchAll();
    
    // Fetch recent orders with buyer info
    $orderList = $pdo->query("SELECT o.id, u.username as buyer, o.total_amount, o.status, o.created_at 
                             FROM orders o
                             JOIN users u ON o.user_id = u.id
                             ORDER BY o.created_at DESC LIMIT 5")->fetchAll();
    
    // Fetch recent stores with seller info
    $storeList = $pdo->query("SELECT s.id, s.store_name, u.username as seller, s.location, s.description 
                             FROM stores s 
                             JOIN users u ON s.seller_id = u.id
                             ORDER BY s.id DESC LIMIT 5")->fetchAll();
    
    // Monthly revenue data for chart
    $revenueData = $pdo->query("SELECT 
                                DATE_FORMAT(created_at, '%Y-%m') AS month, 
                                SUM(total_amount) AS revenue 
                                FROM orders 
                                WHERE status = 'delivered'
                                GROUP BY month 
                                ORDER BY month DESC 
                                LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
    
    // Format for chart
    $chartLabels = [];
    $chartValues = [];
    foreach(array_reverse($revenueData) as $data) {
        $chartLabels[] = date('M Y', strtotime($data['month']));
        $chartValues[] = $data['revenue'];
    }
    
    // Top selling products
    $topProducts = $pdo->query("SELECT 
                                p.name, 
                                SUM(oi.quantity) as total_sold, 
                                SUM(oi.quantity * oi.price) as total_revenue 
                                FROM order_items oi
                                JOIN products p ON oi.product_id = p.id
                                GROUP BY p.id 
                                ORDER BY total_sold DESC 
                                LIMIT 5")->fetchAll();
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    // Initialize empty results to prevent errors
    $users = $sellers = $products = $orders = $stores = $revenue = 0;
    $userList = $productList = $orderList = $storeList = $topProducts = [];
    $chartLabels = $chartValues = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Shop Luvanski</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --dark: #212529;
            --light: #f8f9fa;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fb;
            color: #333;
            line-height: 1.6;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, var(--primary), var(--secondary));
            color: white;
            padding: 20px 0;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            position: fixed;
            height: 100%;
            z-index: 100;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar h2 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        
        .admin-name {
            font-weight: 500;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
        }
        
        .admin-name i {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        .nav-links {
            list-style: none;
            padding: 20px 0;
        }
        
        .nav-links li a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            font-size: 1rem;
        }
        
        .nav-links li a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .nav-links li a:hover,
        .nav-links li a.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left: 4px solid var(--success);
        }
        
        .main-content {
            flex: 1;
            padding: 30px;
            margin-left: 250px;
            overflow-y: auto;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 2rem;
            color: var(--dark);
        }
        
        .logout-btn {
            background: var(--danger);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            display: flex;
            align-items: center;
        }
        
        .logout-btn i {
            margin-right: 5px;
        }
        
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }
        
        .card h3 {
            color: #666;
            font-size: 1rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        
        .card h3 i {
            margin-right: 8px;
            color: var(--primary);
        }
        
        .card .value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .card .trend {
            display: flex;
            align-items: center;
            color: #28a745;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        
        .card .icon-bg {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 3rem;
            opacity: 0.1;
            color: var(--primary);
        }
        
        .section {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .section-header h2 {
            font-size: 1.5rem;
            color: var(--dark);
            display: flex;
            align-items: center;
        }
        
        .section-header h2 i {
            margin-right: 10px;
            color: var(--primary);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .badge-admin {
            background-color: #e0f7fa;
            color: #006064;
        }
        
        .badge-seller {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .badge-buyer {
            background-color: #fff3e0;
            color: #ef6c00;
        }
        
        .badge-success {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .badge-warning {
            background-color: #fff3e0;
            color: #ef6c00;
        }
        
        .badge-danger {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .badge-secondary {
            background-color: #eceff1;
            color: #37474f;
        }
        
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
        }
        
        .edit-btn {
            background-color: #e0f7fa;
            color: #007bff;
        }
        
        .delete-btn {
            background-color: #ffebee;
            color: #dc3545;
            margin-left: 5px;
        }
        
        .view-btn {
            background-color: #e8f5e9;
            color: #28a745;
            margin-left: 5px;
        }
        
        .action-btn i {
            margin-right: 5px;
            font-size: 0.8rem;
        }
        
        .action-btn:hover {
            opacity: 0.9;
            transform: scale(1.05);
        }
        
        .status-pill {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-pending {
            background-color: #fff3e0;
            color: #ef6c00;
        }
        
        .status-processing {
            background-color: #e0f7fa;
            color: #007bff;
        }
        
        .status-completed {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .status-cancelled {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .chart-container {
            height: 300px;
            margin-top: 20px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 15px;
        }
        
        .analytics-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .analytics-chart {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        
        .analytics-stats {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        
        .stats-header {
            font-size: 1.2rem;
            margin-bottom: 15px;
            color: var(--dark);
            display: flex;
            align-items: center;
        }
        
        .stats-header i {
            margin-right: 10px;
            color: var(--primary);
        }
        
        .stats-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
        }
        
        .stats-item:last-child {
            border-bottom: none;
        }
        
        .stats-label {
            color: #666;
        }
        
        .stats-value {
            font-weight: 600;
            color: var(--primary);
        }
        
        .status-select {
            padding: 4px 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
            font-size: 0.85rem;
            background-color: #f8f9fa;
        }
        
        @media (max-width: 1200px) {
            .analytics-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .dashboard-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Shop Luvanski</h2>
                <div class="admin-name">
                    <i class="fas fa-user-shield"></i>
                    <?= htmlspecialchars($_SESSION['username']) ?> (Admin)
                </div>
            </div>
            
            <ul class="nav-links">
                <li><a href="#" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="#"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="#"><i class="fas fa-store"></i> Stores</a></li>
                <li><a href="#"><i class="fas fa-box-open"></i> Products</a></li>
                <li><a href="#"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="#"><i class="fas fa-chart-line"></i> Analytics</a></li>
                <li><a href="#"><i class="fas fa-cog"></i> Settings</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
                <form action="logout.php" method="post">
                    <button type="submit" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
            
            <!-- Analytics Cards -->
            <div class="dashboard-cards">
                <div class="card">
                    <i class="fas fa-users icon-bg"></i>
                    <h3><i class="fas fa-users"></i> Total Users</h3>
                    <div class="value"><?= $users ?></div>
                    <div class="trend"><i class="fas fa-arrow-up"></i> 12% from last month</div>
                </div>
                
                <div class="card">
                    <i class="fas fa-store icon-bg"></i>
                    <h3><i class="fas fa-store"></i> Stores</h3>
                    <div class="value"><?= $stores ?></div>
                    <div class="trend"><i class="fas fa-arrow-up"></i> 5% from last month</div>
                </div>
                
                <div class="card">
                    <i class="fas fa-boxes icon-bg"></i>
                    <h3><i class="fas fa-boxes"></i> Products</h3>
                    <div class="value"><?= $products ?></div>
                    <div class="trend"><i class="fas fa-arrow-up"></i> 15% from last month</div>
                </div>
                
                <div class="card">
                    <i class="fas fa-shopping-bag icon-bg"></i>
                    <h3><i class="fas fa-shopping-bag"></i> Total Orders</h3>
                    <div class="value"><?= $orders ?></div>
                    <div class="trend"><i class="fas fa-arrow-up"></i> 22% from last month</div>
                </div>
                
                <div class="card">
                    <i class="fas fa-money-bill-wave icon-bg"></i>
                    <h3><i class="fas fa-money-bill-wave"></i> Revenue</h3>
                    <div class="value">R<?= number_format($revenue, 2) ?></div>
                    <div class="trend"><i class="fas fa-arrow-up"></i> 18% from last month</div>
                </div>
                
                <div class="card">
                    <i class="fas fa-user-tie icon-bg"></i>
                    <h3><i class="fas fa-user-tie"></i> Sellers</h3>
                    <div class="value"><?= $sellers ?></div>
                    <div class="trend"><i class="fas fa-arrow-up"></i> 8% from last month</div>
                </div>
            </div>
            
            <!-- Analytics Section -->
            <div class="analytics-grid">
                <div class="analytics-chart">
                    <div class="section-header">
                        <h2><i class="fas fa-chart-line"></i> Revenue Trends</h2>
                    </div>
                    <div class="chart-container">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
                
                <div class="analytics-stats">
                    <div class="section-header">
                        <h2><i class="fas fa-star"></i> Top Products</h2>
                    </div>
                    <?php if (!empty($topProducts)): ?>
                        <?php foreach ($topProducts as $product): ?>
                            <div class="stats-item">
                                <span class="stats-label"><?= htmlspecialchars($product['name']) ?></span>
                                <span class="stats-value"><?= $product['total_sold'] ?> sold (R<?= number_format($product['total_revenue'], 2) ?>)</span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-chart-pie"></i>
                            <h3>No sales data</h3>
                            <p>No sales data available at this time.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="section">
                <div class="section-header">
                    <h2><i class="fas fa-shopping-cart"></i> Recent Orders</h2>
                    <a href="#" class="action-btn view-btn">View All</a>
                </div>
                
                <?php if (!empty($orderList)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Buyer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderList as $order): ?>
                            <tr>
                                <td>#<?= $order['id'] ?></td>
                                <td><?= htmlspecialchars($order['buyer']) ?></td>
                                <td>R<?= number_format($order['total_amount'], 2) ?></td>
                                <td>
                                    <select class="status-select">
                                        <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>Processing</option>
                                        <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                        <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                    </select>
                                </td>
                                <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                <td>
                                    <button class="action-btn view-btn">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-shopping-cart"></i>
                        <h3>No recent orders</h3>
                        <p>There are no orders to display at this time.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Store Management -->
            <div class="section">
                <div class="section-header">
                    <h2><i class="fas fa-store"></i> Recent Stores</h2>
                    <button class="action-btn edit-btn">
                        <i class="fas fa-plus"></i> Add Store
                    </button>
                </div>
                
                <?php if (!empty($storeList)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Store Name</th>
                                <th>Seller</th>
                                <th>Location</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($storeList as $store): ?>
                            <tr>
                                <td><?= $store['id'] ?></td>
                                <td><?= htmlspecialchars($store['store_name']) ?></td>
                                <td><?= htmlspecialchars($store['seller']) ?></td>
                                <td><?= htmlspecialchars($store['location']) ?></td>
                                <td>
                                    <button class="action-btn edit-btn">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="action-btn delete-btn">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-store"></i>
                        <h3>No stores found</h3>
                        <p>There are no stores to display at this time.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Product Management -->
            <div class="section">
                <div class="section-header">
                    <h2><i class="fas fa-box-open"></i> Recent Products</h2>
                    <button class="action-btn edit-btn">
                        <i class="fas fa-plus"></i> Add Product
                    </button>
                </div>
                
                <?php if (!empty($productList)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Store</th>
                                <th>Stock</th>
                                <th>Date Added</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productList as $product): ?>
                            <tr>
                                <td><?= $product['id'] ?></td>
                                <td><?= htmlspecialchars($product['name']) ?></td>
                                <td>R<?= number_format($product['price'], 2) ?></td>
                                <td><?= htmlspecialchars($product['store_name']) ?></td>
                                <td><?= $product['quantity'] ?></td>
                                <td><?= date('M d, Y', strtotime($product['created_at'])) ?></td>
                                <td>
                                    <button class="action-btn edit-btn">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="action-btn delete-btn">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <h3>No products found</h3>
                        <p>There are no products to display at this time.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- User Management -->
            <div class="section">
                <div class="section-header">
                    <h2><i class="fas fa-users"></i> Recent Users</h2>
                    <button class="action-btn edit-btn">
                        <i class="fas fa-plus"></i> Add User
                    </button>
                </div>
                
                <?php if (!empty($userList)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($userList as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <span class="badge 
                                        <?= $user['role'] === 'admin' ? 'badge-admin' : '' ?>
                                        <?= $user['role'] === 'seller' ? 'badge-seller' : '' ?>
                                        <?= $user['role'] === 'buyer' ? 'badge-buyer' : '' ?>">
                                        <?= ucfirst($user['role']) ?>
                                    </span>
                                </td>
                                <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <button class="action-btn edit-btn">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="action-btn delete-btn">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <h3>No users found</h3>
                        <p>There are no users to display at this time.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Initialize Revenue Chart
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [{
                    label: 'Monthly Revenue (R)',
                    data: <?= json_encode($chartValues) ?>,
                    backgroundColor: 'rgba(67, 97, 238, 0.1)',
                    borderColor: '#4361ee',
                    borderWidth: 3,
                    tension: 0.3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#4361ee',
                    pointBorderWidth: 2,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 14
                        },
                        callbacks: {
                            label: function(context) {
                                return 'R' + context.parsed.y.toFixed(2);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return 'R' + value;
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Status update functionality
        document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', function() {
                const orderId = this.closest('tr').querySelector('td:first-child').textContent.substring(1);
                const newStatus = this.value;
                
                // In a real application, you would send an AJAX request here
                console.log(`Updating order #${orderId} to status: ${newStatus}`);
                
                // Show a success message
                alert(`Order #${orderId} status updated to ${newStatus}`);
            });
        });
    </script>
</body>
</html>