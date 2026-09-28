<?php
/**
 * driver_api.php
 * Serves JSON data for the Driver Dashboard:
 *   ?action=get_requests  – pending trips
 *   ?action=get_stats     – completed trips + earnings
 *   POST ?action=accept_trip – claim a pending trip
 */
session_start();
require 'config.php';
header('Content-Type: application/json');

// ── Auth: check session role (stored as 'role' key by login.php) ──────────────
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'driver') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$action    = $_GET['action'] ?? '';
$driver_id = (int) $_SESSION['user_id'];

// ── get_requests ──────────────────────────────────────────────────────────────
if ($action === 'get_requests') {
    try {
        // Use `bookings` table (that's what the schema actually has).
        // Adjust column names if your trip/booking table differs.
        $stmt = $pdo->query(
            "SELECT id, pickup_location, rental_date AS destination, 0 AS fare
             FROM bookings
             WHERE status = 'pending'
             ORDER BY created_at DESC
             LIMIT 50"
        );
        $requests = $stmt->fetchAll();
        echo json_encode(['status' => 'success', 'data' => $requests]);
    } catch (\PDOException $e) {
        error_log('[driver_api/get_requests] ' . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Failed to fetch requests.']);
    }

// ── accept_trip ───────────────────────────────────────────────────────────────
} elseif ($action === 'accept_trip') {
    $trip_id = (int) ($_POST['trip_id'] ?? 0);
    if ($trip_id < 1) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid trip ID.']);
        exit;
    }
    try {
        $stmt = $pdo->prepare(
            "UPDATE bookings SET status = 'confirmed'
             WHERE id = ? AND status = 'pending'"
        );
        $stmt->execute([$trip_id]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Trip accepted!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Trip no longer available.']);
        }
    } catch (\PDOException $e) {
        error_log('[driver_api/accept_trip] ' . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Failed to accept trip.']);
    }

// ── get_stats ─────────────────────────────────────────────────────────────────
} elseif ($action === 'get_stats') {
    try {
        // bookings doesn't have a payment table yet; return safe zero values
        // until you add a payment/trip table. Swap the query below when ready.
        $stmt = $pdo->prepare(
            "SELECT COUNT(id) AS trips_completed
             FROM bookings
             WHERE status = 'completed'"
        );
        $stmt->execute();
        $row = $stmt->fetch();

        echo json_encode([
            'status' => 'success',
            'data'   => [
                'trips_completed' => (int) ($row['trips_completed'] ?? 0),
                'net_earnings'    => 0,
                'fees_paid'       => 0,
            ],
        ]);
    } catch (\PDOException $e) {
        error_log('[driver_api/get_stats] ' . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Failed to fetch stats.']);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
}
