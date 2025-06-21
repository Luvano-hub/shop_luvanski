<?php
require 'config.php';
$stmt = $conn->query("SELECT * FROM items");
$items = $stmt->fetchAll();
?>
<!DOCTYPE html><html><head>
  <meta charset="UTF-8"><title>Browse | Shop Luvanski</title>
  <link rel="stylesheet" href="style.css">
  <script src="main.js" defer></script>
</head><body>
  <nav><a href="buyer_dashboard.php">Dashboard</a> | <a href="logout.php">Logout</a></nav>
  <h2>Browse Items</h2>
  <div class="cards">
    <?php foreach($items as $i): ?>
      <div class="card">
        <img src="images/<?=htmlspecialchars($i['image'])?>" alt="">
        <h3><?=htmlspecialchars($i['title'])?></h3>
        <p>$<?=number_format($i['price'],2)?></p>
        <button class="add-to-cart" data-id="<?=$i['id']?>">Add to cart</button>
      </div>
    <?php endforeach; ?>
  </div>
</body></html>
