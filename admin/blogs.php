<?php
require_once __DIR__ . '/includes/db.php';
$pageTitle = 'All Blogs';
$activePage = 'blogs';
require __DIR__ . '/includes/header.php';

$pdo = getDbConnection();

$status = $_GET['status'] ?? 'all';
$search = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$where = ["status != 'deleted'"];
$params = [];
if (in_array($status, ['published', 'draft', 'pending', 'scheduled'], true)) {
    $where[] = 'b.status = :status';
    $params[':status'] = $status;
}
if ($search !== '') {
    $where[] = 'b.title LIKE :q';
    $params[':q'] = '%' . $search . '%';
}
$whereSql = implode(' AND ', $where);

$counts = $pdo->query(
    "SELECT
        SUM(status != 'deleted') AS all_count,
        SUM(status = 'published') AS published_count,
        SUM(status = 'draft') AS draft_count,
        SUM(status = 'pending') AS pending_count,
        SUM(status = 'scheduled') AS scheduled_count
     FROM blogs"
)->fetch();

$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM blogs b WHERE $whereSql");
$totalStmt->execute($params);
$total = (int) $totalStmt->fetchColumn();
$totalPages = max(1, (int) ceil($total / $perPage));

$sql = "SELECT b.*, a.name AS author_name FROM blogs b
        LEFT JOIN blog_authors a ON a.id = b.author_id
        WHERE $whereSql ORDER BY b.updated_at DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$blogs = $stmt->fetchAll();

function tab_url(string $status, string $search): string
{
    $params = ['status' => $status];
    if ($search !== '') $params['q'] = $search;
    return '/admin/blogs.php?' . http_build_query($params);
}
?>

<div class="status-tabs">
  <a href="<?= tab_url('all', $search) ?>" class="status-tab <?= $status === 'all' ? 'active' : '' ?>">All (<?= (int)($counts['all_count'] ?? 0) ?>)</a>
  <a href="<?= tab_url('published', $search) ?>" class="status-tab <?= $status === 'published' ? 'active' : '' ?>">Published (<?= (int)($counts['published_count'] ?? 0) ?>)</a>
  <a href="<?= tab_url('draft', $search) ?>" class="status-tab <?= $status === 'draft' ? 'active' : '' ?>">Drafts (<?= (int)($counts['draft_count'] ?? 0) ?>)</a>
  <a href="<?= tab_url('pending', $search) ?>" class="status-tab <?= $status === 'pending' ? 'active' : '' ?>">Pending (<?= (int)($counts['pending_count'] ?? 0) ?>)</a>
  <a href="<?= tab_url('scheduled', $search) ?>" class="status-tab <?= $status === 'scheduled' ? 'active' : '' ?>">Scheduled (<?= (int)($counts['scheduled_count'] ?? 0) ?>)</a>
</div>

<form class="filter-bar" method="get">
  <input type="hidden" name="status" value="<?= e($status) ?>">
  <input type="text" name="q" placeholder="Search blogs by title..." value="<?= e($search) ?>">
  <button type="submit" class="btn btn-outline">Search</button>
  <a href="<?= tab_url($status, '') ?>" class="btn btn-muted">Clear</a>
</form>

<div class="card">
  <div class="card-body" style="padding:0;">
    <?php if ($blogs): ?>
    <table>
      <thead>
        <tr><th>Title</th><th>Author</th><th>Status</th><th>Published</th><th>Views</th><th>Updated</th><th>Actions</th></tr>
      </thead>
      <tbody>
      <?php foreach ($blogs as $b): ?>
        <tr>
          <td>
            <div><?= e($b['title']) ?></div>
            <div class="table-slug">/blogs/<?= e($b['slug']) ?><?= $b['read_time'] ? ' · ' . (int)$b['read_time'] . ' min read' : '' ?></div>
          </td>
          <td><?= e($b['author_name'] ?? '—') ?></td>
          <td><?= status_badge($b['status']) ?></td>
          <td><?= $b['publish_date'] ? date('M j, Y', strtotime($b['publish_date'])) : '—' ?></td>
          <td><?= (int)$b['views'] ?></td>
          <td><?= time_ago($b['updated_at']) ?></td>
          <td>
            <a class="icon-btn" href="/admin/blog-edit.php?id=<?= (int)$b['id'] ?>" title="Edit">✏️</a>
            <?php if ($b['status'] === 'published'): ?>
              <a class="icon-btn" href="/blogs/<?= e($b['slug']) ?>" target="_blank" title="View Live">🔗</a>
            <?php endif; ?>
            <button class="icon-btn danger" title="Delete" onclick="deleteBlog(<?= (int)$b['id'] ?>, '<?= e(addslashes($b['title'])) ?>')">🗑️</button>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
      <div class="empty-state">
        <div class="icon">📭</div>
        <p>No blogs found<?= $search !== '' ? ' for "' . e($search) . '"' : '' ?>.</p>
        <a class="btn btn-primary" href="/admin/blog-edit.php">+ Create your first blog</a>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php if ($totalPages > 1): ?>
<div class="pagination">
  <?php for ($p = 1; $p <= $totalPages; $p++): ?>
    <a class="<?= $p === $page ? 'active' : '' ?>" href="?status=<?= e($status) ?>&q=<?= urlencode($search) ?>&page=<?= $p ?>"><?= $p ?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>

<script>
function deleteBlog(id, title) {
  confirmAction('Delete blog?', `"${title}" will be moved to deleted status.`, async () => {
    try {
      await apiRequest('/admin/api/blog_delete.php', { method: 'POST', body: JSON.stringify({ id }) });
      showToast('Blog deleted', 'success');
      setTimeout(() => location.reload(), 700);
    } catch (err) {
      showToast(err.message, 'error');
    }
  });
}
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
