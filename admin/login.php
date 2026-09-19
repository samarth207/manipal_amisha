<?php
require_once __DIR__ . '/includes/auth.php';

if (current_admin()) {
    header('Location: /admin/dashboard.php');
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $user = attempt_login($username, $password);
    if ($user) {
        $_SESSION['admin_user'] = $user;
        header('Location: /admin/dashboard.php');
        exit;
    }
    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Login - Online Manipal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/admin/assets/css/admin.css">
</head>
<body class="login-body">
  <div class="login-wrap">
    <div class="login-card">
      <div class="login-brand">
        <img src="/online-manipal-logo.svg" alt="Online Manipal">
        <h1>Admin Panel</h1>
        <p>Sign in to manage the blog</p>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="post" class="login-form">
        <div class="form-group icon-group">
          <span class="input-icon">👤</span>
          <input type="text" name="username" placeholder="Username" required autofocus>
        </div>
        <div class="form-group icon-group">
          <span class="input-icon">🔒</span>
          <input type="password" name="password" id="loginPassword" placeholder="Password" required>
          <button type="button" class="toggle-password" onclick="const i=document.getElementById('loginPassword'); i.type = i.type==='password'?'text':'password'; this.textContent = i.type==='password'?'Show':'Hide';">Show</button>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Sign In</button>
      </form>

      <div class="login-links">
        <a href="/blogs">&larr; Back to Blog</a>
        <a href="/">Site Homepage</a>
      </div>
    </div>
  </div>
</body>
</html>
