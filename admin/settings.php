<?php
require_once __DIR__ . '/includes/db.php';
$pageTitle = 'Settings';
$activePage = 'settings';
require __DIR__ . '/includes/header.php';
$admin = current_admin();
?>

<div class="two-col">
  <div>
    <div class="card">
      <div class="card-header"><h3>Account Info</h3></div>
      <div class="card-body">
        <div class="form-group"><label>Username</label><input type="text" value="<?= e($admin['username']) ?>" disabled></div>
        <div class="form-group"><label>Role</label><input type="text" value="<?= e(ucfirst($admin['role'] ?? 'editor')) ?>" disabled></div>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h3>Change Password</h3></div>
      <div class="card-body">
        <form id="passwordForm" onsubmit="return false;">
          <div class="form-group"><label>Current Password</label><input type="password" id="currentPassword" required></div>
          <div class="form-group"><label>New Password</label><input type="password" id="newPassword" minlength="8" required></div>
          <div class="form-group"><label>Confirm New Password</label><input type="password" id="confirmPassword" minlength="8" required></div>
          <button type="button" class="btn btn-primary" id="changePasswordBtn">Update Password</button>
        </form>
      </div>
    </div>
  </div>

  <div>
    <div class="card">
      <div class="card-header"><h3>Editor Configuration</h3></div>
      <div class="card-body">
        <p class="form-hint">The blog editor uses the self-hosted TinyMCE community build loaded from a CDN, so no API key is required for the core toolbar (text formatting, links, images, tables, code view, fullscreen).</p>
        <p class="form-hint">If you later want Tiny Cloud premium plugins, add an API key in <code>public/admin/blog-edit.php</code> where the TinyMCE script tag is included.</p>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('changePasswordBtn').addEventListener('click', async () => {
  const payload = {
    current_password: document.getElementById('currentPassword').value,
    new_password: document.getElementById('newPassword').value,
    confirm_password: document.getElementById('confirmPassword').value,
  };
  try {
    await apiRequest('/admin/api/change_password.php', { method: 'POST', body: JSON.stringify(payload) });
    showToast('Password updated', 'success');
    document.getElementById('passwordForm').reset();
  } catch (err) {
    showToast(err.message, 'error');
  }
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
