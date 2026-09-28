<?php
session_start();
require 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Username and password are required.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            echo json_encode([
                'status' => 'success',
                'role' => $user['role'],
                'message' => 'Login successful!'
            ]);
            exit();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid credentials.']);
        }
    } catch (\PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error occurred: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
