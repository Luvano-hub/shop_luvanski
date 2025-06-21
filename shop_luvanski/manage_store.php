<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM stores WHERE seller_id = ?");
$stmt->execute([$user_id]);
$store = $stmt->fetch();

if (!$store) {
    echo "Store not found.";
    exit();
}
?>

<h2>Welcome to Your Store: <?php echo $store['store_name']; ?></h2>
<p><?php echo $store['description']; ?></p>
<p>Location: <?php echo $store['location']; ?></p>

<a href="manage_inventory.php"><button>Manage Inventory</button></a>
<a href="manage_orders.php"><button>Manage Orders</button></a>
