<?php
// Load database connection and session start from config
require 'config.php';

// Check if form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if this is an admin login attempt
    if (isset($_POST['admin_login'])) {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // Hardcoded admin credentials
        if ($username === 'admin' && $password === 'admin') {
            $_SESSION['user_id'] = 0;
            $_SESSION['username'] = 'admin';
            $_SESSION['role'] = 'admin';
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error = "Invalid admin credentials.";
        }
    } else {
        // Regular user login
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        // Proceed only if both fields are filled
        if ($username && $password) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                if ($_SESSION['role'] === 'seller') {
                    header("Location: seller_dashboard.php");
                    exit();
                } else {
                    header("Location: buyer_dashboard.php");
                    exit();
                }
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Please enter both fields.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login | Shop Luvanski</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    .admin-access {
      position: fixed;
      bottom: 20px;
      right: 20px;
      background: rgba(0,0,0,0.1);
      padding: 8px 12px;
      border-radius: 20px;
      font-size: 12px;
      color: #666;
      cursor: pointer;
      transition: all 0.3s;
    }
    .admin-access:hover {
      background: rgba(0,0,0,0.2);
    }
    .admin-login-form {
      display: none;
      position: fixed;
      bottom: 60px;
      right: 20px;
      background: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      width: 250px;
      z-index: 100;
    }
    .admin-login-form h3 {
      margin-top: 0;
      color: #333;
      font-size: 16px;
    }
    .admin-login-form label {
      display: block;
      margin-bottom: 8px;
      font-size: 14px;
    }
    .admin-login-form input {
      width: 100%;
      padding: 8px;
      margin-bottom: 12px;
      border: 1px solid #ddd;
      border-radius: 4px;
    }
    .admin-login-form button {
      background: #4361ee;
      color: white;
      border: none;
      padding: 8px 12px;
      border-radius: 4px;
      cursor: pointer;
    }
  </style>
</head>
<body>
  <form method="POST" action="login.php">
    <h2>Login to Shop Luvanski</h2>
    
    <?php if (!empty($error)): ?>
      <p style='color:red; text-align:center;'><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <label>Username
      <input type="text" name="username" required />
    </label>

    <label>Password
      <input type="password" name="password" required />
    </label>

    <button type="submit">Login</button>
    <p>Don't have an account? <a href="register.php">Register</a></p>
  </form>

  <div class="admin-access" onclick="toggleAdminForm()">Staff Access</div>
  
  <div class="admin-login-form" id="adminForm">
    <h3>Admin Login</h3>
    <form method="POST" action="login.php">
      <label>Username
        <input type="text" name="username" required />
      </label>
      <label>Password
        <input type="password" name="password" required />
      </label>
      <input type="hidden" name="admin_login" value="1">
      <button type="submit">Login</button>
    </form>
  </div>

  <script>
    function toggleAdminForm() {
      const form = document.getElementById('adminForm');
      form.style.display = form.style.display === 'block' ? 'none' : 'block';
    }
  </script>
</body>
</html>