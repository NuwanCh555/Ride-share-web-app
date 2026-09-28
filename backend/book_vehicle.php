<?php
// book_vehicle.php
session_start();
require 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to book a vehicle.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $vehicleId = $_POST['vehicle_id'] ?? null;
    $pickup = trim($_POST['pickup'] ?? '');
    $date = $_POST['date'] ?? '';

    if (!$vehicleId || empty($pickup) || empty($date)) {
        echo json_encode(['status' => 'error', 'message' => 'All booking fields are required.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, vehicle_id, pickup_location, rental_date) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $vehicleId, $pickup, $date]);
        
        echo json_encode(['status' => 'success', 'message' => 'Booking confirmed successfully!']);
    } catch (\PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Booking failed. Please try again.']);
    }
}
?>
