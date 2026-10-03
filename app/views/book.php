<?php
/** @var array $book */
/** @var array|null $series */
$line = series_line($book);
$links = buy_links($book);
$chars = $book['characters'];
$leads = array_filter($chars, fn ($c) => empty($c['spoiler']) && empty($c['supporting']));
$support = array_filter($chars, fn ($c) => empty($c['spoiler']) && !empty($c['supporting']));
$spoilers = array_filter($chars, fn ($c) => !empty($c['spoiler']));
$siblings = $series ? array_filter(books_in_series($series['slug']), fn ($b) => $b['slug'] !== $book['slug']) : [];
$gateLabel = $book['series_number'] ? 'I’ve read ' . $book['title'] : 'I’ve read it';
?>
<section class="bookhero">
  <div class="atmos" aria-hidden="true"></div>
  <div class="bookhero__inner wrap">
    <div class="bookhero__cover"><?= cover_html($book, 'lg', true) ?></div>
    <div class="bookhero__text">
      <?php if ($line): ?>
        <p class="bookhero__series"><?php if ($series): ?><a href="<?= e(url('series/' . $series['slug'])) ?>"><?= e($line) ?></a><?php else: ?><?= e($line) ?><?php endif; ?></p>
      <?php endif; ?>
      <h1 class="bookhero__title"><?= e($book['title']) ?></h1>
      <?php if ($book['subtitle']): ?><p class="bookhero__subtitle"><?= e($book['subtitle']) ?></p><?php endif; ?>
      <?php if ($book['tagline']): ?><p class="bookhero__tagline"><?= e($book['tagline']) ?></p><?php endif; ?>

      <div class="facts">
        <span class="status status--<?= e($book['status']) ?>"><?= e(status_text($book)) ?></span>
        <?php if ($book['kindle_unlimited']): ?><span class="badge">Kindle Unlimited</span><?php endif; ?>
        <?= heat_html($book) ?>
      </div>

      <?php if ($links): ?>
      <div class="buy">
        <?php foreach ($links as $l): ?>
          <a class="btn<?= $l['primary'] ? '' : ' btn--ghost' ?>" href="<?= e($l['url']) ?>" rel="noopener"><?= e($l['label']) ?></a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <?php if (trim($book['content_warnings']) !== ''): ?>
        <a class="cwjump" href="#content-warnings">Read the content warnings</a>
      <?php endif; ?>
    </div>
  </div>
</section>

<div class="bookbody wrap">
  <div class="bookbody__main">
    <?php if (trim($book['blurb']) !== ''): ?>
      <div class="blurb"><?= prose($book['blurb']) ?></div>
    <?php endif; ?>

    <?php if (trim($book['content_warnings']) !== ''): ?>
    <details class="warnings" id="content-warnings">
      <summary>Content warnings</summary>
      <div class="warnings__body"><?= prose($book['content_warnings']) ?></div>
    </details>
    <?php endif; ?>
  </div>

  <aside class="glance" aria-label="At a glance">
    <dl>
      <?php if ($book['genres']): ?><div><dt>Genre</dt><dd><?= e(implode(', ', $book['genres'])) ?></dd></div><?php endif; ?>
      <?php if ($book['heat'] !== null || $book['heat_label']): ?><div><dt>Heat</dt><dd><?= heat_html($book) ?></dd></div><?php endif; ?>
      <?php if ($book['pov']): ?><div><dt>Point of view</dt><dd><?= e($book['pov']) ?></dd></div><?php endif; ?>
      <?php if ($book['length']): ?><div><dt>Length</dt><dd><?= e($book['length']) ?></dd></div><?php endif; ?>
      <?php if ($book['status'] === 'published' && $book['release_date']): ?><div><dt>Released</dt><dd><?= e(format_date($book['release_date'])) ?></dd></div><?php endif; ?>
    </dl>
    <?php if ($book['tropes']): ?>
      <p class="glance__head">Tropes</p>
      <ul class="tropes">
        <?php foreach ($book['tropes'] as $t): ?><li><?= e($t) ?></li><?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </aside>
</div>

<?php if (trim($book['excerpt']['text']) !== ''): ?>
<section class="excerpt wrap">
  <h2 class="section-title"><?= e($book['excerpt']['title'] ?: 'Read a little') ?></h2>
  <div class="excerpt__text"><?= prose($book['excerpt']['text']) ?></div>
</section>
<?php endif; ?>

<?php if ($leads || $support || $spoilers): ?>
<section class="cast wrap" aria-labelledby="cast-title">
  <h2 class="section-title" id="cast-title">The cast</h2>
  <?php if ($leads): ?>
  <ul class="cast__grid">
    <?php foreach ($leads as $c): ?><?php require APP . '/views/partials/character.php'; ?><?php endforeach; ?>
  </ul>
  <?php endif; ?>
  <?php if ($support): ?>
  <ul class="cast__grid cast__grid--small">
    <?php foreach ($support as $c): ?><?php require APP . '/views/partials/character.php'; ?><?php endforeach; ?>
  </ul>
  <?php endif; ?>
  <?php if ($spoilers): ?>
  <div class="spoilers" data-spoilers="<?= e($book['slug']) ?>">
    <div class="spoilers__gate">
      <p>More of the cast is revealed in the book. Open this only if you’ve finished it.</p>
      <button class="btn btn--ghost" type="button" data-spoiler-open aria-expanded="false" aria-controls="spoilers-<?= e($book['slug']) ?>"><?= e($gateLabel) ?>, show spoilers</button>
    </div>
    <ul class="cast__grid spoilers__content" id="spoilers-<?= e($book['slug']) ?>" hidden>
      <?php foreach ($spoilers as $c): ?><?php require APP . '/views/partials/character.php'; ?><?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>
</section>
<?php endif; ?>

<?php if ($siblings): ?>
<section class="more wrap">
  <h2 class="section-title">More from <?= e($series['name']) ?></h2>
  <ul class="shelf__grid">
    <?php foreach ($siblings as $b): ?>
    <li class="shelf__item">
      <a class="shelf__link" href="<?= e(book_url($b)) ?>">
        <?= cover_html($b, 'sm') ?>
        <span class="shelf__title"><?= e($b['title']) ?></span>
        <span class="shelf__status"><?= e(status_text($b)) ?></span>
      </a>
    </li>
    <?php endforeach; ?>
  </ul>
</section>
<?php endif; ?>
