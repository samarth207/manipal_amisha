<?php
header('Content-Type: application/json; charset=UTF-8');

require __DIR__ . '/config.php';

try {
    $pdo = getDbConnection();
    $slug = isset($_GET['slug']) ? trim((string) $_GET['slug']) : '';

    if ($slug !== '') {
        $stmt = $pdo->prepare(
            'SELECT * FROM blogs
             WHERE slug = :slug
               AND status = :status
               AND (publish_date IS NULL OR publish_date <= NOW())
             ORDER BY publish_date DESC, id DESC
             LIMIT 1'
        );
        $stmt->execute([':slug' => $slug, ':status' => 'published']);
        $blog = $stmt->fetch();
        echo json_encode($blog ?: null);
        exit;
    }

    $stmt = $pdo->prepare(
        'SELECT * FROM blogs
         WHERE status = :status
           AND (publish_date IS NULL OR publish_date <= NOW())
         ORDER BY COALESCE(publish_date, created_at) DESC, id DESC'
    );
    $stmt->execute([':status' => 'published']);
    $blogs = $stmt->fetchAll();
    echo json_encode($blogs);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Unable to load blog posts.']);
}
