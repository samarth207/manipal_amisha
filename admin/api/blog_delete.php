<?php
require_once __DIR__ . '/guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = isset($data['id']) ? (int) $data['id'] : 0;
if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing blog id']);
    exit;
}

try {
    $pdo = getDbConnection();
    // Soft delete keeps rows for audit/analytics instead of hard-removing them.
    $stmt = $pdo->prepare("UPDATE blogs SET status = 'deleted' WHERE id = :id");
    $stmt->execute([':id' => $id]);
    echo json_encode(['message' => 'Blog deleted']);
} catch (Throwable $e) {
    error_log('Blog delete failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to delete blog']);
}
