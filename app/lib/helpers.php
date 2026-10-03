<?php
declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Base path the site is served from ("" at a domain root, "/sub" in a subfolder). */
function base_path(): string
{
    static $base = null;
    if ($base === null) {
        $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
        $base = ($dir === '/' || $dir === '.') ? '' : rtrim($dir, '/');
    }
    return $base;
}

function url(string $path = ''): string
{
    $path = ltrim($path, '/');
    return base_path() . '/' . $path;
}

function absolute_url(string $path = ''): string
{
    $site = rtrim((string) config('site_url', ''), '/');
    if ($site === '') {
        $scheme = is_https() ? 'https' : 'http';
        $site = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . base_path();
    }
    return $site . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    $file = ROOT . '/' . ltrim($path, '/');
    $v = is_file($file) ? (string) filemtime($file) : '1';
    return url($path) . '?v=' . $v;
}

/** Public URL for a stored upload path like "assets/uploads/covers/x.jpg"; passes full URLs through. */
function media_url(?string $path): string
{
    if (!$path) {
        return '';
    }
    if (preg_match('~^https?://~i', $path)) {
        return $path;
    }
    return url($path);
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443);
}

function route_path(): string
{
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $uri = rawurldecode($uri);
    $base = base_path();
    if ($base !== '' && str_starts_with($uri, $base)) {
        $uri = substr($uri, strlen($base));
    }
    if (str_starts_with($uri, '/index.php')) {
        $uri = substr($uri, strlen('/index.php'));
    }
    return trim($uri, '/');
}

function redirect(string $path, int $code = 303): never
{
    header('Location: ' . url($path), true, $code);
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('~[\'’]~u', '', $text);
    $text = preg_replace('~[^a-z0-9]+~', '-', $text);
    return trim((string) $text, '-');
}

function valid_slug(string $slug): bool
{
    return (bool) preg_match('~^[a-z0-9]+(?:-[a-z0-9]+)*$~', $slug) && strlen($slug) <= 80;
}

/**
 * Tiny, safe text formatter for blurbs and bios:
 * blank line = new paragraph, single newline = line break, **bold**, *italic*.
 */
function prose(?string $text, string $class = ''): string
{
    $text = trim(str_replace("\r\n", "\n", (string) $text));
    if ($text === '') {
        return '';
    }
    $out = '';
    foreach (preg_split("~\n{2,}~", $text) as $para) {
        $html = e(trim($para));
        $html = preg_replace('~\*\*(.+?)\*\*~s', '<strong>$1</strong>', $html);
        $html = preg_replace('~(?<![\w*])\*(?!\s)(.+?)(?<!\s)\*(?![\w*])~s', '<em>$1</em>', $html);
        $html = nl2br($html, false);
        $out .= '<p' . ($class ? ' class="' . e($class) . '"' : '') . '>' . $html . "</p>\n";
    }
    return $out;
}

/** Plain-text excerpt for meta descriptions. */
function plain_excerpt(?string $text, int $len = 160): string
{
    $text = trim(preg_replace('~\s+~', ' ', str_replace(['**', '*'], '', (string) $text)));
    if (mb_strlen($text) <= $len) {
        return $text;
    }
    $cut = mb_substr($text, 0, $len - 1);
    $cut = preg_replace('~\s+\S*$~u', '', $cut);
    return $cut . '…';
}

function lines_to_list(?string $text): array
{
    $items = preg_split('~\r?\n~', (string) $text);
    $items = array_map('trim', $items ?: []);
    return array_values(array_filter($items, fn ($s) => $s !== ''));
}

function format_date(?string $date): string
{
    if (!$date) {
        return '';
    }
    $ts = strtotime($date);
    return $ts ? date('F j, Y', $ts) : '';
}

function render(string $view, array $vars = [], ?string $layout = 'layout'): void
{
    extract($vars, EXTR_SKIP);
    ob_start();
    require APP . '/views/' . $view . '.php';
    $content = ob_get_clean();
    if ($layout === null) {
        echo $content;
        return;
    }
    require APP . '/views/' . $layout . '.php';
}

function not_found(): never
{
    http_response_code(404);
    render('404', ['pageTitle' => 'Page not found', 'world' => 'nightfall']);
    exit;
}

function ensure_storage(): void
{
    if (!is_dir(STORAGE)) {
        @mkdir(STORAGE, 0755, true);
    }
    $deny = STORAGE . '/.htaccess';
    if (is_dir(STORAGE) && !is_file($deny)) {
        @file_put_contents($deny, "Require all denied\nDeny from all\n");
    }
}

function send_security_headers(): void
{
    if (headers_sent()) {
        return;
    }
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Frame-Options: SAMEORIGIN');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
}

function flash(?string $message = null, string $type = 'ok'): ?array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return null;
    }
    if ($message !== null) {
        $_SESSION['flash'][] = ['message' => $message, 'type' => $type];
        return null;
    }
    $all = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $all;
}
