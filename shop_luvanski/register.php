<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'buyer';

    if ($username && $email && $password && in_array($role, ['buyer', 'seller'])) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        try {
            $stmt->execute([$username, $email, $hashedPassword, $role]);
            header("Location: login.php");
            exit();
        } catch (PDOException $e) {
            $error = "Registration failed: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all fields correctly.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Register | Shop Luvanski</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <form action="register.php" method="POST">
    <h2>Create an Account</h2>
    
    <label>Username
      <input name="username" required />
    </label>

    <label>Email
      <input type="email" name="email" required />
    </label>

    <label>Password
      <input type="password" name="password" required />
    </label>

    <label>Account Type
      <select name="role" required>
        <option value="">-- Choose --</option>
        <option value="buyer">Buyer</option>
        <option value="seller">Seller</option>
      </select>
    </label>

    <button type="submit">Register</button>
    <p>Already have an account? <a href="login.php">Login</a></p>
  </form>
</body>
</html>
