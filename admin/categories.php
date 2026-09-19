<?php
require_once __DIR__ . '/includes/db.php';
$pageTitle = 'Categories';
$activePage = 'categories';
require __DIR__ . '/includes/header.php';

$pdo = getDbConnection();
$categories = $pdo->query('SELECT * FROM blog_categories ORDER BY created_at DESC')->fetchAll();
?>

<div class="two-col">
  <div class="card">
    <div class="card-header"><h3>All Categories</h3></div>
    <div class="card-body" style="padding:0;">
      <?php if ($categories): ?>
      <table>
        <thead><tr><th>Name</th><th>Slug</th><th>Description</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($categories as $cat): ?>
          <tr>
            <td><?= e($cat['name']) ?></td>
            <td class="table-slug"><?= e($cat['slug']) ?></td>
            <td><?= e($cat['description']) ?></td>
            <td><?= $cat['is_active'] ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-muted">Inactive</span>' ?></td>
            <td>
              <button class="icon-btn" title="Edit" onclick='editCategory(<?= json_encode($cat) ?>)'>✏️</button>
              <?php if ($cat['is_active']): ?>
              <button class="icon-btn danger" title="Deactivate" onclick="deleteCategory(<?= (int)$cat['id'] ?>)">🗑️</button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
        <div class="empty-state"><div class="icon">🗂️</div>No categories yet.</div>
      <?php endif; ?>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h3 id="categoryFormTitle">Add Category</h3></div>
    <div class="card-body">
      <form id="categoryForm" onsubmit="return false;">
        <input type="hidden" id="categoryId">
        <div class="form-group">
          <label>Name</label>
          <input type="text" id="categoryName" required>
        </div>
        <div class="form-group">
          <label>Description</label>
          <textarea id="categoryDescription" rows="3"></textarea>
        </div>
        <div class="form-group">
          <label><input type="checkbox" id="categoryActive" checked style="width:auto;"> Active</label>
        </div>
        <div style="display:flex; gap:10px;">
          <button type="button" class="btn btn-primary" id="categorySaveBtn">Save Category</button>
          <button type="button" class="btn btn-muted" id="categoryResetBtn">Reset</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function editCategory(cat) {
  document.getElementById('categoryFormTitle').textContent = 'Edit Category';
  document.getElementById('categoryId').value = cat.id;
  document.getElementById('categoryName').value = cat.name;
  document.getElementById('categoryDescription').value = cat.description || '';
  document.getElementById('categoryActive').checked = !!Number(cat.is_active);
}

document.getElementById('categoryResetBtn').addEventListener('click', () => {
  document.getElementById('categoryFormTitle').textContent = 'Add Category';
  document.getElementById('categoryForm').reset();
  document.getElementById('categoryId').value = '';
});

document.getElementById('categorySaveBtn').addEventListener('click', async () => {
  const payload = {
    id: document.getElementById('categoryId').value || null,
    name: document.getElementById('categoryName').value.trim(),
    description: document.getElementById('categoryDescription').value.trim(),
    is_active: document.getElementById('categoryActive').checked,
  };
  if (!payload.name) { showToast('Category name is required', 'error'); return; }
  try {
    await apiRequest('/admin/api/category_save.php', { method: 'POST', body: JSON.stringify(payload) });
    showToast('Category saved', 'success');
    setTimeout(() => location.reload(), 600);
  } catch (err) {
    showToast(err.message, 'error');
  }
});

function deleteCategory(id) {
  confirmAction('Deactivate category?', 'It will no longer be selectable for new blogs.', async () => {
    try {
      await apiRequest('/admin/api/category_delete.php', { method: 'POST', body: JSON.stringify({ id }) });
      showToast('Category deactivated', 'success');
      setTimeout(() => location.reload(), 600);
    } catch (err) {
      showToast(err.message, 'error');
    }
  });
}
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
