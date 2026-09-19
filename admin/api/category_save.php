<?php
require_once __DIR__ . '/guard.php';
require_once __DIR__ . '/../includes/functions.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = isset($data['id']) && $data['id'] !== '' ? (int) $data['id'] : null;
$name = trim((string)($data['name'] ?? ''));
$description = trim((string)($data['description'] ?? ''));
$isActive = !empty($data['is_active']) ? 1 : 0;

if ($name === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Category name is required']);
    exit;
}

try {
    $pdo = getDbConnection();
    $slug = slugify($name);

    if ($id) {
        $stmt = $pdo->prepare('UPDATE blog_categories SET name=:name, slug=:slug, description=:description, is_active=:is_active WHERE id=:id');
        $stmt->execute([':name' => $name, ':slug' => $slug, ':description' => $description, ':is_active' => $isActive, ':id' => $id]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO blog_categories (name, slug, description, is_active) VALUES (:name, :slug, :description, :is_active)');
        $stmt->execute([':name' => $name, ':slug' => $slug, ':description' => $description, ':is_active' => $isActive]);
        $id = (int) $pdo->lastInsertId();
    }

    echo json_encode(['message' => 'Category saved', 'id' => $id]);
} catch (Throwable $e) {
    error_log('Category save failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save category']);
}
