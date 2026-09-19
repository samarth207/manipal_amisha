<?php
require_once __DIR__ . '/guard.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON body']);
    exit;
}

$id = isset($data['id']) && $data['id'] !== '' ? (int) $data['id'] : null;
$title = trim((string)($data['title'] ?? ''));
$excerpt = trim((string)($data['excerpt'] ?? ''));
$content = (string)($data['content'] ?? '');
$featureImage = trim((string)($data['feature_image'] ?? ''));
$featureImageAlt = trim((string)($data['feature_image_alt'] ?? ''));
$featureImageTitle = trim((string)($data['feature_image_title'] ?? ''));
$metaTitle = trim((string)($data['meta_title'] ?? ''));
$metaDescription = trim((string)($data['meta_description'] ?? ''));
$focusKeyword = trim((string)($data['focus_keyword'] ?? ''));
$primaryKeyword = trim((string)($data['primary_keyword'] ?? ''));
$authorId = isset($data['author_id']) && $data['author_id'] !== '' ? (int) $data['author_id'] : null;
$categoryIds = is_array($data['category_ids'] ?? null) ? array_map('intval', $data['category_ids']) : [];
$tags = is_array($data['tags'] ?? null) ? array_map('trim', $data['tags']) : [];
$status = in_array($data['status'] ?? '', ['draft', 'pending', 'published', 'scheduled'], true) ? $data['status'] : 'draft';
$publishDate = trim((string)($data['publish_date'] ?? ''));
$slugInput = trim((string)($data['slug'] ?? ''));
$ogImage = trim((string)($data['og_image'] ?? ''));
$canonicalUrl = trim((string)($data['canonical_url'] ?? ''));

// Server-side validation matching the editor's required fields.
$errors = [];
if ($title === '') $errors[] = 'Title is required';
if ($featureImage === '') $errors[] = 'Feature image is required';
if ($featureImageAlt === '') $errors[] = 'Feature image alt text is required';
if ($excerpt === '') $errors[] = 'Excerpt is required';
if ($focusKeyword === '') $errors[] = 'Focus keyword is required';
if (empty($categoryIds)) $errors[] = 'At least one category is required';

if ($errors) {
    http_response_code(400);
    echo json_encode(['error' => implode(', ', $errors)]);
    exit;
}

try {
    $pdo = getDbConnection();

    $baseSlug = $slugInput !== '' ? slugify($slugInput) : slugify($title);
    $slug = unique_slug($pdo, $baseSlug, $id);

    if ($status === 'scheduled' && $publishDate !== '') {
        $publish = date('Y-m-d H:i:s', strtotime($publishDate));
    } elseif ($status === 'published') {
        $publish = $publishDate !== '' ? date('Y-m-d H:i:s', strtotime($publishDate)) : date('Y-m-d H:i:s');
    } else {
        $publish = $publishDate !== '' ? date('Y-m-d H:i:s', strtotime($publishDate)) : null;
    }

    $readTime = estimate_read_time($content);
    $toc = json_encode(extract_toc($content));
    $categoryJson = json_encode(array_values($categoryIds));

    if ($metaTitle === '') $metaTitle = $title;

    if ($id) {
        $stmt = $pdo->prepare(
            'UPDATE blogs SET title=:title, slug=:slug, excerpt=:excerpt, content=:content, toc=:toc,
             feature_image=:feature_image, feature_image_alt=:feature_image_alt, feature_image_title=:feature_image_title,
             meta_title=:meta_title, meta_description=:meta_description, focus_keyword=:focus_keyword, primary_keyword=:primary_keyword,
             author_id=:author_id, category_ids=:category_ids, status=:status, publish_date=:publish_date,
             og_image=:og_image, canonical_url=:canonical_url, views=views, read_time=:read_time
             WHERE id=:id'
        );
        $stmt->execute([
            ':title' => $title, ':slug' => $slug, ':excerpt' => $excerpt, ':content' => $content, ':toc' => $toc,
            ':feature_image' => $featureImage, ':feature_image_alt' => $featureImageAlt, ':feature_image_title' => $featureImageTitle,
            ':meta_title' => $metaTitle, ':meta_description' => $metaDescription, ':focus_keyword' => $focusKeyword, ':primary_keyword' => $primaryKeyword,
            ':author_id' => $authorId, ':category_ids' => $categoryJson, ':status' => $status, ':publish_date' => $publish,
            ':og_image' => $ogImage, ':canonical_url' => $canonicalUrl, ':read_time' => $readTime, ':id' => $id,
        ]);
        $blogId = $id;
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO blogs (title, slug, excerpt, content, toc, feature_image, feature_image_alt, feature_image_title,
             meta_title, meta_description, focus_keyword, primary_keyword, author_id, category_ids, status, publish_date,
             og_image, canonical_url, views, read_time, created_at, updated_at)
             VALUES (:title, :slug, :excerpt, :content, :toc, :feature_image, :feature_image_alt, :feature_image_title,
             :meta_title, :meta_description, :focus_keyword, :primary_keyword, :author_id, :category_ids, :status, :publish_date,
             :og_image, :canonical_url, 0, :read_time, NOW(), NOW())'
        );
        $stmt->execute([
            ':title' => $title, ':slug' => $slug, ':excerpt' => $excerpt, ':content' => $content, ':toc' => $toc,
            ':feature_image' => $featureImage, ':feature_image_alt' => $featureImageAlt, ':feature_image_title' => $featureImageTitle,
            ':meta_title' => $metaTitle, ':meta_description' => $metaDescription, ':focus_keyword' => $focusKeyword, ':primary_keyword' => $primaryKeyword,
            ':author_id' => $authorId, ':category_ids' => $categoryJson, ':status' => $status, ':publish_date' => $publish,
            ':og_image' => $ogImage, ':canonical_url' => $canonicalUrl, ':read_time' => $readTime,
        ]);
        $blogId = (int) $pdo->lastInsertId();
    }

    // Sync tags: upsert each tag then rewrite relations for this blog.
    $tagIds = [];
    foreach ($tags as $tagName) {
        $tagName = trim($tagName);
        if ($tagName === '') continue;
        $tagSlug = slugify($tagName);
        $stmt = $pdo->prepare('INSERT INTO blog_tags (name, slug) VALUES (:name, :slug) ON DUPLICATE KEY UPDATE name = VALUES(name)');
        $stmt->execute([':name' => $tagName, ':slug' => $tagSlug]);
        $tagRow = $pdo->prepare('SELECT id FROM blog_tags WHERE slug = :slug');
        $tagRow->execute([':slug' => $tagSlug]);
        $tagIds[] = (int) $tagRow->fetchColumn();
    }

    $pdo->prepare('DELETE FROM blog_tag_relations WHERE blog_id = :id')->execute([':id' => $blogId]);
    if ($tagIds) {
        $stmt = $pdo->prepare('INSERT IGNORE INTO blog_tag_relations (blog_id, tag_id) VALUES (:blog_id, :tag_id)');
        foreach ($tagIds as $tagId) {
            $stmt->execute([':blog_id' => $blogId, ':tag_id' => $tagId]);
        }
    }

    echo json_encode(['message' => 'Blog saved', 'id' => $blogId, 'slug' => $slug, 'status' => $status]);
} catch (Throwable $e) {
    error_log('Blog save failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save blog']);
}
