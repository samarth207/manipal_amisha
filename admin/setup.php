<?php
// One-time setup script: creates or resets the first admin user with a real
// bcrypt hash generated on this server. DELETE THIS FILE after use.
require_once __DIR__ . '/includes/db.php';

$done = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $fullName = trim($_POST['full_name'] ?? 'Site Administrator');

    if ($username === '' || strlen($password) < 8) {
        $error = 'Username is required and password must be at least 8 characters.';
    } else {
        try {
            $pdo = getDbConnection();
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                'INSERT INTO admin_users (username, password_hash, full_name, role, is_active)
                 VALUES (:username, :hash, :full_name, "admin", 1)
                 ON DUPLICATE KEY UPDATE password_hash = :hash2, full_name = :full_name2, is_active = 1'
            );
            $stmt->execute([
                ':username' => $username,
                ':hash' => $hash,
                ':full_name' => $fullName,
                ':hash2' => $hash,
                ':full_name2' => $fullName,
            ]);
            $done = true;
        } catch (Throwable $ex) {
            $error = 'Database error: ' . $ex->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Setup</title>
<style>
  body { font-family: Poppins, Arial, sans-serif; background:#0f1d35; color:#1a2332; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; }
  .card { background:#fff; border-radius:12px; padding:32px; width:380px; box-shadow:0 20px 40px rgba(0,0,0,.3); }
  h1 { font-size:18px; margin:0 0 16px; }
  label { display:block; font-size:13px; margin:12px 0 4px; color:#6b7280; }
  input { width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:8px; font-size:14px; box-sizing:border-box; }
  button { margin-top:20px; width:100%; padding:12px; background:#F26522; color:#fff; border:none; border-radius:8px; font-weight:600; cursor:pointer; }
  .msg-ok { background:#ecfdf5; color:#10b981; padding:10px; border-radius:8px; font-size:13px; margin-bottom:12px; }
  .msg-err { background:#fef2f2; color:#ef4444; padding:10px; border-radius:8px; font-size:13px; margin-bottom:12px; }
</style>
</head>
<body>
  <div class="card">
    <h1>Create / Reset Admin User</h1>
    <?php if ($done): ?>
      <div class="msg-ok">Admin user saved. <a href="/admin/login.php">Go to login</a>. Now delete this setup.php file.</div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="msg-err"><?= e($error) ?></div>
    <?php endif; ?>
    <?php if (!$done): ?>
    <form method="post">
      <label>Username</label>
      <input type="text" name="username" required>
      <label>Full Name</label>
      <input type="text" name="full_name" placeholder="Site Administrator">
      <label>Password (min 8 characters)</label>
      <input type="password" name="password" required minlength="8">
      <button type="submit">Save Admin User</button>
    </form>
    <?php endif; ?>
  </div>
</body>
</html>
