<p class="crumbs"><a href="<?= e(url('studio')) ?>">Books</a></p>
<div class="head">
  <h1><?= e($series['name']) ?></h1>
  <a class="textlink" href="<?= e(url('series/' . $series['slug'])) ?>" target="_blank" rel="noopener">View page</a>
</div>
<?= f_errors($errors) ?>
<form method="post" class="editor">
  <?= csrf_field() ?>
  <input type="hidden" name="_fingerprint" value="<?= e($fingerprint) ?>">
  <section class="panel">
    <h2>Series</h2>
    <?= f_text('name', 'Name', $series['name']) ?>
    <div class="grid3">
      <?= f_select('kind', 'Type', $series['kind'], ['series' => 'Series (read in order)', 'universe' => 'Universe (standalones)']) ?>
      <?= f_text('planned_count', 'Books planned', $series['planned_count'], 'Shows “Title to come” slots for the rest.', 'number', ['min' => 1, 'max' => 50]) ?>
      <?= f_text('order', 'Sort order', $series['order'], null, 'number', ['min' => 0, 'max' => 999]) ?>
    </div>
    <?= f_text('tagline', 'Tagline', $series['tagline']) ?>
    <?= f_area('description', 'Description', $series['description'], 'Blank line between paragraphs. *Italics* and **bold** work.', 6) ?>
    <div class="grid2">
      <?= f_select('theme', 'World', $series['theme'], theme_options()) ?>
      <?= f_check('visible', 'Show this series on the site', (bool) $series['visible']) ?>
    </div>
  </section>
  <section class="panel"><?= f_review($series['needs_review']) ?></section>
  <div class="savebar">
    <button class="btn" type="submit">Save changes</button>
    <a class="textlink" href="<?= e(url('studio')) ?>">Cancel</a>
  </div>
</form>
