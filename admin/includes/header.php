<?php
/**
 * Expects $pageTitle and $activePage to be set before including this file.
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
require_login();
$admin = current_admin();
$activePage = $activePage ?? '';
$pageTitle = $pageTitle ?? 'Dashboard';

function nav_link(string $key, string $href, string $icon, string $label, string $active): string
{
    $isActive = $active === $key ? ' active' : '';
    return '<a href="' . $href . '" class="nav-link' . $isActive . '"><span class="nav-icon">' . $icon . '</span><span>' . $label . '</span></a>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?> - Online Manipal Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/admin/assets/css/admin.css">
</head>
<body>
  <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle menu">☰</button>
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <img src="/online-manipal-logo.svg" alt="Online Manipal">
      <div>
        <div class="brand-title">Online Manipal</div>
        <div class="brand-subtitle">Admin Panel</div>
      </div>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-group">
        <div class="nav-group-label">Main</div>
        <?= nav_link('dashboard', '/admin/dashboard.php', '📊', 'Dashboard', $activePage) ?>
      </div>
      <div class="nav-group">
        <div class="nav-group-label">Manage</div>
        <?= nav_link('blogs', '/admin/blogs.php', '📰', 'All Blogs', $activePage) ?>
        <?= nav_link('new-blog', '/admin/blog-edit.php', '✍️', 'New Blog', $activePage) ?>
        <?= nav_link('categories', '/admin/categories.php', '🗂️', 'Categories', $activePage) ?>
        <?= nav_link('authors', '/admin/authors.php', '🧑‍💼', 'Authors', $activePage) ?>
      </div>
      <div class="nav-group">
        <div class="nav-group-label">System</div>
        <?= nav_link('settings', '/admin/settings.php', '⚙️', 'Settings', $activePage) ?>
        <a href="/blogs" target="_blank" class="nav-link"><span class="nav-icon">🔗</span><span>View Blog</span></a>
        <a href="/admin/logout.php" class="nav-link"><span class="nav-icon">🚪</span><span>Logout</span></a>
      </div>
    </nav>

    <div class="sidebar-user">
      <div class="avatar"><?= e(strtoupper(substr($admin['full_name'] ?? $admin['username'], 0, 1))) ?></div>
      <div>
        <div class="user-name"><?= e($admin['full_name'] ?? $admin['username']) ?></div>
        <div class="user-role"><?= e(ucfirst($admin['role'] ?? 'editor')) ?></div>
      </div>
    </div>
  </aside>

  <div class="main-wrap">
    <header class="topbar">
      <h1 class="topbar-title"><?= e($pageTitle) ?></h1>
      <div class="topbar-actions">
        <a href="/blogs" target="_blank" class="btn btn-outline">View Site</a>
        <a href="/admin/blog-edit.php" class="btn btn-primary">+ New Blog</a>
      </div>
    </header>
    <main class="main-content">
