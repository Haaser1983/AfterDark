<?php
$groups = [];
foreach (all_series() as $s) {
    $list = books_in_series($s['slug']);
    if ($list) {
        $groups[] = ['series' => $s, 'books' => $list];
    }
}
$loose = array_filter(all_books(), fn ($b) => !get_series($b['series']));
if ($loose) {
    $groups[] = ['series' => null, 'books' => $loose];
}
?>
<section class="pagehead wrap">
  <h1 class="pagehead__title">Books</h1>
</section>

<?php foreach ($groups as $g): $s = $g['series']; $key = theme_key($s['theme'] ?? 'nightfall'); ?>
<section class="group wrap">
  <div class="group__head">
    <h2 class="group__title">
      <?php if ($s): ?><a href="<?= e(url('series/' . $s['slug'])) ?>"><?= e($s['name']) ?></a><?php else: ?>Standalones<?php endif; ?>
    </h2>
    <?php if ($s && $s['tagline']): ?><p class="group__tag"><?= e($s['tagline']) ?></p><?php endif; ?>
  </div>
  <ul class="booklist">
    <?php foreach ($g['books'] as $book): ?>
    <li class="booklist__item">
      <a class="booklist__cover" href="<?= e(book_url($book)) ?>" tabindex="-1" aria-hidden="true"><?= cover_html($book, 'sm') ?></a>
      <div class="booklist__text">
        <?php $line = series_line($book); if ($line && $s): ?><p class="booklist__series"><?= e($line) ?></p><?php endif; ?>
        <h3 class="booklist__title"><a href="<?= e(book_url($book)) ?>"><?= e($book['title']) ?></a></h3>
        <?php if ($book['tagline']): ?><p class="booklist__tag"><?= e($book['tagline']) ?></p><?php endif; ?>
        <p class="booklist__meta">
          <span class="status status--<?= e($book['status']) ?>"><?= e(status_text($book)) ?></span>
          <?php if ($book['kindle_unlimited']): ?><span class="badge">Kindle Unlimited</span><?php endif; ?>
          <?= heat_html($book, false) ?>
        </p>
      </div>
    </li>
    <?php endforeach; ?>
  </ul>
</section>
<?php endforeach; ?>
