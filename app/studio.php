<?php
declare(strict_types=1);

require_once APP . '/lib/forms.php';

/**
 * The Studio: Liz's editor at /studio. Every save writes the content JSON on the server
 * and commits it to GitHub (see lib/sync.php), so git always matches the live site.
 */

function studio_route(array $parts): void
{
    start_session();
    header('X-Robots-Tag: noindex, nofollow');
    header('Cache-Control: no-store');

    $first = $parts[0] ?? '';

    if ($first === 'login') {
        studio_login();
        return;
    }

    require_login();

    if (is_post()) {
        csrf_check();
    }

    switch (true) {
        case $first === '':
            studio_dashboard();
            break;
        case $first === 'logout' && is_post():
            logout();
            redirect('studio/login');
        case $first === 'site':
            studio_site();
            break;
        case $first === 'password':
            studio_password();
            break;
        case $first === 'sync' && is_post():
            [$ok, $msg] = sync_changes([], 'Sync Studio changes');
            flash($msg, $ok ? 'ok' : 'warn');
            redirect('studio');
        case $first === 'book' && ($parts[1] ?? '') === 'new':
            studio_book(null);
            break;
        case $first === 'book' && isset($parts[1]) && ($parts[2] ?? '') === 'delete' && is_post():
            studio_book_delete($parts[1]);
            break;
        case $first === 'book' && isset($parts[1]):
            studio_book($parts[1]);
            break;
        case $first === 'series' && isset($parts[1]):
            studio_series($parts[1]);
            break;
        default:
            http_response_code(404);
            studio_render('studio/notfound', ['title' => 'Not found']);
    }
}

function studio_render(string $view, array $vars = []): void
{
    render($view, $vars, 'studio/layout');
}

/* ---------- Login ---------- */

function studio_login(): void
{
    if (is_logged_in()) {
        redirect('studio');
    }
    $error = '';
    if (is_post()) {
        csrf_check();
        if (login_locked()) {
            $error = 'Too many attempts. Wait 15 minutes, then try again.';
        } elseif (attempt_login((string) ($_POST['username'] ?? ''), (string) ($_POST['password'] ?? ''))) {
            redirect('studio');
        } else {
            $error = 'That username and password don’t match.';
        }
    }
    studio_render('studio/login', ['title' => 'Log in', 'error' => $error, 'configured' => studio_is_configured(), 'bare' => true]);
}

/* ---------- Dashboard ---------- */

function studio_dashboard(): void
{
    studio_render('studio/dashboard', [
        'title' => 'Studio',
        'books' => all_books(true),
        'series' => all_series(true),
        'site' => site(),
        'pending' => pending_paths(),
        'lastSync' => sync_status(),
    ]);
}

/* ---------- Shared form helpers ---------- */

function post_str(string $key, int $max = 5000): string
{
    $v = $_POST[$key] ?? '';
    $v = is_string($v) ? str_replace("\r\n", "\n", trim($v)) : '';
    return mb_substr($v, 0, $max);
}

function post_bool(string $key): bool
{
    return !empty($_POST[$key]);
}

function post_int(string $key, ?int $min = null, ?int $max = null): ?int
{
    $v = trim((string) ($_POST[$key] ?? ''));
    if ($v === '' || !is_numeric($v)) {
        return null;
    }
    $n = (int) $v;
    if ($min !== null) {
        $n = max($min, $n);
    }
    if ($max !== null) {
        $n = min($max, $n);
    }
    return $n;
}

function clean_url(string $u): string
{
    $u = trim($u);
    if ($u === '') {
        return '';
    }
    if (str_starts_with($u, 'mailto:')) {
        return $u;
    }
    if (!preg_match('~^https?://~i', $u)) {
        $u = 'https://' . $u;
    }
    return filter_var($u, FILTER_VALIDATE_URL) ? $u : '';
}

function clean_date(string $d): ?string
{
    $d = trim($d);
    if ($d === '') {
        return null;
    }
    $dt = DateTime::createFromFormat('Y-m-d', $d);
    return $dt && $dt->format('Y-m-d') === $d ? $d : null;
}

/** Fingerprint of a file as it was when the form opened, so stale saves are refused. */
function file_fingerprint(string $relPath): string
{
    $abs = ROOT . '/' . $relPath;
    return is_file($abs) ? sha1_file($abs) : 'new';
}

function stale_check(string $relPath): bool
{
    return hash_equals(file_fingerprint($relPath), (string) ($_POST['_fingerprint'] ?? ''));
}

/** Kept review notes: the checkboxes that are still ticked. */
function kept_review(array $original): array
{
    $keep = array_map('intval', (array) ($_POST['review_keep'] ?? []));
    $out = [];
    foreach ($original as $i => $note) {
        if (in_array($i, $keep, true)) {
            $out[] = $note;
        }
    }
    $new = post_str('review_add', 300);
    if ($new !== '') {
        $out[] = $new;
    }
    return $out;
}

/**
 * Saves an uploaded image to assets/uploads/<dir>/ and returns its repo-relative path,
 * null when nothing was uploaded, or throws with a message for Liz.
 */
function handle_image_upload(string $field, string $dir, string $base): ?string
{
    $f = $_FILES[$field] ?? null;
    if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($f['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('The image didn’t upload. Try a smaller file (under 6 MB).');
    }
    if ($f['size'] > (int) config('uploads.max_bytes')) {
        throw new RuntimeException('That image is too large. Use one under 6 MB.');
    }
    $info = @getimagesize($f['tmp_name']);
    $types = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
    if (!$info || !isset($types[$info[2]])) {
        throw new RuntimeException('Use a JPG, PNG or WebP image.');
    }
    $rel = 'assets/uploads/' . $dir . '/' . $base . '-' . date('YmdHis') . '.' . $types[$info[2]];
    $abs = ROOT . '/' . $rel;
    if (!is_dir(dirname($abs))) {
        mkdir(dirname($abs), 0755, true);
    }
    if (!move_uploaded_file($f['tmp_name'], $abs)) {
        throw new RuntimeException('The image couldn’t be saved on the server. Check that assets/uploads is writable.');
    }
    @chmod($abs, 0644);
    return $rel;
}

function remove_upload(?string $rel): ?string
{
    if (!$rel || !str_starts_with($rel, 'assets/uploads/')) {
        return null;
    }
    $abs = ROOT . '/' . $rel;
    if (is_file($abs)) {
        @unlink($abs);
    }
    return $rel;
}

function finish_save(array $changed, string $message, string $back): never
{
    [$ok, $msg] = sync_changes($changed, $message);
    flash($msg, $ok ? 'ok' : 'warn');
    redirect($back);
}

/* ---------- Books ---------- */

function studio_book(?string $slug): void
{
    $isNew = $slug === null;
    $book = $isNew ? normalize_book([]) : get_book((string) $slug, true);
    if (!$book) {
        http_response_code(404);
        studio_render('studio/notfound', ['title' => 'Not found']);
        return;
    }
    $rel = $isNew ? '' : 'content/books/' . $book['slug'] . '.json';
    $errors = [];

    if (is_post()) {
        $title = post_str('title', 200);
        if ($title === '') {
            $errors[] = 'Give the book a title.';
        }
        if ($isNew) {
            $newSlug = slugify(post_str('slug', 80) ?: $title);
            if (!valid_slug($newSlug)) {
                $errors[] = 'The web address can only use lowercase letters, numbers and dashes.';
            } elseif (get_book($newSlug, true)) {
                $errors[] = 'Another book already uses the web address “' . $newSlug . '”.';
            }
            $book['slug'] = $newSlug;
            $rel = 'content/books/' . $newSlug . '.json';
        } elseif (!stale_check($rel)) {
            $errors[] = 'This book was changed somewhere else (probably a GitHub update) since you opened it. Reload the page to get the latest version, then make your edit again.';
        }

        $series = post_str('series', 80);
        $characters = [];
        foreach ((array) ($_POST['chars'] ?? []) as $c) {
            $name = trim(mb_substr((string) ($c['name'] ?? ''), 0, 120));
            if ($name === '') {
                continue;
            }
            $color = trim((string) ($c['color'] ?? ''));
            if (!in_array($color, ['rainbow', 'iridescent'], true) && !valid_hex($color)) {
                $color = '';
            }
            $characters[] = [
                'name' => $name,
                'role' => trim(mb_substr((string) ($c['role'] ?? ''), 0, 120)),
                'description' => trim(str_replace("\r\n", "\n", mb_substr((string) ($c['description'] ?? ''), 0, 1200))),
                'color' => $color,
                'supporting' => !empty($c['supporting']),
                'spoiler' => !empty($c['spoiler']),
            ];
        }
        $other = [];
        foreach (lines_to_list(post_str('links_other', 2000)) as $line) {
            [$label, $u] = array_pad(array_map('trim', explode('|', $line, 2)), 2, '');
            if ($label !== '' && clean_url($u) !== '') {
                $other[] = ['label' => $label, 'url' => clean_url($u)];
            }
        }
        $heat = trim((string) ($_POST['heat'] ?? ''));

        $updated = array_replace($book, [
            'title' => $title,
            'subtitle' => post_str('subtitle', 200),
            'series' => get_series($series, true) ? $series : '',
            'series_number' => post_int('series_number', 1, 99),
            'series_label' => post_str('series_label', 160),
            'status' => isset(BOOK_STATUSES[post_str('status', 20)]) ? post_str('status', 20) : 'in-progress',
            'release_date' => clean_date(post_str('release_date', 10)),
            'visible' => post_bool('visible'),
            'featured' => post_bool('featured'),
            'order' => post_int('order', 0, 999) ?? 10,
            'tagline' => post_str('tagline', 300),
            'blurb' => post_str('blurb', 8000),
            'excerpt' => ['title' => post_str('excerpt_title', 160), 'text' => post_str('excerpt_text', 20000)],
            'genres' => lines_to_list(post_str('genres', 2000)),
            'tropes' => lines_to_list(post_str('tropes', 3000)),
            'heat' => $heat === '' ? null : max(0, min(5, (int) $heat)),
            'heat_label' => post_str('heat_label', 80),
            'pov' => post_str('pov', 200),
            'length' => post_str('length', 120),
            'kindle_unlimited' => post_bool('kindle_unlimited'),
            'links' => [
                'amazon' => clean_url(post_str('link_amazon', 500)),
                'goodreads' => clean_url(post_str('link_goodreads', 500)),
                'bookbub' => clean_url(post_str('link_bookbub', 500)),
                'other' => $other,
            ],
            'content_warnings' => post_str('content_warnings', 5000),
            'characters' => $characters,
            'theme' => theme_key(post_str('theme', 40)),
            'accent' => valid_hex(post_str('accent', 7)) && post_bool('use_accent') ? post_str('accent', 7) : '',
            'cover_alt' => post_str('cover_alt', 300),
            'needs_review' => kept_review($book['needs_review']),
        ]);

        $changed = [];
        if (!$errors) {
            try {
                $newCover = handle_image_upload('cover', 'covers', $updated['slug']);
                if ($newCover || post_bool('remove_cover')) {
                    if ($old = remove_upload($book['cover'])) {
                        $changed[] = $old;
                    }
                    $updated['cover'] = $newCover;
                    if ($newCover) {
                        $changed[] = $newCover;
                    }
                }
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (!$errors) {
            $changed[] = write_json($rel, $updated);
            finish_save($changed, ($isNew ? 'Add book: ' : 'Update book: ') . $updated['title'], 'studio/book/' . $updated['slug']);
        }
        $book = $updated;
    }

    studio_render('studio/book', [
        'title' => $isNew ? 'Add a book' : $book['title'],
        'book' => $book,
        'isNew' => $isNew,
        'errors' => $errors,
        'fingerprint' => $isNew ? 'new' : file_fingerprint($rel),
    ]);
}

function studio_book_delete(string $slug): void
{
    $book = get_book($slug, true);
    if (!$book) {
        redirect('studio');
    }
    if (post_str('confirm_title', 200) !== $book['title']) {
        flash('To delete, type the title exactly as shown. Nothing was deleted.', 'warn');
        redirect('studio/book/' . $slug);
    }
    $rel = 'content/books/' . $slug . '.json';
    @unlink(ROOT . '/' . $rel);
    content_cache_clear();
    $changed = [$rel];
    if ($c = remove_upload($book['cover'])) {
        $changed[] = $c;
    }
    finish_save($changed, 'Delete book: ' . $book['title'], 'studio');
}

/* ---------- Series ---------- */

function studio_series(string $slug): void
{
    $series = get_series($slug, true);
    if (!$series) {
        http_response_code(404);
        studio_render('studio/notfound', ['title' => 'Not found']);
        return;
    }
    $rel = 'content/series/' . $series['slug'] . '.json';
    $errors = [];
    if (is_post()) {
        if (!stale_check($rel)) {
            $errors[] = 'This series was changed somewhere else since you opened it. Reload the page, then make your edit again.';
        }
        $updated = array_replace($series, [
            'name' => post_str('name', 160) ?: $series['name'],
            'kind' => post_str('kind', 20) === 'universe' ? 'universe' : 'series',
            'tagline' => post_str('tagline', 300),
            'description' => post_str('description', 4000),
            'planned_count' => post_int('planned_count', 1, 50),
            'theme' => theme_key(post_str('theme', 40)),
            'order' => post_int('order', 0, 999) ?? 10,
            'visible' => post_bool('visible'),
            'needs_review' => kept_review($series['needs_review']),
        ]);
        if (!$errors) {
            finish_save([write_json($rel, $updated)], 'Update series: ' . $updated['name'], 'studio/series/' . $slug);
        }
        $series = $updated;
    }
    studio_render('studio/series', [
        'title' => $series['name'],
        'series' => $series,
        'errors' => $errors,
        'fingerprint' => file_fingerprint($rel),
    ]);
}

/* ---------- Site & author ---------- */

function studio_site(): void
{
    $site = site();
    $rel = 'content/site.json';
    $errors = [];
    if (is_post()) {
        if (!stale_check($rel)) {
            $errors[] = 'Site settings were changed somewhere else since you opened them. Reload the page, then make your edit again.';
        }
        $links = [];
        foreach (array_keys(site_defaults()['links']) as $k) {
            $v = post_str('link_' . $k, 500);
            if ($k === 'email') {
                $links[$k] = filter_var($v, FILTER_VALIDATE_EMAIL) ? $v : '';
            } else {
                $links[$k] = clean_url($v);
            }
        }
        $updated = array_replace($site, [
            'site_name' => post_str('site_name', 120) ?: $site['site_name'],
            'author' => array_replace($site['author'], [
                'name' => post_str('name', 120) ?: $site['author']['name'],
                'tagline' => post_str('tagline', 200),
                'intro' => post_str('intro', 1200),
                'bio' => post_str('bio', 8000),
                'photo_alt' => post_str('photo_alt', 300),
            ]),
            'links' => $links,
            'newsletter' => [
                'enabled' => post_bool('newsletter_enabled'),
                'heading' => post_str('newsletter_heading', 160),
                'text' => post_str('newsletter_text', 600),
                'url' => clean_url(post_str('newsletter_url', 500)),
                'button' => post_str('newsletter_button', 60) ?: 'Join the newsletter',
            ],
            'arc' => [
                'enabled' => post_bool('arc_enabled'),
                'heading' => post_str('arc_heading', 160),
                'text' => post_str('arc_text', 1200),
                'url' => clean_url(post_str('arc_url', 500)),
                'button' => post_str('arc_button', 60) ?: 'Apply for the ARC team',
            ],
            'age_gate' => ['enabled' => post_bool('age_gate_enabled'), 'text' => post_str('age_gate_text', 400)],
            'footer_note' => post_str('footer_note', 300),
            'needs_review' => kept_review($site['needs_review']),
        ]);
        $changed = [];
        if (!$errors) {
            try {
                $photo = handle_image_upload('photo', 'author', 'author');
                if ($photo || post_bool('remove_photo')) {
                    if ($old = remove_upload($site['author']['photo'])) {
                        $changed[] = $old;
                    }
                    $updated['author']['photo'] = $photo;
                    if ($photo) {
                        $changed[] = $photo;
                    }
                }
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }
        if (!$errors) {
            $changed[] = write_json($rel, $updated);
            finish_save($changed, 'Update site and author details', 'studio/site');
        }
        $site = $updated;
    }
    studio_render('studio/site', [
        'title' => 'Site & author',
        'site' => $site,
        'errors' => $errors,
        'fingerprint' => file_fingerprint($rel),
    ]);
}

/* ---------- Password ---------- */

function studio_password(): void
{
    $errors = [];
    if (is_post()) {
        $current = (string) ($_POST['current'] ?? '');
        $new = (string) ($_POST['new'] ?? '');
        $confirm = (string) ($_POST['confirm'] ?? '');
        if (!password_verify($current, admin_credentials()['password_hash'])) {
            $errors[] = 'Your current password isn’t right.';
        }
        if (mb_strlen($new) < 10) {
            $errors[] = 'Use at least 10 characters for the new password.';
        }
        if ($new !== $confirm) {
            $errors[] = 'The two new passwords don’t match.';
        }
        if (!$errors) {
            save_admin_password($new);
            session_regenerate_id(true);
            flash('Password changed.');
            redirect('studio');
        }
    }
    studio_render('studio/password', ['title' => 'Change password', 'errors' => $errors]);
}
