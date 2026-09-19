<?php
require_once __DIR__ . '/guard.php';

$data = json_decode(file_get_contents('php://input'), true);
$current = (string)($data['current_password'] ?? '');
$new = (string)($data['new_password'] ?? '');
$confirm = (string)($data['confirm_password'] ?? '');

if (strlen($new) < 8) {
    http_response_code(400);
    echo json_encode(['error' => 'New password must be at least 8 characters']);
    exit;
}
if ($new !== $confirm) {
    http_response_code(400);
    echo json_encode(['error' => 'New password and confirmation do not match']);
    exit;
}

try {
    $pdo = getDbConnection();
    $admin = current_admin();
    $stmt = $pdo->prepare('SELECT password_hash FROM admin_users WHERE id = :id');
    $stmt->execute([':id' => $admin['id']]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($current, $row['password_hash'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Current password is incorrect']);
        exit;
    }

    $hash = password_hash($new, PASSWORD_DEFAULT);
    $pdo->prepare('UPDATE admin_users SET password_hash = :hash WHERE id = :id')
        ->execute([':hash' => $hash, ':id' => $admin['id']]);

    echo json_encode(['message' => 'Password updated']);
} catch (Throwable $e) {
    error_log('Password change failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update password']);
}
