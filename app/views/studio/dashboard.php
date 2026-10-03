<?php
$reviewCount = count($site['needs_review']);
foreach ($books as $b) { $reviewCount += count($b['needs_review']); }
foreach ($series as $s) { $reviewCount += count($s['needs_review']); }
?>
<div class="head">
  <h1>Books</h1>
  <a class="btn" href="<?= e(url('studio/book/new')) ?>">Add a book</a>
</div>

<section class="sync<?= $pending ? ' sync--pending' : '' ?>" aria-label="GitHub sync">
  <?php if (!github_enabled()): ?>
    <p><strong>Saving to the site only.</strong> GitHub sync isn’t set up, so Studio changes won’t reach git. Ask John to add the GitHub token in <code>app/config.local.php</code>.</p>
  <?php elseif ($pending): ?>
    <p><strong><?= count($pending) ?> change<?= count($pending) === 1 ? '' : 's' ?> not synced to GitHub yet.</strong> They’re live on the site. Sync them so the next update from GitHub doesn’t overwrite them.</p>
    <?php if (!empty($lastSync['error'])): ?><p class="hint">Last error: <?= e($lastSync['error']) ?></p><?php endif; ?>
    <form method="post" action="<?= e(url('studio/sync')) ?>"><?= csrf_field() ?><button class="btn" type="submit">Sync now</button></form>
  <?php else: ?>
    <p>Everything is synced to GitHub.<?php if (!empty($lastSync['at'])): ?> Last sync <?= e(date('M j, g:ia', strtotime($lastSync['at']))) ?>.<?php endif; ?></p>
  <?php endif; ?>
</section>

<ul class="rows">
  <?php foreach ($books as $b): ?>
  <li class="row">
    <div class="row__thumb"><?= cover_html($b, 'xs') ?></div>
    <div class="row__main">
      <a class="row__title" href="<?= e(url('studio/book/' . $b['slug'])) ?>"><?= e($b['title']) ?></a>
      <p class="row__meta">
        <?= e(series_line($b) ?: 'No series') ?> · <?= e(BOOK_STATUSES[$b['status']]) ?>
        <?php if (!$b['visible']): ?> · <span class="tag tag--muted">Hidden</span><?php endif; ?>
        <?php if ($b['featured']): ?> · <span class="tag">On the home page</span><?php endif; ?>
      </p>
    </div>
    <div class="row__side">
      <?php if ($b['needs_review']): ?><span class="tag tag--warn"><?= count($b['needs_review']) ?> to review</span><?php endif; ?>
      <a class="textlink" href="<?= e(book_url($b)) ?>" target="_blank" rel="noopener">View</a>
    </div>
  </li>
  <?php endforeach; ?>
</ul>

<div class="cols">
  <section>
    <h2>Series</h2>
    <ul class="simple">
      <?php foreach ($series as $s): ?>
        <li><a href="<?= e(url('studio/series/' . $s['slug'])) ?>"><?= e($s['name']) ?></a>
          <?php if ($s['needs_review']): ?><span class="tag tag--warn"><?= count($s['needs_review']) ?> to review</span><?php endif; ?></li>
      <?php endforeach; ?>
    </ul>
    <p class="hint">New series are added by John or Claude in git, then show up here.</p>
  </section>
  <section>
    <h2>Site &amp; author</h2>
    <p>Your name, tagline, bio, photo, social links, newsletter and ARC sign-up.</p>
    <p><a class="textlink" href="<?= e(url('studio/site')) ?>">Edit site &amp; author</a>
      <?php if ($site['needs_review']): ?> <span class="tag tag--warn"><?= count($site['needs_review']) ?> to review</span><?php endif; ?></p>
  </section>
</div>

<?php if ($reviewCount): ?>
<p class="hint footnote">“To review” marks text that was drafted for you or is still missing. Open an item to see the list.</p>
<?php endif; ?>
