<?php
/** @var string $content */
$flashes = flash() ?? [];
$bare = $bare ?? false;
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title ?? 'Studio') ?> | Studio</title>
<link rel="icon" href="<?= e(asset('assets/img/favicon.svg')) ?>" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,opsz,wght@1,6..96,500&family=Hanken+Grotesk:wght@400..700&display=swap">
<link rel="stylesheet" href="<?= e(asset('assets/css/studio.css')) ?>">
</head>
<body class="<?= $bare ? 'is-bare' : '' ?>">
<?php if (!$bare): ?>
<header class="bar">
  <a class="bar__brand" href="<?= e(url('studio')) ?>"><span>Studio</span></a>
  <nav class="bar__nav" aria-label="Studio">
    <a href="<?= e(url('studio')) ?>">Books</a>
    <a href="<?= e(url('studio/site')) ?>">Site &amp; author</a>
    <a href="<?= e(url('studio/password')) ?>">Password</a>
    <a href="<?= e(url()) ?>" target="_blank" rel="noopener">View site</a>
    <form method="post" action="<?= e(url('studio/logout')) ?>"><?= csrf_field() ?><button class="linkbtn" type="submit">Log out</button></form>
  </nav>
</header>
<?php endif; ?>
<main class="page">
  <?php foreach ($flashes as $f): ?>
    <div class="notice notice--<?= e($f['type']) ?>" role="status"><p><?= e($f['message']) ?></p></div>
  <?php endforeach; ?>
  <?= $content ?>
</main>
<script src="<?= e(asset('assets/js/studio.js')) ?>" defer></script>
</body>
</html>
