<?php
// Copy this file to app/config.local.php on the server (and locally if you want the Studio).
// config.local.php is gitignored and excluded from the FTP deploy, so it is never overwritten.

return [
    'site_url' => 'https://www.afterdarkromance.com',

    'admin' => [
        'username'      => 'liz',
        // Run:  php tools/hash-password.php "her-password"   and paste the result here.
        'password_hash' => '',
    ],

    'github' => [
        // Fine-grained personal access token scoped to Haaser1983/AfterDark only,
        // permission "Contents: Read and write". Lets Studio saves land in git.
        'token'  => '',
        'owner'  => 'Haaser1983',
        'repo'   => 'AfterDark',
        'branch' => 'main',
    ],
];
