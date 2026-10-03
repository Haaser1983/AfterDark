<?php
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
define('APP', __DIR__);
define('CONTENT', ROOT . '/content');
define('STORAGE', ROOT . '/storage');

require APP . '/config.php';
require APP . '/lib/helpers.php';
require APP . '/lib/content.php';
require APP . '/lib/themes.php';
require APP . '/lib/ui.php';
require APP . '/lib/auth.php';
require APP . '/lib/github.php';
require APP . '/lib/sync.php';

if (config('debug')) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

date_default_timezone_set((string) config('timezone', 'America/New_York'));
ensure_storage();
send_security_headers();
