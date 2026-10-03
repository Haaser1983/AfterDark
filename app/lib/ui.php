<?php
declare(strict_types=1);

/** A book cover: the uploaded image, or a typographic cover drawn in the book's world colors. */
function cover_html(array $book, string $size = 'md', bool $eager = false): string
{
    $vars = theme_vars(theme_key($book['theme']), (string) $book['accent']);
    $cls = 'cover cover--' . e($size) . ' world-' . e(theme_key($book['theme']));
    if (!empty($book['cover'])) {
        $alt = $book['cover_alt'] ?: ('Cover of ' . $book['title']);
        return '<div class="' . $cls . '" style="' . e($vars) . '"><img src="' . e(media_url($book['cover'])) . '" alt="' . e($alt) . '"'
            . ($eager ? '' : ' loading="lazy"') . ' decoding="async"></div>';
    }
    $series = get_series($book['series'], true);
    $top = $series ? $series['name'] : '';
    return '<div class="' . $cls . ' cover--type" style="' . e($vars) . '" role="img" aria-label="' . e($book['title'] . ' by ' . site()['author']['name']) . '">'
        . ($top !== '' ? '<span class="cover__series">' . e($top) . '</span>' : '')
        . '<span class="cover__title">' . e($book['title']) . '</span>'
        . '<span class="cover__author">' . e(site()['author']['name']) . '</span>'
        . '</div>';
}

function flame_svg(bool $on): string
{
    return '<svg class="flame' . ($on ? ' is-on' : '') . '" viewBox="0 0 16 20" aria-hidden="true" focusable="false">'
        . '<path d="M8.6 1c.4 3.1 4.9 5.3 4.9 10.3A5.6 5.6 0 0 1 8 17a5.6 5.6 0 0 1-5.5-5.7c0-2.6 1.5-4.4 2.9-5.6-.1 1.8.6 3 1.7 3.5C6.6 6.4 7.4 3.6 8.6 1Z"/></svg>';
}

function heat_html(array $book, bool $withLabel = true): string
{
    $heat = $book['heat'];
    $label = trim((string) $book['heat_label']);
    if ($heat === null || $heat === '') {
        return $label !== '' ? '<span class="heat"><span class="heat__label">' . e($label) . '</span></span>' : '';
    }
    $n = max(0, min(5, (int) $heat));
    $icons = '';
    for ($i = 1; $i <= 5; $i++) {
        $icons .= flame_svg($i <= $n);
    }
    return '<span class="heat" role="img" aria-label="Heat level ' . $n . ' of 5' . ($label ? ', ' . e($label) : '') . '">'
        . '<span class="heat__icons">' . $icons . '</span>'
        . ($withLabel && $label !== '' ? '<span class="heat__label" aria-hidden="true">' . e($label) . '</span>' : '')
        . '</span>';
}

/** Retail links for a book, falling back to search links for published titles with no link yet. */
function buy_links(array $book): array
{
    $author = site()['author']['name'];
    $links = [];
    $q = rawurlencode($book['title'] . ' ' . $author);
    if ($book['links']['amazon']) {
        $links[] = ['label' => $book['kindle_unlimited'] ? 'Read it on Kindle Unlimited' : 'Buy on Amazon', 'url' => $book['links']['amazon'], 'primary' => true];
    } elseif ($book['status'] === 'published') {
        $links[] = ['label' => 'Find it on Amazon', 'url' => 'https://www.amazon.com/s?k=' . $q . '&i=digital-text', 'primary' => true];
    }
    if ($book['links']['goodreads']) {
        $links[] = ['label' => 'Add on Goodreads', 'url' => $book['links']['goodreads'], 'primary' => false];
    } elseif ($book['status'] === 'published') {
        $links[] = ['label' => 'Find it on Goodreads', 'url' => 'https://www.goodreads.com/search?q=' . $q, 'primary' => false];
    }
    if ($book['links']['bookbub']) {
        $links[] = ['label' => 'Follow on BookBub', 'url' => $book['links']['bookbub'], 'primary' => false];
    }
    foreach ((array) $book['links']['other'] as $other) {
        if (!empty($other['url']) && !empty($other['label'])) {
            $links[] = ['label' => $other['label'], 'url' => $other['url'], 'primary' => false];
        }
    }
    return $links;
}

/** CSS background for a character swatch: a hex, or the keywords "rainbow" / "iridescent". */
function swatch_css(?string $color): string
{
    $color = trim((string) $color);
    if ($color === 'rainbow') {
        return 'conic-gradient(from 200deg, #ff4f9a, #ffb23f, #f7f04a, #4fe39a, #3fb6ff, #a46bff, #ff4f9a)';
    }
    if ($color === 'iridescent') {
        return 'linear-gradient(135deg, #d9dde3, #a7b6d8 30%, #c7a5d9 50%, #9fd6c8 70%, #e4e6ea)';
    }
    return valid_hex($color) ? $color : '';
}

function social_links(): array
{
    $l = site()['links'];
    $map = [
        'amazon' => 'Amazon', 'goodreads' => 'Goodreads', 'bookbub' => 'BookBub', 'facebook' => 'Facebook',
        'instagram' => 'Instagram', 'tiktok' => 'TikTok', 'threads' => 'Threads',
    ];
    $out = [];
    foreach ($map as $key => $label) {
        if (!empty($l[$key])) {
            $out[] = ['label' => $label, 'url' => $l[$key]];
        }
    }
    if (!empty($l['email'])) {
        $out[] = ['label' => 'Email', 'url' => 'mailto:' . $l['email']];
    }
    return $out;
}

function book_url(array $book): string
{
    return url('books/' . $book['slug']);
}
