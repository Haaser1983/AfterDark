<?php
declare(strict_types=1);

const LOGIN_MAX_ATTEMPTS = 5;
const LOGIN_WINDOW_SECONDS = 900;
const SESSION_IDLE_SECONDS = 60 * 60 * 8;

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_name('epstudio');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => base_path() . '/',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    session_start();
    if (isset($_SESSION['last_seen']) && time() - $_SESSION['last_seen'] > SESSION_IDLE_SECONDS) {
        $_SESSION = [];
        session_regenerate_id(true);
    }
    $_SESSION['last_seen'] = time();
}

/** Password hash: storage/admin.json (set from the Studio) wins over config.local.php. */
function admin_credentials(): array
{
    $stored = read_json(STORAGE . '/admin.json') ?? [];
    return [
        'username' => (string) ($stored['username'] ?? config('admin.username', 'liz')),
        'password_hash' => (string) ($stored['password_hash'] ?? config('admin.password_hash', '')),
    ];
}

function studio_is_configured(): bool
{
    return admin_credentials()['password_hash'] !== '';
}

function is_logged_in(): bool
{
    return !empty($_SESSION['studio_user']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect('studio/login');
    }
}

function client_ip(): string
{
    return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
}

function login_attempts_file(): string
{
    return STORAGE . '/login-attempts.json';
}

function login_locked(): bool
{
    $data = read_json(login_attempts_file()) ?? [];
    $rec = $data[client_ip()] ?? null;
    if (!$rec) {
        return false;
    }
    if (time() - $rec['first'] > LOGIN_WINDOW_SECONDS) {
        return false;
    }
    return $rec['count'] >= LOGIN_MAX_ATTEMPTS;
}

function record_login_failure(): void
{
    $file = login_attempts_file();
    $data = read_json($file) ?? [];
    $now = time();
    foreach ($data as $ip => $rec) {
        if ($now - $rec['first'] > LOGIN_WINDOW_SECONDS) {
            unset($data[$ip]);
        }
    }
    $rec = $data[client_ip()] ?? ['count' => 0, 'first' => $now];
    $rec['count']++;
    $data[client_ip()] = $rec;
    @file_put_contents($file, json_encode($data), LOCK_EX);
}

function clear_login_failures(): void
{
    $file = login_attempts_file();
    $data = read_json($file) ?? [];
    unset($data[client_ip()]);
    @file_put_contents($file, json_encode($data), LOCK_EX);
}

function attempt_login(string $username, string $password): bool
{
    $creds = admin_credentials();
    if ($creds['password_hash'] === '') {
        return false;
    }
    $ok = hash_equals(strtolower($creds['username']), strtolower(trim($username)))
        && password_verify($password, $creds['password_hash']);
    if (!$ok) {
        record_login_failure();
        return false;
    }
    clear_login_failures();
    session_regenerate_id(true);
    $_SESSION['studio_user'] = $creds['username'];
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
    if (password_needs_rehash($creds['password_hash'], PASSWORD_DEFAULT)) {
        save_admin_password($password);
    }
    return true;
}

function save_admin_password(string $password): void
{
    $data = [
        'username' => admin_credentials()['username'],
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'updated' => date('c'),
    ];
    file_put_contents(STORAGE . '/admin.json', json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
}

function logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $sent = (string) ($_POST['_csrf'] ?? '');
    if ($sent === '' || !hash_equals(csrf_token(), $sent)) {
        http_response_code(400);
        exit('Your session expired. Go back, reload the page, and try again.');
    }
}
