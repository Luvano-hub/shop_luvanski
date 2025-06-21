<!DOCTYPE html>
<html><head>
  <meta charset="UTF-8">
  <title>Register | Shop Luvanski</title>
  <link rel="stylesheet" href="style.css">
</head><body>
  <h2>Create an Account</h2>
  <form action="register.php" method="POST">
    <label>Username<input name="username" required></label>
    <label>Email<input type="email" name="email" required></label>
    <label>Password<input type="password" name="password" required></label>
    <label>Account Type
      <select name="role" required>
        <option value="">-- Choose --</option>
        <option value="buyer">Buyer</option>
        <option value="seller">Seller</option>
      </select>
    </label>
    <button type="submit">Register</button>
  </form>
  <p>Already have an account? <a href="login.php">Login</a></p>
</body></html>
