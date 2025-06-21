<?php
require 'config.php';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $img = $_FILES['image']['name'];
  move_uploaded_file($_FILES['image']['tmp_name'], "images/$img");
  $stmt = $conn->prepare(
    "INSERT INTO items(seller_id,title,description,price,quantity,category,image)
     VALUES(?,?,?,?,?,?,?)"
  );
  $stmt->execute([
    $_SESSION['user_id'],
    $_POST['title'],$_POST['description'],
    $_POST['price'],$_POST['quantity'],
    $_POST['category'],$img
  ]);
  $msg = "Item added.";
}
?>
<!DOCTYPE html><html><head>
  <meta charset="UTF-8"><title>Add Item | Shop Luvanski</title>
  <link rel="stylesheet" href="style.css">
</head><body>
  <nav><a href="seller_dashboard.php">Dashboard</a> | <a href="logout.php">Logout</a></nav>
  <h2>Add New Item</h2>
  <?php if(!empty($msg)) echo "<p>$msg</p>"; ?>
  <form method="POST" enctype="multipart/form-data">
    <label>Title<input name="title" required></label>
    <label>Description<textarea name="description"></textarea></label>
    <label>Price<input name="price" type="number" step="0.01" required></label>
    <label>Quantity<input name="quantity" type="number" required></label>
    <label>Category<input name="category" required></label>
    <label>Image<input name="image" type="file" required></label>
    <button type="submit">Add Item</button>
  </form>
</body></html>
