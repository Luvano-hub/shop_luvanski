<?php
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: login.php");
    exit;
}

$sellerId = $_SESSION['user_id'];

// Fetch the seller's store
$storeStmt = $pdo->prepare("SELECT * FROM stores WHERE seller_id = ?");
$storeStmt->execute([$sellerId]);
$store = $storeStmt->fetch();

// Handle form submission for create or update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $storeName = $_POST['store_name'];
    $desc = $_POST['description'];
    $location = $_POST['location'];

    if ($store) {
        $updateStmt = $pdo->prepare("UPDATE stores SET store_name = ?, description = ?, location = ? WHERE seller_id = ?");
        $updateStmt->execute([$storeName, $desc, $location, $sellerId]);
    } else {
        $insertStmt = $pdo->prepare("INSERT INTO stores (seller_id, store_name, description, location) VALUES (?, ?, ?, ?)");
        $insertStmt->execute([$sellerId, $storeName, $desc, $location]);
    }

    header("Location: create_store.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $store ? 'Edit Store' : 'Create Store' ?></title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 30px;
            background: linear-gradient(135deg, #d4fc79, #96e6a1);
            animation: fadeIn 0.6s ease-in;
        }

        .container {
            background-color: white;
            max-width: 600px;
            margin: 0 auto;
            padding: 25px 30px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            font-size: 24px;
            color: #333;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #444;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
            margin-bottom: 20px;
            resize: vertical;
        }

        textarea {
            min-height: 100px;
        }

        button {
            padding: 12px 20px;
            background-color: #6f42c1;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 100%;
        }

        button:hover {
            background-color: #5a32a3;
        }

        .btn-back {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
        }

        .btn-back button {
            background-color: #6c757d;
        }

        .btn-back button:hover {
            background-color: #5a6268;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<div class="container">
    <a class="btn-back" href="seller_dashboard.php">
        <button>← Back to Dashboard</button>
    </a>

    <h2><?= $store ? 'Edit Your Store' : 'Create Your Store' ?></h2>

    <form method="POST">
        <label for="store_name">Store Name:</label>
        <input type="text" id="store_name" name="store_name" placeholder="Store Name" required
               value="<?= $store ? htmlspecialchars($store['store_name']) : '' ?>">

        <label for="description">Description:</label>
        <textarea id="description" name="description" placeholder="Store Description" required><?= $store ? htmlspecialchars($store['description']) : '' ?></textarea>

        <label for="location">Location:</label>
        <input type="text" id="location" name="location" placeholder="e.g. Johannesburg, Cape Town" required
               value="<?= $store ? htmlspecialchars($store['location']) : '' ?>">

        <button type="submit"><?= $store ? 'Update Store' : 'Create Store' ?></button>
    </form>
</div>

</body>
</html>
