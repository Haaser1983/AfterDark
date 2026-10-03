<?php
$site = site();
$a = $site['author'];
$featured = array_values($featured);
?>
<section class="hero">
  <h1 class="hero__name"><?= e($a['name']) ?></h1>
  <?php if ($a['tagline']): ?><p class="hero__tag"><?= e($a['tagline']) ?></p><?php endif; ?>
</section>

<?php if ($featured): ?>
<section class="worlds" aria-label="Featured books" data-worlds>
  <?php foreach ($featured as $i => $book): $key = theme_key($book['theme']); ?>
  <article class="world world-<?= e($key) ?><?= $i === 0 ? ' is-active' : '' ?>" style="<?= e(theme_vars($key, (string) $book['accent'])) ?>" data-world>
    <div class="atmos" aria-hidden="true"></div>
    <span class="world__spine" aria-hidden="true"><?= e($book['title']) ?></span>
    <div class="world__body">
      <?php $line = series_line($book); if ($line): ?><p class="world__series"><?= e($line) ?></p><?php endif; ?>
      <h2 class="world__title"><a href="<?= e(book_url($book)) ?>"><?= e($book['title']) ?></a></h2>
      <?php if ($book['tagline']): ?><p class="world__hook"><?= e($book['tagline']) ?></p><?php endif; ?>
      <p class="world__meta">
        <span class="status status--<?= e($book['status']) ?>"><?= e(status_text($book)) ?></span>
        <?= heat_html($book, false) ?>
      </p>
    </div>
    <div class="world__cover"><?= cover_html($book, 'md', $i === 0) ?></div>
  </article>
  <?php endforeach; ?>
</section>
<?php endif; ?>

<section class="shelf wrap">
  <h2 class="section-title">Every book</h2>
  <ul class="shelf__grid">
    <?php foreach ($books as $book): ?>
    <li class="shelf__item">
      <a class="shelf__link" href="<?= e(book_url($book)) ?>">
        <?= cover_html($book, 'sm') ?>
        <span class="shelf__title"><?= e($book['title']) ?></span>
        <span class="shelf__status"><?= e(status_text($book)) ?></span>
      </a>
    </li>
    <?php endforeach; ?>
  </ul>
</section>

<?php if ($a['intro'] || $a['bio']): ?>
<section class="aboutband wrap">
  <div class="aboutband__mark" aria-hidden="true">
    <?php if ($a['photo']): ?>
      <img src="<?= e(media_url($a['photo'])) ?>" alt="" loading="lazy">
    <?php else: ?>
      <span><?= e(implode('', array_map(fn ($w) => mb_substr($w, 0, 1), array_filter(explode(' ', str_replace('.', '', $a['name'])))))) ?></span>
    <?php endif; ?>
  </div>
  <div class="aboutband__text">
    <?= prose($a['intro'] ?: plain_excerpt($a['bio'], 320)) ?>
    <a class="textlink" href="<?= e(url('about')) ?>">More about Eliza</a>
  </div>
</section>
<?php endif; ?>

<?php if ($site['arc']['enabled'] && $site['arc']['url']): ?>
<section class="callout wrap">
  <h2 class="section-title"><?= e($site['arc']['heading'] ?: 'Read the next one early') ?></h2>
  <?= prose($site['arc']['text']) ?>
  <a class="btn" href="<?= e($site['arc']['url']) ?>" rel="noopener"><?= e($site['arc']['button']) ?></a>
</section>
<?php endif; ?>
