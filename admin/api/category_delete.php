<?php
require_once __DIR__ . '/guard.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = isset($data['id']) ? (int) $data['id'] : 0;
if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing category id']);
    exit;
}

try {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare('UPDATE blog_categories SET is_active = 0 WHERE id = :id');
    $stmt->execute([':id' => $id]);
    echo json_encode(['message' => 'Category deactivated']);
} catch (Throwable $e) {
    error_log('Category delete failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to deactivate category']);
}
