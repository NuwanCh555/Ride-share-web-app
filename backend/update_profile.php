<?php
/**
 * update_profile.php
 * Handles driver profile + vehicle detail upserts via PDO.
 * Returns JSON { success: true/false, message: "..." }
 */
session_start();
require 'config.php';
header('Content-Type: application/json');

// ── Helpers ──────────────────────────────────────────────────────────────────
function jsonOut(bool $ok, string $msg): void {
    echo json_encode(['success' => $ok, 'message' => $msg]);
    exit;
}

function handleUpload(string $field, string $prefix, string $uploadDir): ?string {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        return null;                        // file not submitted / skipped
    }
    $file     = $_FILES[$field];
    $maxBytes = 2 * 1024 * 1024;           // 2 MB

    if ($file['size'] > $maxBytes) {
        jsonOut(false, "File '$field' exceeds the 2 MB limit.");
    }

    // Allow only safe MIME types (also check extension as belt-and-braces)
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $finfo   = new finfo(FILEINFO_MIME_TYPE);
    $mime    = $finfo->file($file['tmp_name']);
    if (!array_key_exists($mime, $allowed)) {
        jsonOut(false, "File '$field' must be JPG, PNG, or WebP.");
    }

    $ext      = $allowed[$mime];
    $safeName = $prefix . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $dest     = $uploadDir . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        jsonOut(false, "Could not save uploaded file '$field'.");
    }

    return 'uploads/' . $safeName;         // relative path stored in DB
}

// ── Auth ─────────────────────────────────────────────────────────────────────
// Use session user_id; fall back to 2 only while testing (remove before launch)
$driver_id = $_SESSION['user_id'] ?? 2;

// ── Only allow POST ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonOut(false, 'Invalid request method.');
}

// ── Validate text inputs ──────────────────────────────────────────────────────
$vehicle_type = trim($_POST['vehicle_type'] ?? '');
$plate_number = trim($_POST['plate_no']     ?? '');      // HTML field name is plate_no
$capacity     = (int) ($_POST['capacity']   ?? 0);

if ($vehicle_type === '') jsonOut(false, 'Vehicle type is required.');
if ($plate_number === '') jsonOut(false, 'Plate number is required.');
if ($capacity < 1)        jsonOut(false, 'Capacity must be at least 1.');

// ── Upload directory ──────────────────────────────────────────────────────────
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// ── Handle file uploads ───────────────────────────────────────────────────────
$licenseImagePath = handleUpload('profile_picture', 'license', $uploadDir);
$vehicleImagePath = handleUpload('vehicle_image',   'vehicle', $uploadDir);

// ── DB upsert (single driver_profile row, keyed by user_id) ──────────────────
try {
    $sql = "
        INSERT INTO driver_profile
            (user_id, license_image, vehicle_image, vehicle_type, plate_number, capacity)
        VALUES
            (:uid, :li, :vi, :vt, :pn, :cap)
        ON DUPLICATE KEY UPDATE
            vehicle_type  = VALUES(vehicle_type),
            plate_number  = VALUES(plate_number),
            capacity      = VALUES(capacity),
            license_image = IF(VALUES(license_image) IS NOT NULL, VALUES(license_image), license_image),
            vehicle_image = IF(VALUES(vehicle_image) IS NOT NULL, VALUES(vehicle_image), vehicle_image),
            updated_at    = CURRENT_TIMESTAMP
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':uid' => $driver_id,
        ':li'  => $licenseImagePath,
        ':vi'  => $vehicleImagePath,
        ':vt'  => $vehicle_type,
        ':pn'  => $plate_number,
        ':cap' => $capacity,
    ]);

    jsonOut(true, 'Profile and vehicle details saved successfully!');

} catch (\PDOException $e) {
    // Log real error server-side; never expose to client
    error_log('[update_profile] PDO error: ' . $e->getMessage());
    jsonOut(false, 'A database error occurred. Please try again.');
}
