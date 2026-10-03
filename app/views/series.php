<?php
/** @var array $series */
/** @var array $books */
$books = array_values($books);
$slots = [];
if ($series['kind'] === 'series' && $series['planned_count']) {
    $taken = array_filter(array_map(fn ($b) => $b['series_number'], $books));
    for ($n = 1; $n <= (int) $series['planned_count']; $n++) {
        if (!in_array($n, $taken, false)) {
            $slots[] = $n;
        }
    }
}
?>
<section class="serieshero">
  <div class="atmos" aria-hidden="true"></div>
  <div class="wrap serieshero__inner">
    <p class="serieshero__kind"><?= $series['kind'] === 'universe' ? 'Standalones in one universe' : ($series['planned_count'] ? 'A series in ' . e((string) $series['planned_count']) . ' books' : 'A series') ?></p>
    <h1 class="serieshero__title"><?= e($series['name']) ?></h1>
    <?php if ($series['tagline']): ?><p class="serieshero__tag"><?= e($series['tagline']) ?></p><?php endif; ?>
    <?php if ($series['description']): ?><div class="serieshero__desc"><?= prose($series['description']) ?></div><?php endif; ?>
  </div>
</section>

<section class="wrap">
  <ol class="readorder<?= $series['kind'] === 'universe' ? ' readorder--loose' : '' ?>">
    <?php foreach ($books as $book): ?>
    <li class="readorder__item">
      <a class="readorder__link" href="<?= e(book_url($book)) ?>">
        <?= cover_html($book, 'md') ?>
        <span class="readorder__text">
          <?php if ($series['kind'] === 'series' && $book['series_number']): ?><span class="readorder__num">Book <?= (int) $book['series_number'] ?></span><?php endif; ?>
          <span class="readorder__title"><?= e($book['title']) ?></span>
          <span class="readorder__status"><?= e(status_text($book)) ?></span>
          <?= heat_html($book, false) ?>
        </span>
      </a>
    </li>
    <?php endforeach; ?>
    <?php foreach ($slots as $n): ?>
    <li class="readorder__item readorder__item--empty">
      <div class="readorder__ghost" aria-hidden="true"></div>
      <span class="readorder__text">
        <span class="readorder__num">Book <?= $n ?></span>
        <span class="readorder__title">Title to come</span>
      </span>
    </li>
    <?php endforeach; ?>
  </ol>
</section>
