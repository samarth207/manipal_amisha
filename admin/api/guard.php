<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json; charset=utf-8');
if (!current_admin()) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}
