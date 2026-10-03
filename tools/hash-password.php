<?php
// Usage: php tools/hash-password.php "the-password"
// Prints a hash to paste into app/config.local.php as admin.password_hash.
if (PHP_SAPI !== 'cli') {
    exit;
}
$pw = $argv[1] ?? '';
if (strlen($pw) < 10) {
    fwrite(STDERR, "Give a password of at least 10 characters: php tools/hash-password.php \"...\"\n");
    exit(1);
}
echo password_hash($pw, PASSWORD_DEFAULT), PHP_EOL;
