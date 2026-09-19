<?php
require_once __DIR__ . '/includes/db.php';
$pdo = getDbConnection();

$blogId = isset($_GET['id']) ? (int) $_GET['id'] : null;
$blog = null;
if ($blogId) {
    $stmt = $pdo->prepare('SELECT * FROM blogs WHERE id = :id');
    $stmt->execute([':id' => $blogId]);
    $blog = $stmt->fetch();
    if (!$blog) {
        header('Location: /admin/blogs.php');
        exit;
    }
}

$pageTitle = $blog ? 'Edit Blog' : 'New Blog';
$activePage = $blog ? 'blogs' : 'new-blog';
require __DIR__ . '/includes/header.php';

$categories = $pdo->query('SELECT * FROM blog_categories WHERE is_active = 1 ORDER BY name')->fetchAll();
$authors = $pdo->query('SELECT * FROM blog_authors WHERE is_active = 1 ORDER BY name')->fetchAll();

$selectedCategoryIds = [];
if ($blog && $blog['category_ids']) {
    $selectedCategoryIds = json_decode($blog['category_ids'], true) ?: [];
}

$tags = [];
if ($blog) {
    $tagStmt = $pdo->prepare(
        'SELECT t.name FROM blog_tags t
         JOIN blog_tag_relations r ON r.tag_id = t.id
         WHERE r.blog_id = :id'
    );
    $tagStmt->execute([':id' => $blog['id']]);
    $tags = array_column($tagStmt->fetchAll(), 'name');
}
?>

<div class="editor-action-strip">
  <div class="left">
    <a href="/admin/blogs.php" class="btn btn-outline btn-sm">&larr; All Blogs</a>
    <span id="lastSavedText"><?= $blog ? 'Last saved ' . time_ago($blog['updated_at']) : 'Not saved yet' ?></span>
  </div>
  <div class="actions">
    <button type="button" class="btn btn-muted" id="btnSaveDraft">Save Draft</button>
    <button type="button" class="btn btn-outline" id="btnSubmitReview">Submit for Review</button>
    <button type="button" class="btn btn-primary" id="btnPublish">Publish</button>
  </div>
</div>

<form id="blogForm" onsubmit="return false;">
<input type="hidden" name="id" value="<?= (int)($blog['id'] ?? 0) ?>">
<input type="hidden" name="feature_image" id="featureImageInput" value="<?= e($blog['feature_image'] ?? '') ?>">
<input type="hidden" name="tags" id="tagsInput" value="<?= e(json_encode($tags)) ?>">

<div class="editor-grid">
  <div class="editor-main">

    <div class="card">
      <div class="card-header"><h3>Blog Title &amp; Excerpt</h3></div>
      <div class="card-body">
        <div class="form-group">
          <label>Blog Title *</label>
          <input type="text" id="titleInput" maxlength="70" value="<?= e($blog['title'] ?? '') ?>" placeholder="Enter blog title" required>
          <div class="char-counter" id="titleCounter">0 / 70</div>
        </div>
        <div class="form-group">
          <label>Short Description / Excerpt *</label>
          <textarea id="excerptInput" rows="3" maxlength="250" placeholder="Short summary shown in blog listings" required><?= e($blog['excerpt'] ?? '') ?></textarea>
          <div class="char-counter" id="excerptCounter">0 / 250</div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h3>Feature Image</h3></div>
      <div class="card-body">
        <div class="dropzone" id="dropzone">
          <div>📤 Drag &amp; drop an image here, or click to browse</div>
          <div class="form-hint">JPG, PNG or WebP · Max 5MB · Recommended 1200x628</div>
          <input type="file" id="fileInput" accept="image/jpeg,image/png,image/webp" hidden>
        </div>
        <div class="dropzone-preview" id="imagePreviewWrap" style="<?= empty($blog['feature_image']) ? 'display:none;' : '' ?>">
          <img id="imagePreview" src="<?= e($blog['feature_image'] ?? '') ?>" alt="">
          <button type="button" class="remove-img" id="removeImageBtn">✕</button>
        </div>
        <div class="form-group" style="margin-top:14px;">
          <label>Image Alt Text *</label>
          <input type="text" id="altTextInput" value="<?= e($blog['feature_image_alt'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label>Image Title</label>
          <input type="text" id="imageTitleInput" value="<?= e($blog['feature_image_title'] ?? '') ?>">
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h3>Blog Content</h3>
        <span class="table-slug" id="headingCount">0 headings</span>
      </div>
      <div class="card-body">
        <textarea id="contentEditor"><?= $blog['content'] ?? '' ?></textarea>
      </div>
    </div>

  </div>

  <div class="editor-sidebar">

    <div class="card">
      <div class="card-header"><h3>SEO &amp; Meta</h3></div>
      <div class="card-body">
        <div class="form-group">
          <label>Meta Title *</label>
          <input type="text" id="metaTitleInput" value="<?= e($blog['meta_title'] ?? '') ?>" required>
          <div class="char-counter" id="metaTitleCounter">0 / 60 ideal</div>
          <div class="meter"><div class="meter-fill" id="metaTitleMeter"></div></div>
        </div>
        <div class="form-group">
          <label>Meta Description *</label>
          <textarea id="metaDescInput" rows="3" required><?= e($blog['meta_description'] ?? '') ?></textarea>
          <div class="char-counter" id="metaDescCounter">0 / 200-250 ideal</div>
          <div class="meter"><div class="meter-fill" id="metaDescMeter"></div></div>
        </div>
        <div class="form-group">
          <label>Focus Keyword *</label>
          <input type="text" id="focusKeywordInput" value="<?= e($blog['focus_keyword'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label>Primary Keyword</label>
          <input type="text" id="primaryKeywordInput" value="<?= e($blog['primary_keyword'] ?? '') ?>">
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h3>URL Slug</h3></div>
      <div class="card-body">
        <div class="slug-field">
          <span class="slug-prefix">/blogs/</span>
          <input type="text" id="slugInput" value="<?= e($blog['slug'] ?? '') ?>" placeholder="auto-generated-from-title">
        </div>
        <div class="form-hint">Lowercase, hyphen-separated. Edit manually to override.</div>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h3>Publish Settings</h3></div>
      <div class="card-body">
        <div class="publish-tabs" id="publishTabs">
          <div class="publish-tab" data-status="draft">Draft</div>
          <div class="publish-tab" data-status="pending">Pending</div>
          <div class="publish-tab" data-status="published">Published</div>
          <div class="publish-tab" data-status="scheduled">Scheduled</div>
        </div>
        <input type="hidden" id="statusInput" value="<?= e($blog['status'] ?? 'draft') ?>">
        <div id="scheduleFields" style="display:none; margin-top:14px;">
          <div class="form-group">
            <label>Publish Date</label>
            <input type="date" id="publishDateInput">
          </div>
          <div class="form-group">
            <label>Publish Time</label>
            <input type="time" id="publishTimeInput">
          </div>
        </div>
        <?php if ($blog): ?>
          <div class="form-hint">Last updated: <?= date('M j, Y g:ia', strtotime($blog['updated_at'])) ?></div>
        <?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h3>Categories *</h3>
        <a href="/admin/categories.php" class="table-slug">+ Add new</a>
      </div>
      <div class="card-body">
        <div class="pill-grid" id="categoryPills">
          <?php foreach ($categories as $cat): ?>
            <label class="pill-checkbox <?= in_array($cat['id'], $selectedCategoryIds) ? 'checked' : '' ?>">
              <input type="checkbox" value="<?= (int)$cat['id'] ?>" <?= in_array($cat['id'], $selectedCategoryIds) ? 'checked' : '' ?>>
              <?= e($cat['name']) ?>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h3>Tags</h3></div>
      <div class="card-body">
        <div class="tag-input-box" id="tagInputBox">
          <input type="text" id="tagTextInput" placeholder="Type a tag and press Enter">
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h3>Author</h3>
        <a href="/admin/authors.php" class="table-slug">+ Add new</a>
      </div>
      <div class="card-body">
        <select id="authorSelect">
          <option value="">Select author</option>
          <?php foreach ($authors as $author): ?>
            <option value="<?= (int)$author['id'] ?>" <?= ($blog['author_id'] ?? null) == $author['id'] ? 'selected' : '' ?>><?= e($author['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h3>Table of Contents</h3></div>
      <div class="card-body">
        <div id="tocPreview" class="table-slug">No headings yet. Add H2/H3/H4 in content.</div>
      </div>
    </div>

    <div class="card">
      <div class="card-body" style="display:flex; flex-direction:column; gap:10px;">
        <button type="button" class="btn btn-primary btn-block" id="btnPublishNow">Publish Now</button>
        <button type="button" class="btn btn-outline btn-block" id="btnScheduleBottom">Schedule Post</button>
        <button type="button" class="btn btn-muted btn-block" id="btnSaveDraftBottom">Save as Draft</button>
        <?php if (($blog['status'] ?? null) === 'published'): ?>
          <a class="btn btn-outline btn-block" href="/blogs/<?= e($blog['slug']) ?>" target="_blank">View Live Post</a>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>
</form>

<!-- FAQ block modal -->
<div class="modal-overlay" id="faqModal">
  <div class="modal-card">
    <h3>Add FAQ</h3>
    <div class="form-group"><label>Question</label><input type="text" id="faqQuestion"></div>
    <div class="form-group"><label>Answer</label><textarea id="faqAnswer" rows="3"></textarea></div>
    <div class="modal-actions">
      <button type="button" class="btn btn-outline" id="faqCancel">Cancel</button>
      <button type="button" class="btn btn-primary" id="faqInsert">Insert</button>
    </div>
  </div>
</div>

<!-- Lead form block modal -->
<div class="modal-overlay" id="leadModal">
  <div class="modal-card">
    <h3>Add Lead Capture Form</h3>
    <div class="form-group"><label>Headline</label><input type="text" id="leadHeadline"></div>
    <div class="form-group"><label>Subtext</label><input type="text" id="leadSubtext"></div>
    <div class="form-group"><label>Button Text</label><input type="text" id="leadButtonText" value="Enquire Now"></div>
    <div class="modal-actions">
      <button type="button" class="btn btn-outline" id="leadCancel">Cancel</button>
      <button type="button" class="btn btn-primary" id="leadInsert">Insert</button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
window.BLOG_EDIT_CONTEXT = {
  isEditing: <?= $blog ? 'true' : 'false' ?>,
};
</script>
<script src="/admin/assets/js/blog-editor.js"></script>

<?php require __DIR__ . '/includes/footer.php'; ?>
