<?php
/**
 * vehicles_api.php  –  Public endpoint (no login required)
 * Returns driver-uploaded vehicles from driver_profile.
 *
 * GET  /backend/vehicles_api.php          → all vehicles (vehicle_image NOT NULL)
 * GET  /backend/vehicles_api.php?id=N     → single vehicle by driver_profile.id
 */
require 'config.php';
header('Content-Type: application/json');

// ── Helper: never expose raw SQL errors ───────────────────────────────────────
function fail(string $msg): void {
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

// ── Columns returned – NEVER include license_image, email, phone, password ────
$safeColumns = "
    dp.id,
    dp.vehicle_type,
    dp.plate_number,
    dp.capacity,
    dp.vehicle_image,
    u.username AS driver_name
";

try {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

    if ($id > 0) {
        // ── Single vehicle ────────────────────────────────────────────────────
        $stmt = $pdo->prepare("
            SELECT $safeColumns
            FROM   driver_profile dp
            JOIN   users u ON u.id = dp.user_id
            WHERE  dp.id = ?
              AND  dp.vehicle_image IS NOT NULL
              AND  dp.vehicle_image <> ''
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            fail('Vehicle not found.');
        }

        echo json_encode(['success' => true, 'vehicle' => $row]);

    } else {
        // ── All vehicles ──────────────────────────────────────────────────────
        $stmt = $pdo->query("
            SELECT $safeColumns
            FROM   driver_profile dp
            JOIN   users u ON u.id = dp.user_id
            WHERE  dp.vehicle_image IS NOT NULL
              AND  dp.vehicle_image <> ''
            ORDER BY dp.updated_at DESC
        ");
        $rows = $stmt->fetchAll();

        echo json_encode(['success' => true, 'vehicles' => $rows]);
    }

} catch (\PDOException $e) {
    error_log('[vehicles_api] ' . $e->getMessage());
    fail('Could not load vehicles. Please try again later.');
}
