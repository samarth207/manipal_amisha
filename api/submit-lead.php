<?php
declare(strict_types=1);

define('LEADS_API', true);

header('Content-Type: application/json; charset=utf-8');
// Tighten this to your own domain(s) once the site is live on Hostinger.
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

require __DIR__ . '/config.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON body']);
    exit;
}

$fullName = trim((string)($data['fullName'] ?? ''));
$email    = trim((string)($data['email'] ?? ''));
$mobile   = trim((string)($data['mobile'] ?? ''));
$course   = trim((string)($data['course'] ?? ''));
$consent  = !empty($data['consent']) ? 1 : 0;
$source   = trim((string)($data['source'] ?? 'website'));

if ($fullName === '' || $email === '' || $mobile === '' || $course === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address']);
    exit;
}

if (!preg_match('/^[0-9+\-\s]{7,15}$/', $mobile)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid mobile number']);
    exit;
}

try {
    $pdo = getDbConnection();

    // Single shared table for every lead form on the site.
    $stmt = $pdo->prepare(
        'INSERT INTO leads (full_name, email, mobile, course, consent, source, ip_address, user_agent, created_at)
         VALUES (:full_name, :email, :mobile, :course, :consent, :source, :ip_address, :user_agent, NOW())'
    );

    $stmt->execute([
        ':full_name'  => $fullName,
        ':email'      => $email,
        ':mobile'     => $mobile,
        ':course'     => $course,
        ':consent'    => $consent,
        ':source'     => $source,
        ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
    ]);

    http_response_code(200);
    echo json_encode(['message' => 'Lead submitted successfully']);
} catch (Throwable $e) {
    error_log('Lead submission failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to submit lead']);
}
