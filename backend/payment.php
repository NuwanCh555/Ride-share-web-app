<?php
session_start();
require 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trip_id = $_POST['trip_id'] ?? 0;
    $amount = $_POST['amount'] ?? 0;
    
    if ($trip_id <= 0 || $amount <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid trip ID or amount.']);
        exit;
    }
    
    // Calculate 15% platform commission
    $platform_fee = $amount * 0.15;
    $net_earnings = $amount * 0.85;
    
    try {
        $pdo->beginTransaction();
        
        // Insert payment record
        $stmt = $pdo->prepare("INSERT INTO payment (trip_id, amount, platform_fee, net_earnings, payment_status) VALUES (?, ?, ?, ?, 'completed')");
        $stmt->execute([$trip_id, $amount, $platform_fee, $net_earnings]);
        
        // Update trip status to completed
        $stmt2 = $pdo->prepare("UPDATE trip SET status = 'completed' WHERE id = ?");
        $stmt2->execute([$trip_id]);
        
        $pdo->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Payment processed successfully.',
            'data' => [
                'amount' => number_format($amount, 2, '.', ''),
                'platform_fee' => number_format($platform_fee, 2, '.', ''),
                'net_earnings' => number_format($net_earnings, 2, '.', '')
            ]
        ]);
        
    } catch (\PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Database error while processing payment.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
