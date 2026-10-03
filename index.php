<?php
declare(strict_types=1);

// Let PHP's built-in dev server (php -S localhost:8000 index.php) serve real files directly.
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $blocked = preg_match('~^/(app|content|storage|tools)(/|$)~', (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if (!$blocked && is_file($file)) {
        return false;
    }
}

require __DIR__ . '/app/bootstrap.php';

$path = route_path();
$parts = $path === '' ? [] : explode('/', $path);

if (($parts[0] ?? '') === 'studio') {
    require APP . '/studio.php';
    studio_route(array_slice($parts, 1));
    exit;
}

switch (true) {
    case $path === '':
        render('home', [
            'world' => 'nightfall',
            'featured' => featured_books(),
            'books' => all_books(),
            'isHome' => true,
        ]);
        break;

    case $path === 'books':
        render('books', ['pageTitle' => 'Books', 'world' => 'nightfall', 'nav' => 'books']);
        break;

    case count($parts) === 2 && $parts[0] === 'books':
        $book = valid_slug($parts[1]) ? get_book($parts[1]) : null;
        if (!$book) {
            not_found();
        }
        $series = get_series($book['series']);
        render('book', [
            'book' => $book,
            'series' => $series,
            'pageTitle' => $book['title'],
            'metaDescription' => plain_excerpt($book['tagline'] ?: $book['blurb']),
            'ogImage' => $book['cover'] ? absolute_url($book['cover']) : null,
            'world' => theme_key($book['theme']),
            'accent' => $book['accent'],
            'nav' => 'books',
        ]);
        break;

    case count($parts) === 2 && $parts[0] === 'series':
        $series = valid_slug($parts[1]) ? get_series($parts[1]) : null;
        if (!$series) {
            not_found();
        }
        render('series', [
            'series' => $series,
            'books' => books_in_series($series['slug']),
            'pageTitle' => $series['name'],
            'metaDescription' => plain_excerpt($series['tagline'] ?: $series['description']),
            'world' => theme_key($series['theme']),
            'nav' => 'books',
        ]);
        break;

    case $path === 'about':
        render('about', ['pageTitle' => 'About', 'world' => 'nightfall', 'nav' => 'about']);
        break;

    case $path === 'sitemap.xml':
        header('Content-Type: application/xml; charset=utf-8');
        render('sitemap', [], null);
        break;

    default:
        not_found();
}
