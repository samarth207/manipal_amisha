<?php

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text === '' ? 'post-' . time() : $text;
}

function unique_slug(PDO $pdo, string $baseSlug, ?int $excludeId = null): string
{
    $slug = $baseSlug;
    $i = 1;
    while (true) {
        $sql = 'SELECT id FROM blogs WHERE slug = :slug';
        $params = [':slug' => $slug];
        if ($excludeId) {
            $sql .= ' AND id != :id';
            $params[':id'] = $excludeId;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        if (!$stmt->fetch()) {
            return $slug;
        }
        $i++;
        $slug = $baseSlug . '-' . $i;
    }
}

function estimate_read_time(string $html): int
{
    $text = trim(strip_tags($html));
    if ($text === '') {
        return 0;
    }
    $words = str_word_count($text);
    return max(1, (int) ceil($words / 200));
}

function extract_toc(string $html): array
{
    $toc = [];
    if (preg_match_all('/<h([2-4])[^>]*>(.*?)<\/h\1>/is', $html, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $m) {
            $toc[] = [
                'level' => (int) $m[1],
                'text' => trim(strip_tags($m[2])),
            ];
        }
    }
    return $toc;
}

function status_badge(string $status): string
{
    $map = [
        'draft' => ['Draft', 'badge-muted'],
        'pending' => ['Pending Review', 'badge-warning'],
        'published' => ['Published', 'badge-success'],
        'scheduled' => ['Scheduled', 'badge-info'],
        'deleted' => ['Deleted', 'badge-danger'],
    ];
    [$label, $class] = $map[$status] ?? [ucfirst($status), 'badge-muted'];
    return '<span class="badge ' . $class . '">' . htmlspecialchars($label) . '</span>';
}

function time_ago(?string $datetime): string
{
    if (!$datetime) {
        return '—';
    }
    $ts = strtotime($datetime);
    $diff = time() - $ts;
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 2592000) return floor($diff / 86400) . 'd ago';
    return date('M j, Y', $ts);
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
