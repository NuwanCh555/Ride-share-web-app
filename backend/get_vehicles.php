<?php
// get_vehicles.php
require 'config.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("
        SELECT v.id, v.name, v.image_url, v.price_per_day, v.description, v.page_url, c.name as category 
        FROM vehicles v
        JOIN vehicle_categories c ON v.category_id = c.id
        WHERE v.status = 'available'
    ");
    $vehicles = $stmt->fetchAll();
    echo json_encode(['status' => 'success', 'data' => $vehicles]);
} catch (\PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch vehicles.']);
}
?>
