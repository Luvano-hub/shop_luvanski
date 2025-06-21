<?php
// Prevent any output before headers
ob_start();
require 'config.php';
ob_end_clean();

session_start();

// Disable error output to response
error_reporting(0);
ini_set('display_errors', 0);

try {
    // Validate session first
    if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'seller') {
        throw new Exception('Unauthorized access. Please login as seller.');
    }

    // Get and validate input
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['order_id'], $input['status'])) {
        throw new Exception('Invalid request format');
    }

    $orderId = (int)$input['order_id'];
    $newStatus = $input['status'];
    $sellerId = (int)$_SESSION['user_id'];

    // Verify order belongs to seller
    $verifyStmt = $pdo->prepare("
        SELECT 1 
        FROM order_items 
        WHERE order_id = ? 
        AND seller_id = ?
        LIMIT 1
    ");
    $verifyStmt->execute([$orderId, $sellerId]);
    
    if (!$verifyStmt->fetchColumn()) {
        throw new Exception('Order not found or access denied');
    }

    // Update status
    $updateStmt = $pdo->prepare("
        UPDATE orders 
        SET status = ? 
        WHERE id = ?
    ");
    $updateStmt->execute([$newStatus, $orderId]);

    // Successful response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Status updated successfully',
        'new_status' => $newStatus
    ]);
    exit();

} catch (PDOException $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error',
        'code' => $e->getCode()
    ]);
    exit();
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit();
}