<?php
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function current_admin(): ?array
{
    return $_SESSION['admin_user'] ?? null;
}

function require_login(): void
{
    if (!current_admin()) {
        header('Location: /admin/login.php');
        exit;
    }
}

function attempt_login(string $username, string $password): ?array
{
    $pdo = getDbConnection();
    $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE username = :username AND is_active = 1 LIMIT 1');
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return null;
    }

    $pdo->prepare('UPDATE admin_users SET last_login = NOW() WHERE id = :id')
        ->execute([':id' => $user['id']]);

    unset($user['password_hash']);
    return $user;
}

function admin_logout(): void
{
    $_SESSION = [];
    session_destroy();
}
