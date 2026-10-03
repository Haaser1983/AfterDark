<?php
declare(strict_types=1);

/**
 * Content lives as JSON files in /content. They are the single source of truth:
 * the Studio edits them on the server (and commits them to GitHub), and Claude or
 * a developer can edit them in git (and the FTP deploy ships them).
 */

function read_json(string $file): ?array
{
    if (!is_file($file)) {
        return null;
    }
    $data = json_decode((string) file_get_contents($file), true);
    return is_array($data) ? $data : null;
}

/** Atomically writes JSON and returns the repo-relative path. */
function write_json(string $relPath, array $data): string
{
    $file = ROOT . '/' . $relPath;
    $dir = dirname($file);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
    $tmp = $file . '.tmp-' . bin2hex(random_bytes(4));
    file_put_contents($tmp, $json, LOCK_EX);
    rename($tmp, $file);
    content_cache_clear();
    return $relPath;
}

function content_cache_clear(): void
{
    $GLOBALS['__content_cache'] = [];
}

function cached(string $key, callable $fn): mixed
{
    if (!array_key_exists($key, $GLOBALS['__content_cache'] ?? [])) {
        $GLOBALS['__content_cache'][$key] = $fn();
    }
    return $GLOBALS['__content_cache'][$key];
}

/* ---------- Site ---------- */

function site_defaults(): array
{
    return [
        'author' => [
            'name' => 'Eliza A. Penn',
            'tagline' => '',
            'intro' => '',
            'bio' => '',
            'photo' => null,
            'photo_alt' => '',
        ],
        'links' => [
            'amazon' => '', 'goodreads' => '', 'bookbub' => '', 'facebook' => '',
            'instagram' => '', 'tiktok' => '', 'threads' => '', 'email' => '',
        ],
        'newsletter' => ['enabled' => false, 'heading' => '', 'text' => '', 'url' => '', 'button' => 'Join the newsletter'],
        'arc' => ['enabled' => false, 'heading' => '', 'text' => '', 'url' => '', 'button' => 'Apply for the ARC team'],
        'age_gate' => ['enabled' => false, 'text' => ''],
        'footer_note' => '',
        'needs_review' => [],
    ];
}

function site(): array
{
    return cached('site', function () {
        $data = read_json(CONTENT . '/site.json') ?? [];
        return array_replace_recursive(site_defaults(), $data);
    });
}

/* ---------- Series ---------- */

function series_defaults(): array
{
    return [
        'slug' => '',
        'name' => '',
        'kind' => 'series',      // "series" (read in order) or "universe" (standalones in one world)
        'tagline' => '',
        'description' => '',
        'planned_count' => null,
        'theme' => 'nightfall',
        'order' => 10,
        'visible' => true,
        'needs_review' => [],
    ];
}

function all_series(bool $includeHidden = false): array
{
    $all = cached('series', function () {
        $out = [];
        foreach (glob(CONTENT . '/series/*.json') ?: [] as $file) {
            $data = read_json($file);
            if (!$data) {
                continue;
            }
            $data = array_replace(series_defaults(), $data);
            $data['slug'] = $data['slug'] ?: basename($file, '.json');
            $out[$data['slug']] = $data;
        }
        uasort($out, fn ($a, $b) => [$a['order'], $a['name']] <=> [$b['order'], $b['name']]);
        return $out;
    });
    return $includeHidden ? $all : array_filter($all, fn ($s) => !empty($s['visible']));
}

function get_series(?string $slug, bool $includeHidden = false): ?array
{
    if (!$slug) {
        return null;
    }
    return all_series($includeHidden)[$slug] ?? null;
}

/* ---------- Books ---------- */

const BOOK_STATUSES = [
    'published'   => 'Out now',
    'coming-soon' => 'Coming soon',
    'in-progress' => 'In the works',
];

function book_defaults(): array
{
    return [
        'slug' => '',
        'title' => '',
        'subtitle' => '',
        'series' => '',
        'series_number' => null,
        'series_label' => '',
        'status' => 'in-progress',
        'release_date' => null,
        'visible' => true,
        'featured' => false,
        'order' => 10,
        'tagline' => '',
        'blurb' => '',
        'excerpt' => ['title' => '', 'text' => ''],
        'genres' => [],
        'tropes' => [],
        'heat' => null,
        'heat_label' => '',
        'pov' => '',
        'length' => '',
        'kindle_unlimited' => false,
        'links' => ['amazon' => '', 'goodreads' => '', 'bookbub' => '', 'other' => []],
        'content_warnings' => '',
        'characters' => [],
        'theme' => 'nightfall',
        'accent' => '',
        'cover' => null,
        'cover_alt' => '',
        'needs_review' => [],
    ];
}

function normalize_book(array $data, string $fallbackSlug = ''): array
{
    $book = array_replace(book_defaults(), $data);
    $book['links'] = array_replace(book_defaults()['links'], (array) ($data['links'] ?? []));
    $book['excerpt'] = array_replace(book_defaults()['excerpt'], (array) ($data['excerpt'] ?? []));
    $book['slug'] = $book['slug'] ?: $fallbackSlug;
    if (!isset(BOOK_STATUSES[$book['status']])) {
        $book['status'] = 'in-progress';
    }
    return $book;
}

function all_books(bool $includeHidden = false): array
{
    $all = cached('books', function () {
        $out = [];
        foreach (glob(CONTENT . '/books/*.json') ?: [] as $file) {
            $data = read_json($file);
            if (!$data) {
                continue;
            }
            $book = normalize_book($data, basename($file, '.json'));
            $out[$book['slug']] = $book;
        }
        $seriesOrder = array_flip(array_keys(all_series(true)));
        uasort($out, function ($a, $b) use ($seriesOrder) {
            $sa = $seriesOrder[$a['series']] ?? 999;
            $sb = $seriesOrder[$b['series']] ?? 999;
            return [$sa, $a['series_number'] ?? 99, $a['order'], $a['title']]
               <=> [$sb, $b['series_number'] ?? 99, $b['order'], $b['title']];
        });
        return $out;
    });
    return $includeHidden ? $all : array_filter($all, fn ($b) => !empty($b['visible']));
}

function get_book(string $slug, bool $includeHidden = false): ?array
{
    return all_books($includeHidden)[$slug] ?? null;
}

function books_in_series(string $seriesSlug, bool $includeHidden = false): array
{
    return array_filter(all_books($includeHidden), fn ($b) => $b['series'] === $seriesSlug);
}

function featured_books(): array
{
    $featured = array_filter(all_books(), fn ($b) => !empty($b['featured']));
    uasort($featured, function ($a, $b) {
        $rank = ['published' => 0, 'coming-soon' => 1, 'in-progress' => 2];
        return [$a['order'], $rank[$a['status']]] <=> [$b['order'], $rank[$b['status']]];
    });
    return $featured;
}

/** Label like "Siren Unleashed, book 1" or a custom series_label. */
function series_line(array $book): string
{
    if ($book['series_label'] !== '') {
        return $book['series_label'];
    }
    $series = get_series($book['series'], true);
    if (!$series) {
        return '';
    }
    if ($book['series_number']) {
        return $series['name'] . ', book ' . (int) $book['series_number'];
    }
    return $series['name'];
}

function status_text(array $book): string
{
    $label = BOOK_STATUSES[$book['status']] ?? '';
    if ($book['status'] === 'coming-soon' && $book['release_date']) {
        return 'Coming ' . format_date($book['release_date']);
    }
    return $label;
}
