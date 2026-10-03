<?php
/** @var string $content */
$site = site();
$author = $site['author']['name'];
$world = theme_key($world ?? 'nightfall');
$accent = $accent ?? '';
$titleTag = isset($pageTitle) ? $pageTitle . ' | ' . $author : $author . ($site['author']['tagline'] ? ' | ' . $site['author']['tagline'] : '');
$desc = $metaDescription ?? plain_excerpt($site['author']['tagline'] ?: $site['author']['intro']);
$nav = $nav ?? '';
$canonical = absolute_url(route_path());
$mode = theme($world)['mode'];
?><!doctype html>
<html lang="en" data-mode="<?= e($mode) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($titleTag) ?></title>
<?php if ($desc): ?><meta name="description" content="<?= e($desc) ?>"><?php endif; ?>
<link rel="canonical" href="<?= e($canonical) ?>">
<meta property="og:type" content="<?= isset($book) ? 'book' : 'website' ?>">
<meta property="og:title" content="<?= e($titleTag) ?>">
<?php if ($desc): ?><meta property="og:description" content="<?= e($desc) ?>"><?php endif; ?>
<meta property="og:url" content="<?= e($canonical) ?>">
<?php if (!empty($ogImage)): ?><meta property="og:image" content="<?= e($ogImage) ?>"><meta name="twitter:card" content="summary_large_image"><?php endif; ?>
<meta name="theme-color" content="<?= e(theme($world)['bg']) ?>">
<link rel="icon" href="<?= e(asset('assets/img/favicon.svg')) ?>" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,opsz,wght@0,6..96,400..900;1,6..96,400..900&family=Hanken+Grotesk:ital,wght@0,400..700;1,400..700&display=swap">
<link rel="stylesheet" href="<?= e(asset('assets/css/site.css')) ?>">
</head>
<body class="world-<?= e($world) ?><?= !empty($isHome) ? ' is-home' : '' ?>" style="<?= e(theme_vars($world, $accent)) ?>">
<a class="skip" href="#main">Skip to content</a>
<header class="masthead">
  <a class="wordmark" href="<?= e(url()) ?>"><?= e($author) ?></a>
  <nav class="nav" aria-label="Main">
    <a href="<?= e(url('books')) ?>"<?= $nav === 'books' ? ' aria-current="page"' : '' ?>>Books</a>
    <a href="<?= e(url('about')) ?>"<?= $nav === 'about' ? ' aria-current="page"' : '' ?>>About</a>
    <?php if ($site['newsletter']['enabled'] && $site['newsletter']['url']): ?>
      <a class="nav__cta" href="<?= e($site['newsletter']['url']) ?>" rel="noopener">Newsletter</a>
    <?php endif; ?>
  </nav>
</header>

<main id="main">
<?= $content ?>
</main>

<footer class="footer">
  <div class="footer__inner">
    <div class="footer__sign">
      <p class="footer__name"><?= e($author) ?></p>
      <?php if ($site['author']['tagline']): ?><p class="footer__tag"><?= e($site['author']['tagline']) ?></p><?php endif; ?>
    </div>
    <?php if ($site['newsletter']['enabled'] && $site['newsletter']['url']): ?>
    <div class="footer__join">
      <p class="footer__head"><?= e($site['newsletter']['heading'] ?: 'Get new releases first') ?></p>
      <?php if ($site['newsletter']['text']): ?><p><?= e($site['newsletter']['text']) ?></p><?php endif; ?>
      <a class="btn" href="<?= e($site['newsletter']['url']) ?>" rel="noopener"><?= e($site['newsletter']['button'] ?: 'Join the newsletter') ?></a>
    </div>
    <?php endif; ?>
    <?php $socials = social_links(); if ($socials): ?>
    <ul class="footer__links" aria-label="Find Eliza elsewhere">
      <?php foreach ($socials as $s): ?><li><a href="<?= e($s['url']) ?>" rel="noopener me"><?= e($s['label']) ?></a></li><?php endforeach; ?>
    </ul>
    <?php endif; ?>
  </div>
  <p class="footer__fine">
    <?php if ($site['footer_note']): ?><?= e($site['footer_note']) ?><br><?php endif; ?>
    &copy; <?= date('Y') ?> <?= e($author) ?>. For adult readers.
  </p>
</footer>

<?php if ($site['age_gate']['enabled']): ?>
<div class="agegate" id="agegate" hidden>
  <div class="agegate__card" role="dialog" aria-modal="true" aria-labelledby="agegate-title">
    <p class="agegate__title" id="agegate-title">This site is for adult readers</p>
    <p><?= e($site['age_gate']['text'] ?: 'These books contain explicit sexual content and dark themes. Please confirm you are 18 or older.') ?></p>
    <div class="agegate__actions">
      <button class="btn" type="button" data-agegate-yes>I'm 18 or older</button>
      <a class="btn btn--ghost" href="https://www.google.com/">Leave</a>
    </div>
  </div>
</div>
<?php endif; ?>
<script src="<?= e(asset('assets/js/site.js')) ?>" defer></script>
</body>
</html>
