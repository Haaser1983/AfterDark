<?php
/** @var array $book */
$seriesOpts = ['' => 'No series'];
foreach (all_series(true) as $s) { $seriesOpts[$s['slug']] = $s['name']; }
$heatOpts = ['' => 'Not rated', '0' => '0', '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5'];
$otherLinks = implode("\n", array_map(fn ($o) => $o['label'] . ' | ' . $o['url'], (array) $book['links']['other']));
$action = $isNew ? url('studio/book/new') : url('studio/book/' . $book['slug']);
$charRow = function (int $i, array $c): string {
    $color = (string) ($c['color'] ?? '');
    $isHex = valid_hex($color);
    ob_start(); ?>
    <fieldset class="char" data-char>
      <legend>Character</legend>
      <div class="grid2">
        <?= f_text("chars[$i][name]", 'Name', $c['name'] ?? '') ?>
        <?= f_text("chars[$i][role]", 'Role', $c['role'] ?? '', 'Short, like “Alpha” or “Her best friend”.') ?>
      </div>
      <?= f_area("chars[$i][description]", 'Description', $c['description'] ?? '', null, 3) ?>
      <div class="grid3">
        <div class="field">
          <label for="<?= f_id("chars[$i][color]") ?>">Color</label>
          <p class="hint" id="<?= f_id("chars[$i][color]") ?>-hint">A hex like #B3122E, or the words rainbow or iridescent.</p>
          <div class="colorpair">
            <input type="color" value="<?= e($isHex ? $color : '#888888') ?>" data-color-picker aria-label="Pick a color">
            <input type="text" id="<?= f_id("chars[$i][color]") ?>" name="chars[<?= $i ?>][color]" value="<?= e($color) ?>" aria-describedby="<?= f_id("chars[$i][color]") ?>-hint" data-color-text>
          </div>
        </div>
        <?= f_check("chars[$i][supporting]", 'Supporting character', !empty($c['supporting']), 'Shown smaller, below the leads.') ?>
        <?= f_check("chars[$i][spoiler]", 'Spoiler', !empty($c['spoiler']), 'Hidden until a reader clicks “show spoilers”.') ?>
      </div>
      <button class="linkbtn linkbtn--danger" type="button" data-remove-char>Remove this character</button>
    </fieldset>
    <?php return (string) ob_get_clean();
};
?>
<p class="crumbs"><a href="<?= e(url('studio')) ?>">Books</a></p>
<div class="head">
  <h1><?= $isNew ? 'Add a book' : e($book['title']) ?></h1>
  <?php if (!$isNew && $book['visible']): ?><a class="textlink" href="<?= e(book_url($book)) ?>" target="_blank" rel="noopener">View page</a><?php endif; ?>
</div>

<?= f_errors($errors) ?>

<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" class="editor">
  <?= csrf_field() ?>
  <input type="hidden" name="_fingerprint" value="<?= e($fingerprint) ?>">

  <section class="panel">
    <h2>Basics</h2>
    <?= f_text('title', 'Title', $book['title'], null, 'text', ['required' => true, 'data-title' => true]) ?>
    <?php if ($isNew): ?>
      <?= f_text('slug', 'Web address', $book['slug'], 'The end of the page link: /books/<strong data-slug-preview>' . e($book['slug'] ?: 'your-title') . '</strong>. Filled in from the title; it can’t change later.', 'text', ['data-slug' => true, 'pattern' => '[a-z0-9\-]*']) ?>
    <?php else: ?>
      <p class="hint">Page link: /books/<?= e($book['slug']) ?></p>
    <?php endif; ?>
    <?= f_text('subtitle', 'Subtitle', $book['subtitle'], 'Optional. Leave empty if the series line already says it.') ?>
    <div class="grid3">
      <?= f_select('series', 'Series', $book['series'], $seriesOpts) ?>
      <?= f_text('series_number', 'Book number', $book['series_number'], null, 'number', ['min' => 1, 'max' => 99]) ?>
      <?= f_text('series_label', 'Custom series line', $book['series_label'], 'Optional, e.g. “An IRA standalone”.') ?>
    </div>
    <div class="grid3">
      <?= f_select('status', 'Status', $book['status'], BOOK_STATUSES) ?>
      <?= f_text('release_date', 'Release date', $book['release_date'], 'Shown as “Coming …” or “Released …”.', 'date') ?>
      <?= f_text('order', 'Sort order', $book['order'], 'Lower numbers come first on the home page.', 'number', ['min' => 0, 'max' => 999]) ?>
    </div>
    <div class="grid2">
      <?= f_check('visible', 'Show this book on the site', (bool) $book['visible']) ?>
      <?= f_check('featured', 'Feature it on the home page', (bool) $book['featured']) ?>
    </div>
  </section>

  <section class="panel">
    <h2>The story</h2>
    <?= f_text('tagline', 'Tagline', $book['tagline'], 'One line. Shown under the title and on the home page.') ?>
    <?= f_area('blurb', 'Blurb', $book['blurb'], 'Leave a blank line between paragraphs. Use *asterisks* for italics and **double** for bold.', 12) ?>
    <?= f_text('excerpt_title', 'Excerpt heading', $book['excerpt']['title'], 'Optional. Defaults to “Read a little”.') ?>
    <?= f_area('excerpt_text', 'Excerpt', $book['excerpt']['text'], 'Optional teaser from the book. Leave empty to hide the section.', 8) ?>
  </section>

  <section class="panel">
    <h2>For readers</h2>
    <div class="grid2">
      <?= f_area('genres', 'Genres', implode("\n", $book['genres']), 'One per line.', 4) ?>
      <?= f_area('tropes', 'Tropes', implode("\n", $book['tropes']), 'One per line. Shown as tags.', 8) ?>
    </div>
    <div class="grid3">
      <?= f_select('heat', 'Heat (flames out of 5)', $book['heat'] === null ? '' : (string) $book['heat'], $heatOpts) ?>
      <?= f_text('heat_label', 'Heat words', $book['heat_label'], 'Optional, e.g. “Explicit, high spice”.') ?>
      <?= f_check('kindle_unlimited', 'In Kindle Unlimited', (bool) $book['kindle_unlimited']) ?>
    </div>
    <div class="grid2">
      <?= f_text('pov', 'Point of view', $book['pov']) ?>
      <?= f_text('length', 'Length', $book['length'], 'e.g. “About 90,000 words”.') ?>
    </div>
    <?= f_area('content_warnings', 'Content warnings', $book['content_warnings'], 'Shown in a “Content warnings” section readers can open. Leave empty to hide it.', 6) ?>
  </section>

  <section class="panel">
    <h2>Links</h2>
    <?= f_text('link_amazon', 'Amazon', $book['links']['amazon'], 'Until this is filled in, published books link to an Amazon search for the title.', 'url') ?>
    <?= f_text('link_goodreads', 'Goodreads', $book['links']['goodreads'], null, 'url') ?>
    <?= f_text('link_bookbub', 'BookBub', $book['links']['bookbub'], null, 'url') ?>
    <?= f_area('links_other', 'Other links', $otherLinks, 'One per line as: Button text | https://link', 3) ?>
  </section>

  <section class="panel">
    <h2>Cover</h2>
    <div class="coveredit">
      <div class="coveredit__preview"><?= cover_html($book, 'sm') ?></div>
      <div>
        <div class="field">
          <label for="f-cover"><?= $book['cover'] ? 'Replace the cover' : 'Upload the cover' ?></label>
          <p class="hint" id="f-cover-hint">JPG, PNG or WebP, under 6 MB. A tall 2:3 image works best. Without one, the site draws a typographic cover.</p>
          <input type="file" id="f-cover" name="cover" accept="image/jpeg,image/png,image/webp" aria-describedby="f-cover-hint">
        </div>
        <?= f_text('cover_alt', 'Cover description', $book['cover_alt'], 'For screen readers. Defaults to “Cover of [title]”.') ?>
        <?php if ($book['cover']): ?><?= f_check('remove_cover', 'Remove the current cover', false) ?><?php endif; ?>
      </div>
    </div>
  </section>

  <section class="panel">
    <h2>The cast</h2>
    <p class="hint">Leads appear first, supporting characters smaller. Never put spoilers in a character that isn’t marked Spoiler.</p>
    <div class="chars" data-chars data-next="<?= count($book['characters']) ?>">
      <?php foreach (array_values($book['characters']) as $i => $c): ?><?= $charRow($i, $c) ?><?php endforeach; ?>
    </div>
    <template data-char-template><?= $charRow(999, []) ?></template>
    <button class="btn btn--ghost" type="button" data-add-char>Add a character</button>
  </section>

  <section class="panel">
    <h2>Look</h2>
    <div class="grid2">
      <?= f_select('theme', 'World', $book['theme'], theme_options(), 'Sets the colors and background of this book’s page and its typographic cover.') ?>
      <div class="field">
        <span class="label">Accent color</span>
        <p class="hint">Optional override for buttons and highlights.</p>
        <div class="colorpair">
          <input type="color" name="accent" value="<?= e(valid_hex($book['accent']) ? $book['accent'] : theme(theme_key($book['theme']))['accent']) ?>" aria-label="Accent color">
          <?= f_check('use_accent', 'Use this accent', valid_hex($book['accent'])) ?>
        </div>
      </div>
    </div>
  </section>

  <section class="panel">
    <?= f_review($book['needs_review']) ?>
  </section>

  <div class="savebar">
    <button class="btn" type="submit"><?= $isNew ? 'Add book' : 'Save changes' ?></button>
    <a class="textlink" href="<?= e(url('studio')) ?>">Cancel</a>
  </div>
</form>

<?php if (!$isNew): ?>
<details class="danger">
  <summary>Delete this book</summary>
  <form method="post" action="<?= e(url('studio/book/' . $book['slug'] . '/delete')) ?>" class="stack">
    <?= csrf_field() ?>
    <p>This removes the page and its cover from the site and from GitHub. To hide it for now instead, untick “Show this book on the site”.</p>
    <?= f_text('confirm_title', 'Type the title to confirm: ' . $book['title'], '', null, 'text', ['autocomplete' => 'off']) ?>
    <button class="btn btn--danger" type="submit">Delete book</button>
  </form>
</details>
<?php endif; ?>
