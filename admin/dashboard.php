<?php
require_once __DIR__ . '/includes/db.php';
$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require __DIR__ . '/includes/header.php';

$pdo = getDbConnection();

$counts = $pdo->query(
    "SELECT
        SUM(status != 'deleted') AS total,
        SUM(status = 'published') AS published,
        SUM(status = 'draft') AS draft,
        SUM(status = 'scheduled') AS scheduled,
        SUM(status = 'pending') AS pending,
        SUM(views) AS total_views
     FROM blogs"
)->fetch();

$recent = $pdo->query(
    "SELECT id, title, slug, status, views, updated_at FROM blogs WHERE status != 'deleted' ORDER BY updated_at DESC LIMIT 8"
)->fetchAll();

$upcoming = $pdo->query(
    "SELECT id, title, publish_date FROM blogs WHERE status = 'scheduled' AND publish_date >= NOW() ORDER BY publish_date ASC LIMIT 5"
)->fetchAll();
?>

<div class="stats-grid">
  <div class="stat-card"><div class="stat-icon">📰</div><div class="stat-label">Total Blogs</div><div class="stat-value"><?= (int)($counts['total'] ?? 0) ?></div></div>
  <div class="stat-card"><div class="stat-icon">✅</div><div class="stat-label">Published</div><div class="stat-value"><?= (int)($counts['published'] ?? 0) ?></div></div>
  <div class="stat-card"><div class="stat-icon">📝</div><div class="stat-label">Drafts</div><div class="stat-value"><?= (int)($counts['draft'] ?? 0) ?></div></div>
  <div class="stat-card"><div class="stat-icon">⏰</div><div class="stat-label">Scheduled</div><div class="stat-value"><?= (int)($counts['scheduled'] ?? 0) ?></div></div>
  <div class="stat-card"><div class="stat-icon">🕵️</div><div class="stat-label">Pending Review</div><div class="stat-value"><?= (int)($counts['pending'] ?? 0) ?></div></div>
  <div class="stat-card"><div class="stat-icon">👁️</div><div class="stat-label">Total Views</div><div class="stat-value"><?= (int)($counts['total_views'] ?? 0) ?></div></div>
</div>

<div class="two-col">
  <div class="card">
    <div class="card-header"><h3>Recently Updated</h3></div>
    <div class="card-body" style="padding:0;">
      <?php if ($recent): ?>
      <table>
        <thead><tr><th>Title</th><th>Status</th><th>Views</th><th>Updated</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($recent as $b): ?>
          <tr>
            <td><?= e($b['title']) ?></td>
            <td><?= status_badge($b['status']) ?></td>
            <td><?= (int)$b['views'] ?></td>
            <td><?= time_ago($b['updated_at']) ?></td>
            <td>
              <a class="icon-btn" href="/admin/blog-edit.php?id=<?= (int)$b['id'] ?>" title="Edit">✏️</a>
              <?php if ($b['status'] === 'published'): ?>
              <a class="icon-btn" href="/blogs/<?= e($b['slug']) ?>" target="_blank" title="View Live">🔗</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
        <div class="empty-state"><div class="icon">📭</div>No blogs yet. Create your first post.</div>
      <?php endif; ?>
    </div>
  </div>

  <div>
    <div class="card">
      <div class="card-header"><h3>Quick Actions</h3></div>
      <div class="card-body" style="display:flex; flex-direction:column; gap:10px;">
        <a class="btn btn-primary btn-block" href="/admin/blog-edit.php">+ New Blog Post</a>
        <a class="btn btn-outline btn-block" href="/admin/blogs.php?status=pending">🕵️ Review Pending</a>
        <a class="btn btn-outline btn-block" href="/admin/categories.php">🗂️ Manage Categories</a>
        <a class="btn btn-outline btn-block" href="/admin/authors.php">🧑‍💼 Manage Authors</a>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h3>Upcoming Scheduled</h3></div>
      <div class="card-body">
        <?php if ($upcoming): ?>
          <?php foreach ($upcoming as $u): ?>
            <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid var(--border); font-size:13px;">
              <span><?= e($u['title']) ?></span>
              <span class="table-slug"><?= date('M j, g:ia', strtotime($u['publish_date'])) ?></span>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty-state" style="padding:20px 0;"><div class="icon">🗓️</div>Nothing scheduled.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
