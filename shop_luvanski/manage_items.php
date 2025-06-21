<?php
require 'config.php';
$stmt = $conn->prepare("SELECT * FROM items WHERE seller_id=?");
$stmt->execute([$_SESSION['user_id']]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html><html><head>
  <meta charset="UTF-8"><title>My Items | Shop Luvanski</title>
  <link rel="stylesheet" href="style.css">
</head><body>
  <nav><a href="seller_dashboard.php">Dashboard</a> | <a href="logout.php">Logout</a></nav>
  <h2>Your Inventory</h2>
  <table border="1" cellpadding="5">
    <tr><th>Title</th><th>Qty</th><th>Price</th></tr>
    <?php foreach($items as $i): ?>
      <tr>
        <td><?=htmlspecialchars($i['title'])?></td>
        <td><?=$i['quantity']?></td>
        <td>$<?=number_format($i['price'],2)?></td>
      </tr>
    <?php endforeach; ?>
  </table>
</body></html>
