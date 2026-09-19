<?php
require_once __DIR__ . '/guard.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = isset($data['id']) && $data['id'] !== '' ? (int) $data['id'] : null;
$name = trim((string)($data['name'] ?? ''));
$bio = trim((string)($data['bio'] ?? ''));
$image = trim((string)($data['image'] ?? ''));
$pageUrl = trim((string)($data['page_url'] ?? ''));
$isActive = !empty($data['is_active']) ? 1 : 0;

if ($name === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Author name is required']);
    exit;
}

try {
    $pdo = getDbConnection();

    if ($id) {
        $stmt = $pdo->prepare('UPDATE blog_authors SET name=:name, bio=:bio, image=:image, page_url=:page_url, is_active=:is_active WHERE id=:id');
        $stmt->execute([':name' => $name, ':bio' => $bio, ':image' => $image, ':page_url' => $pageUrl, ':is_active' => $isActive, ':id' => $id]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO blog_authors (name, bio, image, page_url, is_active) VALUES (:name, :bio, :image, :page_url, :is_active)');
        $stmt->execute([':name' => $name, ':bio' => $bio, ':image' => $image, ':page_url' => $pageUrl, ':is_active' => $isActive]);
        $id = (int) $pdo->lastInsertId();
    }

    echo json_encode(['message' => 'Author saved', 'id' => $id]);
} catch (Throwable $e) {
    error_log('Author save failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save author']);
}
