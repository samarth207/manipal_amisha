<?php
require_once __DIR__ . '/includes/db.php';
$pageTitle = 'Authors';
$activePage = 'authors';
require __DIR__ . '/includes/header.php';

$pdo = getDbConnection();
$authors = $pdo->query('SELECT * FROM blog_authors ORDER BY created_at DESC')->fetchAll();
?>

<div class="two-col">
  <div class="card">
    <div class="card-header"><h3>All Authors</h3></div>
    <div class="card-body" style="padding:0;">
      <?php if ($authors): ?>
      <table>
        <thead><tr><th>Author</th><th>Bio</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($authors as $a): ?>
          <tr>
            <td style="display:flex; align-items:center; gap:10px;">
              <?php if ($a['image']): ?>
                <img src="<?= e($a['image']) ?>" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">
              <?php else: ?>
                <div class="avatar" style="background:#eef0f4;color:#1a2332;width:32px;height:32px;font-size:12px;"><?= e(strtoupper(substr($a['name'], 0, 1))) ?></div>
              <?php endif; ?>
              <?= e($a['name']) ?>
            </td>
            <td class="table-slug"><?= e(mb_strimwidth($a['bio'] ?? '', 0, 60, '...')) ?></td>
            <td><?= $a['is_active'] ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-muted">Inactive</span>' ?></td>
            <td>
              <button class="icon-btn" title="Edit" onclick='editAuthor(<?= json_encode($a) ?>)'>✏️</button>
              <?php if ($a['is_active']): ?>
              <button class="icon-btn danger" title="Deactivate" onclick="deleteAuthor(<?= (int)$a['id'] ?>)">🗑️</button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
        <div class="empty-state"><div class="icon">🧑‍💼</div>No authors yet.</div>
      <?php endif; ?>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h3 id="authorFormTitle">Add Author</h3></div>
    <div class="card-body">
      <form id="authorForm" onsubmit="return false;">
        <input type="hidden" id="authorId">
        <div class="form-group">
          <label>Author Name *</label>
          <input type="text" id="authorName" required>
        </div>
        <div class="form-group">
          <label>Bio</label>
          <textarea id="authorBio" rows="3"></textarea>
        </div>
        <div class="form-group">
          <label>Author Image</label>
          <div class="dropzone" id="authorDropzone">
            <div>📤 Drag &amp; drop or click to upload</div>
            <input type="file" id="authorFileInput" accept="image/jpeg,image/png,image/webp" hidden>
          </div>
          <div class="dropzone-preview" id="authorImagePreviewWrap" style="display:none;">
            <img id="authorImagePreview" src="" alt="" style="max-height:120px;width:auto;">
            <button type="button" class="remove-img" id="authorRemoveImageBtn">✕</button>
          </div>
          <input type="url" id="authorImageUrl" placeholder="or paste image URL" style="margin-top:8px;">
        </div>
        <div class="form-group">
          <label>Author Page URL</label>
          <input type="url" id="authorPageUrl">
        </div>
        <div class="form-group">
          <label><input type="checkbox" id="authorActive" checked style="width:auto;"> Active</label>
        </div>
        <div style="display:flex; gap:10px;">
          <button type="button" class="btn btn-primary" id="authorSaveBtn">Save Author</button>
          <button type="button" class="btn btn-muted" id="authorResetBtn">Reset</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function editAuthor(a) {
  document.getElementById('authorFormTitle').textContent = 'Edit Author';
  document.getElementById('authorId').value = a.id;
  document.getElementById('authorName').value = a.name;
  document.getElementById('authorBio').value = a.bio || '';
  document.getElementById('authorImageUrl').value = a.image || '';
  document.getElementById('authorPageUrl').value = a.page_url || '';
  document.getElementById('authorActive').checked = !!Number(a.is_active);
  if (a.image) {
    document.getElementById('authorImagePreview').src = a.image;
    document.getElementById('authorImagePreviewWrap').style.display = 'block';
  }
}

document.getElementById('authorResetBtn').addEventListener('click', () => {
  document.getElementById('authorFormTitle').textContent = 'Add Author';
  document.getElementById('authorForm').reset();
  document.getElementById('authorId').value = '';
  document.getElementById('authorImagePreviewWrap').style.display = 'none';
});

const authorDropzone = document.getElementById('authorDropzone');
const authorFileInput = document.getElementById('authorFileInput');
authorDropzone.addEventListener('click', () => authorFileInput.click());
authorDropzone.addEventListener('dragover', (e) => { e.preventDefault(); authorDropzone.classList.add('dragover'); });
authorDropzone.addEventListener('dragleave', () => authorDropzone.classList.remove('dragover'));
authorDropzone.addEventListener('drop', (e) => { e.preventDefault(); authorDropzone.classList.remove('dragover'); if (e.dataTransfer.files[0]) uploadAuthorImage(e.dataTransfer.files[0]); });
authorFileInput.addEventListener('change', () => { if (authorFileInput.files[0]) uploadAuthorImage(authorFileInput.files[0]); });
document.getElementById('authorRemoveImageBtn').addEventListener('click', () => {
  document.getElementById('authorImageUrl').value = '';
  document.getElementById('authorImagePreviewWrap').style.display = 'none';
});

async function uploadAuthorImage(file) {
  const formData = new FormData();
  formData.append('file', file);
  try {
    const res = await fetch('/admin/api/upload_image.php', { method: 'POST', body: formData });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Upload failed');
    document.getElementById('authorImageUrl').value = data.url;
    document.getElementById('authorImagePreview').src = data.url;
    document.getElementById('authorImagePreviewWrap').style.display = 'block';
    showToast('Image uploaded', 'success');
  } catch (err) {
    showToast(err.message, 'error');
  }
}

document.getElementById('authorSaveBtn').addEventListener('click', async () => {
  const payload = {
    id: document.getElementById('authorId').value || null,
    name: document.getElementById('authorName').value.trim(),
    bio: document.getElementById('authorBio').value.trim(),
    image: document.getElementById('authorImageUrl').value.trim(),
    page_url: document.getElementById('authorPageUrl').value.trim(),
    is_active: document.getElementById('authorActive').checked,
  };
  if (!payload.name) { showToast('Author name is required', 'error'); return; }
  try {
    await apiRequest('/admin/api/author_save.php', { method: 'POST', body: JSON.stringify(payload) });
    showToast('Author saved', 'success');
    setTimeout(() => location.reload(), 600);
  } catch (err) {
    showToast(err.message, 'error');
  }
});

function deleteAuthor(id) {
  confirmAction('Deactivate author?', 'They will no longer be selectable for new blogs.', async () => {
    try {
      await apiRequest('/admin/api/author_delete.php', { method: 'POST', body: JSON.stringify({ id }) });
      showToast('Author deactivated', 'success');
      setTimeout(() => location.reload(), 600);
    } catch (err) {
      showToast(err.message, 'error');
    }
  });
}
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
