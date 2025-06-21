<?php
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

$sellerId = $_SESSION['user_id'];
$storeStmt = $pdo->prepare("SELECT * FROM stores WHERE seller_id = ?");
$storeStmt->execute([$sellerId]);
$store = $storeStmt->fetch();

$storeName = $store ? $store['store_name'] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Seller Dashboard | Shop Luvanski</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* Modern Color Palette & Typography */
    :root {
      --primary: #4361ee;
      --secondary: #3f37c9;
      --accent: #4895ef;
      --success: #4cc9f0;
      --text: #2b2d42;
      --light: #f8f9fa;
      --gray: #8d99ae;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', 'Segoe UI', sans-serif;
    }
    
    body {
      min-height: 100vh;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
      position: relative;
      overflow-x: hidden;
    }
    
    /* Animated Background Elements */
    .bg-bubbles {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 1;
      overflow: hidden;
    }
    
    .bg-bubbles li {
      position: absolute;
      list-style: none;
      display: block;
      width: 40px;
      height: 40px;
      background-color: rgba(255, 255, 255, 0.15);
      bottom: -160px;
      animation: square 20s infinite;
      transition-timing-function: linear;
      border-radius: 5px;
    }
    
    .bg-bubbles li:nth-child(1) { left: 10%; }
    .bg-bubbles li:nth-child(2) { 
      left: 20%; 
      width: 80px;
      height: 80px;
      animation-delay: 2s;
      animation-duration: 17s;
    }
    .bg-bubbles li:nth-child(3) { 
      left: 25%; 
      animation-delay: 4s; 
    }
    .bg-bubbles li:nth-child(4) { 
      left: 40%; 
      width: 60px;
      height: 60px;
      animation-duration: 22s;
      background-color: rgba(255, 255, 255, 0.25);
    }
    .bg-bubbles li:nth-child(5) { left: 70%; }
    .bg-bubbles li:nth-child(6) { 
      left: 80%; 
      width: 120px;
      height: 120px;
      animation-delay: 3s;
      background-color: rgba(255, 255, 255, 0.2);
    }
    .bg-bubbles li:nth-child(7) { 
      left: 32%; 
      width: 160px;
      height: 160px;
      animation-delay: 7s;
    }
    .bg-bubbles li:nth-child(8) { 
      left: 55%; 
      width: 20px;
      height: 20px;
      animation-delay: 15s;
      animation-duration: 40s;
    }
    
    @keyframes square {
      0% {
        transform: translateY(0) rotate(0deg);
        opacity: 1;
        border-radius: 5px;
      }
      100% {
        transform: translateY(-1000px) rotate(720deg);
        opacity: 0;
        border-radius: 50%;
      }
    }
    
    /* Glassmorphism Container */
    .container {
      background: rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.18);
      border-radius: 20px;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
      padding: 40px 35px;
      max-width: 500px;
      width: 100%;
      text-align: center;
      z-index: 10;
      position: relative;
      animation: fadeInUp 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    }
    
    /* Header Section */
    .dashboard-header {
      margin-bottom: 30px;
      padding-bottom: 20px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    .dashboard-header h2 {
      font-size: 28px;
      font-weight: 700;
      color: white;
      margin-bottom: 8px;
      letter-spacing: 0.5px;
      text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .store-info {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      margin-top: 15px;
    }
    
    .store-icon {
      width: 24px;
      height: 24px;
      background: var(--primary);
      border-radius: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 14px;
    }
    
    .store-name {
      font-size: 18px;
      font-weight: 500;
      color: white;
      background: rgba(0, 0, 0, 0.15);
      padding: 6px 15px;
      border-radius: 30px;
      display: inline-block;
    }
    
    .no-store {
      font-size: 16px;
      color: rgba(255, 255, 255, 0.85);
      font-style: italic;
      background: rgba(0, 0, 0, 0.1);
      padding: 8px 15px;
      border-radius: 30px;
      display: inline-block;
    }
    
    /* Dashboard Links */
    .dashboard-links {
      display: flex;
      flex-direction: column;
      gap: 18px;
      margin-top: 30px;
    }
    
    .dashboard-link {
      background: rgba(255, 255, 255, 0.85);
      color: var(--text);
      padding: 18px 25px;
      border-radius: 15px;
      text-decoration: none;
      font-weight: 600;
      font-size: 17px;
      display: flex;
      align-items: center;
      gap: 15px;
      text-align: left;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.4);
    }
    
    .dashboard-link:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
      background: white;
      border-color: rgba(255, 255, 255, 0.7);
    }
    
    .link-icon {
      width: 45px;
      height: 45px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      color: white;
      flex-shrink: 0;
    }
    
    .link-1 .link-icon { background: linear-gradient(135deg, #4361ee, #3a0ca3); }
    .link-2 .link-icon { background: linear-gradient(135deg, #f72585, #b5179e); }
    .link-3 .link-icon { background: linear-gradient(135deg, #4cc9f0, #4895ef); }
    
    .link-text {
      flex-grow: 1;
    }
    
    .link-arrow {
      color: var(--gray);
      font-size: 18px;
      transition: transform 0.3s ease;
    }
    
    .dashboard-link:hover .link-arrow {
      transform: translateX(5px);
      color: var(--primary);
    }
    
    /* Logout Button */
    .logout-btn {
      position: fixed;
      top: 25px;
      right: 25px;
      background: rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      padding: 12px 20px;
      border-radius: 50px;
      color: white;
      text-decoration: none;
      font-weight: 600;
      font-size: 15px;
      display: flex;
      align-items: center;
      gap: 8px;
      z-index: 1000;
      border: 1px solid rgba(255, 255, 255, 0.2);
      transition: all 0.3s ease;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    }
    
    .logout-btn:hover {
      background: rgba(255, 255, 255, 0.3);
      transform: translateY(-3px);
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
    }
    
    /* Footer */
    .dashboard-footer {
      margin-top: 30px;
      color: rgba(255, 255, 255, 0.7);
      font-size: 14px;
      padding-top: 20px;
      border-top: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    /* Animations */
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    /* Responsive Design */
    @media (max-width: 576px) {
      .container {
        padding: 30px 20px;
      }
      
      .dashboard-link {
        padding: 15px 20px;
        font-size: 16px;
      }
      
      .link-icon {
        width: 40px;
        height: 40px;
        font-size: 18px;
      }
      
      .logout-btn {
        top: 15px;
        right: 15px;
        padding: 10px 16px;
        font-size: 14px;
      }
    }
  </style>
</head>
<body>
  <!-- Animated Background Elements -->
  <ul class="bg-bubbles">
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
  </ul>
  
  <!-- Logout Button -->
  <a href="login.php" class="logout-btn">
    <i class="fas fa-sign-out-alt"></i> Logout
  </a>

  <!-- Seller Dashboard Container -->
  <div class="container">
    <div class="dashboard-header">
      <h2>Seller Dashboard</h2>
      <p style="color: rgba(255, 255, 255, 0.9); font-size: 16px; max-width: 400px; margin: 0 auto;">
        Manage your store, inventory, and orders in one place
      </p>
      
      <div class="store-info">
        <?php if ($storeName): ?>
          <div class="store-icon">
            <i class="fas fa-store"></i>
          </div>
          <div class="store-name"><?= htmlspecialchars($storeName) ?></div>
        <?php else: ?>
          <div class="no-store">You haven't created a store yet</div>
        <?php endif; ?>
      </div>
    </div>
    
    <div class="dashboard-links">
      <a href="create_store.php" class="dashboard-link link-1">
        <div class="link-icon">
          <i class="fas fa-store"></i>
        </div>
        <div class="link-text">Create / View Store</div>
        <div class="link-arrow">
          <i class="fas fa-chevron-right"></i>
        </div>
      </a>
      
      <a href="manage_inventory.php" class="dashboard-link link-2">
        <div class="link-icon">
          <i class="fas fa-boxes"></i>
        </div>
        <div class="link-text">Manage Inventory</div>
        <div class="link-arrow">
          <i class="fas fa-chevron-right"></i>
        </div>
      </a>
      
      <a href="manage_orders.php" class="dashboard-link link-3">
        <div class="link-icon">
          <i class="fas fa-clipboard-list"></i>
        </div>
        <div class="link-text">Manage Orders</div>
        <div class="link-arrow">
          <i class="fas fa-chevron-right"></i>
        </div>
      </a>
    </div>
    
    <div class="dashboard-footer">
      <p>Shop Luvanski &copy; <?= date('Y') ?> | Seller Portal</p>
    </div>
  </div>
  
  <script>
    // Add subtle interactive animations
    document.querySelectorAll('.dashboard-link').forEach(link => {
      link.addEventListener('mouseenter', () => {
        link.style.transform = 'translateY(-5px)';
      });
      
      link.addEventListener('mouseleave', () => {
        link.style.transform = 'translateY(0)';
      });
    });
  </script>
</body>
</html>