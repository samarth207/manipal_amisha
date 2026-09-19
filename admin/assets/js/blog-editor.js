// New/Edit Blog page behavior: TinyMCE, counters, slug, dropzone, tags, TOC, save.

document.addEventListener('DOMContentLoaded', () => {
  const titleInput = document.getElementById('titleInput');
  const excerptInput = document.getElementById('excerptInput');
  const slugInput = document.getElementById('slugInput');
  const metaTitleInput = document.getElementById('metaTitleInput');
  const metaDescInput = document.getElementById('metaDescInput');
  const statusInput = document.getElementById('statusInput');
  const scheduleFields = document.getElementById('scheduleFields');
  const featureImageInput = document.getElementById('featureImageInput');
  const tagsInput = document.getElementById('tagsInput');

  let slugManuallyEdited = (slugInput.value || '').trim() !== '';
  let metaTitleManuallyEdited = (metaTitleInput.value || '').trim() !== '';

  function slugify(text) {
    return text.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
  }

  function updateCounter(el, current, max, warnAt) {
    el.textContent = `${current} / ${max}`;
    el.classList.toggle('warn', current >= warnAt && current < max);
    el.classList.toggle('over', current > max);
  }

  function updateMeter(fillEl, current, idealMin, idealMax) {
    const pct = Math.min(100, Math.round((current / idealMax) * 100));
    fillEl.style.width = pct + '%';
    fillEl.classList.remove('warn', 'danger');
    if (current > idealMax) fillEl.classList.add('danger');
    else if (current < idealMin) fillEl.classList.add('warn');
  }

  // ---- Title & excerpt counters ----
  const titleCounter = document.getElementById('titleCounter');
  const excerptCounter = document.getElementById('excerptCounter');

  titleInput.addEventListener('input', () => {
    updateCounter(titleCounter, titleInput.value.length, 70, 60);
    if (!slugManuallyEdited) slugInput.value = slugify(titleInput.value);
    if (!metaTitleManuallyEdited) {
      metaTitleInput.value = titleInput.value;
      metaTitleInput.dispatchEvent(new Event('input'));
    }
  });
  excerptInput.addEventListener('input', () => updateCounter(excerptCounter, excerptInput.value.length, 250, 220));
  updateCounter(titleCounter, titleInput.value.length, 70, 60);
  updateCounter(excerptCounter, excerptInput.value.length, 250, 220);

  slugInput.addEventListener('input', () => {
    slugManuallyEdited = slugInput.value.trim() !== '';
    slugInput.value = slugify(slugInput.value);
  });

  // ---- Meta title / description meters ----
  const metaTitleCounter = document.getElementById('metaTitleCounter');
  const metaTitleMeter = document.getElementById('metaTitleMeter');
  const metaDescCounter = document.getElementById('metaDescCounter');
  const metaDescMeter = document.getElementById('metaDescMeter');

  metaTitleInput.addEventListener('input', () => {
    metaTitleManuallyEdited = metaTitleInput.value.trim() !== '';
    metaTitleCounter.textContent = `${metaTitleInput.value.length} / 60 ideal`;
    updateMeter(metaTitleMeter, metaTitleInput.value.length, 30, 60);
  });
  metaDescInput.addEventListener('input', () => {
    metaDescCounter.textContent = `${metaDescInput.value.length} / 200-250 ideal`;
    updateMeter(metaDescMeter, metaDescInput.value.length, 200, 250);
  });
  metaTitleInput.dispatchEvent(new Event('input'));
  metaDescInput.dispatchEvent(new Event('input'));

  // ---- Category pill toggling ----
  document.querySelectorAll('#categoryPills .pill-checkbox').forEach((pill) => {
    const checkbox = pill.querySelector('input');
    checkbox.addEventListener('change', () => pill.classList.toggle('checked', checkbox.checked));
  });

  // ---- Tag chip input ----
  let tags = [];
  try { tags = JSON.parse(tagsInput.value || '[]'); } catch { tags = []; }
  const tagBox = document.getElementById('tagInputBox');
  const tagTextInput = document.getElementById('tagTextInput');

  function renderTags() {
    tagBox.querySelectorAll('.tag-chip').forEach((c) => c.remove());
    tags.forEach((tag, idx) => {
      const chip = document.createElement('span');
      chip.className = 'tag-chip';
      chip.innerHTML = `${tag} <button type="button" data-idx="${idx}">✕</button>`;
      tagBox.insertBefore(chip, tagTextInput);
    });
    tagsInput.value = JSON.stringify(tags);
  }
  tagBox.addEventListener('click', (e) => {
    if (e.target.matches('.tag-chip button')) {
      tags.splice(Number(e.target.dataset.idx), 1);
      renderTags();
    }
  });
  tagTextInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' || e.key === ',') {
      e.preventDefault();
      const val = tagTextInput.value.trim().replace(/,$/, '');
      if (val && !tags.includes(val)) {
        tags.push(val);
        renderTags();
      }
      tagTextInput.value = '';
    }
  });
  renderTags();

  // ---- Feature image dropzone ----
  const dropzone = document.getElementById('dropzone');
  const fileInput = document.getElementById('fileInput');
  const previewWrap = document.getElementById('imagePreviewWrap');
  const previewImg = document.getElementById('imagePreview');
  const removeImageBtn = document.getElementById('removeImageBtn');

  dropzone.addEventListener('click', () => fileInput.click());
  dropzone.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.classList.add('dragover'); });
  dropzone.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));
  dropzone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropzone.classList.remove('dragover');
    if (e.dataTransfer.files[0]) uploadImage(e.dataTransfer.files[0]);
  });
  fileInput.addEventListener('change', () => {
    if (fileInput.files[0]) uploadImage(fileInput.files[0]);
  });
  removeImageBtn.addEventListener('click', () => {
    featureImageInput.value = '';
    previewWrap.style.display = 'none';
    previewImg.src = '';
  });

  async function uploadImage(file) {
    if (file.size > 5 * 1024 * 1024) {
      showToast('Image must be under 5MB', 'error');
      return;
    }
    const formData = new FormData();
    formData.append('file', file);
    try {
      const res = await fetch('/admin/api/upload_image.php', { method: 'POST', body: formData });
      const data = await res.json();
      if (!res.ok) throw new Error(data.error || 'Upload failed');
      featureImageInput.value = data.url;
      previewImg.src = data.url;
      previewWrap.style.display = 'block';
      showToast('Image uploaded', 'success');
    } catch (err) {
      showToast(err.message, 'error');
    }
  }

  // ---- Publish tabs ----
  const publishTabs = document.querySelectorAll('#publishTabs .publish-tab');
  function setStatus(status) {
    statusInput.value = status;
    publishTabs.forEach((t) => t.classList.toggle('active', t.dataset.status === status));
    scheduleFields.style.display = status === 'scheduled' ? 'block' : 'none';
  }
  publishTabs.forEach((tab) => tab.addEventListener('click', () => setStatus(tab.dataset.status)));
  setStatus(statusInput.value || 'draft');

  // ---- TinyMCE ----
  tinymce.init({
    selector: '#contentEditor',
    height: 680,
    menubar: false,
    plugins: 'link image table code preview fullscreen lists advlist',
    toolbar:
      'undo redo | styles | bold italic underline strikethrough | forecolor backcolor | ' +
      'alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | ' +
      'link image table | faqBlock leadFormBlock | code preview fullscreen | removeformat',
    setup(editor) {
      editor.ui.registry.addButton('faqBlock', {
        text: 'FAQ',
        tooltip: 'Insert FAQ block',
        onAction: () => openModal('faqModal'),
      });
      editor.ui.registry.addButton('leadFormBlock', {
        text: 'Lead Form',
        tooltip: 'Insert lead capture form block',
        onAction: () => openModal('leadModal'),
      });
      editor.on('input change SetContent', () => {
        updateHeadingInfo(editor.getContent());
      });
      editor.on('init', () => updateHeadingInfo(editor.getContent()));
      window.__tinyEditor = editor;
    },
  });

  function openModal(id) { document.getElementById(id).classList.add('open'); }
  function closeModal(id) { document.getElementById(id).classList.remove('open'); }

  document.getElementById('faqCancel').addEventListener('click', () => closeModal('faqModal'));
  document.getElementById('faqInsert').addEventListener('click', () => {
    const q = document.getElementById('faqQuestion').value.trim();
    const a = document.getElementById('faqAnswer').value.trim();
    if (!q || !a) { showToast('Question and answer are required', 'error'); return; }
    const html = `<div class="faq-block" style="border:1px solid #e5e7eb;border-radius:10px;padding:16px;margin:16px 0;background:#f9fafb;"><h4 style="margin:0 0 8px;">${q}</h4><p style="margin:0;color:#4b5563;">${a}</p></div>`;
    window.__tinyEditor.insertContent(html);
    document.getElementById('faqQuestion').value = '';
    document.getElementById('faqAnswer').value = '';
    closeModal('faqModal');
  });

  document.getElementById('leadCancel').addEventListener('click', () => closeModal('leadModal'));
  document.getElementById('leadInsert').addEventListener('click', () => {
    const headline = document.getElementById('leadHeadline').value.trim();
    const subtext = document.getElementById('leadSubtext').value.trim();
    const buttonText = document.getElementById('leadButtonText').value.trim() || 'Enquire Now';
    if (!headline) { showToast('Headline is required', 'error'); return; }
    const html = `<div class="lead-form-block" style="border:1px solid #F26522;border-radius:12px;padding:20px;margin:20px 0;background:#fff7f2;text-align:center;"><h3 style="margin:0 0 8px;">${headline}</h3><p style="margin:0 0 14px;color:#4b5563;">${subtext}</p><button type="button" style="background:#F26522;color:#fff;border:none;padding:10px 22px;border-radius:999px;font-weight:600;">${buttonText}</button></div>`;
    window.__tinyEditor.insertContent(html);
    document.getElementById('leadHeadline').value = '';
    document.getElementById('leadSubtext').value = '';
    closeModal('leadModal');
  });

  // ---- TOC + heading count ----
  const tocPreview = document.getElementById('tocPreview');
  const headingCount = document.getElementById('headingCount');

  function updateHeadingInfo(html) {
    const doc = new DOMParser().parseFromString(html, 'text/html');
    const headings = Array.from(doc.querySelectorAll('h2, h3, h4'));
    headingCount.textContent = `${headings.length} heading${headings.length === 1 ? '' : 's'}`;
    if (!headings.length) {
      tocPreview.innerHTML = 'No headings yet. Add H2/H3/H4 in content.';
      return;
    }
    tocPreview.innerHTML = headings
      .map((h) => `<div style="padding-left:${(parseInt(h.tagName[1], 10) - 2) * 12}px; padding:4px 0 4px ${(parseInt(h.tagName[1], 10) - 2) * 12}px;">${h.textContent}</div>`)
      .join('');
  }

  // ---- Save logic ----
  function collectPayload(status) {
    const categoryIds = Array.from(document.querySelectorAll('#categoryPills input:checked')).map((c) => Number(c.value));
    let publishDate = '';
    if (status === 'scheduled') {
      const d = document.getElementById('publishDateInput').value;
      const t = document.getElementById('publishTimeInput').value || '09:00';
      if (d) publishDate = `${d} ${t}:00`;
    }
    return {
      id: document.querySelector('input[name="id"]').value || null,
      title: titleInput.value.trim(),
      excerpt: excerptInput.value.trim(),
      content: window.__tinyEditor ? window.__tinyEditor.getContent() : '',
      feature_image: featureImageInput.value,
      feature_image_alt: document.getElementById('altTextInput').value.trim(),
      feature_image_title: document.getElementById('imageTitleInput').value.trim(),
      meta_title: metaTitleInput.value.trim(),
      meta_description: metaDescInput.value.trim(),
      focus_keyword: document.getElementById('focusKeywordInput').value.trim(),
      primary_keyword: document.getElementById('primaryKeywordInput').value.trim(),
      author_id: document.getElementById('authorSelect').value || null,
      category_ids: categoryIds,
      tags,
      status,
      publish_date: publishDate,
      slug: slugInput.value.trim(),
    };
  }

  function validate(payload) {
    const errors = [];
    if (!payload.title) errors.push('Title is required');
    if (!payload.feature_image) errors.push('Feature image is required');
    if (!payload.feature_image_alt) errors.push('Feature image alt text is required');
    if (!payload.excerpt) errors.push('Excerpt is required');
    if (!payload.focus_keyword) errors.push('Focus keyword is required');
    if (!payload.category_ids.length) errors.push('At least one category is required');
    return errors;
  }

  async function saveBlog(status) {
    const payload = collectPayload(status);
    const errors = validate(payload);
    if (errors.length) {
      showToast(errors[0], 'error');
      return;
    }
    try {
      const data = await apiRequest('/admin/api/blog_save.php', { method: 'POST', body: JSON.stringify(payload) });
      showToast('Blog saved successfully', 'success');
      document.getElementById('lastSavedText').textContent = 'Last saved just now';
      if (!window.BLOG_EDIT_CONTEXT.isEditing) {
        window.location.href = `/admin/blog-edit.php?id=${data.id}`;
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  }

  document.getElementById('btnSaveDraft').addEventListener('click', () => saveBlog('draft'));
  document.getElementById('btnSaveDraftBottom').addEventListener('click', () => saveBlog('draft'));
  document.getElementById('btnSubmitReview').addEventListener('click', () => saveBlog('pending'));
  document.getElementById('btnPublish').addEventListener('click', () => saveBlog(statusInput.value === 'scheduled' ? 'scheduled' : 'published'));
  document.getElementById('btnPublishNow').addEventListener('click', () => { setStatus('published'); saveBlog('published'); });
  document.getElementById('btnScheduleBottom').addEventListener('click', () => {
    setStatus('scheduled');
    showToast('Set the publish date/time, then click Publish in the top bar to schedule.', 'success');
  });
});
