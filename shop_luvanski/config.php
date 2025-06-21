<?php
session_start();

$host = 'sql111.infinityfree.com';
$dbname = 'if0_39226128_shop_luvanski';
$username = 'if0_39226128';
$password = 'qxAyqg4Sgu3f4';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
