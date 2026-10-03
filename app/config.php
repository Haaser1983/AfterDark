<?php
declare(strict_types=1);

/**
 * Configuration. Defaults live here; real secrets go in app/config.local.php,
 * which is never committed and never touched by the FTP deploy.
 * Copy app/config.sample.php to app/config.local.php to start.
 */
function config(?string $key = null, mixed $default = null): mixed
{
    static $cfg = null;
    if ($cfg === null) {
        $cfg = [
            'site_url' => '',            // e.g. https://www.afterdarkromance.com (used for sitemap + share tags)
            'debug'    => false,
            'timezone' => 'America/New_York',
            'admin'    => [
                'username'      => 'liz',
                'password_hash' => '',   // generate with: php tools/hash-password.php
            ],
            'github'   => [
                'token'           => '', // fine-grained token, Contents: read & write on this repo only
                'owner'           => 'Haaser1983',
                'repo'            => 'AfterDark',
                'branch'          => 'main',
                'committer_name'  => 'Eliza A. Penn (Studio)',
                'committer_email' => 'studio@afterdarkromance.com',
                'api'             => 'https://api.github.com',
            ],
            'uploads'  => [
                'max_bytes' => 6 * 1024 * 1024,
            ],
        ];
        $local = APP . '/config.local.php';
        if (is_file($local)) {
            $override = require $local;
            if (is_array($override)) {
                $cfg = array_replace_recursive($cfg, $override);
            }
        }
    }
    if ($key === null) {
        return $cfg;
    }
    $value = $cfg;
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}
