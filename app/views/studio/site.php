<?php $a = $site['author']; $l = $site['links']; ?>
<p class="crumbs"><a href="<?= e(url('studio')) ?>">Books</a></p>
<div class="head"><h1>Site &amp; author</h1></div>
<?= f_errors($errors) ?>
<form method="post" enctype="multipart/form-data" class="editor">
  <?= csrf_field() ?>
  <input type="hidden" name="_fingerprint" value="<?= e($fingerprint) ?>">

  <section class="panel">
    <h2>You</h2>
    <?= f_text('site_name', 'Site name', $site['site_name'], 'The big name at the top of the site and in browser tabs.') ?>
    <?= f_text('name', 'Pen name', $a['name'], 'Shown as “Books by …”, on covers and on the About page.') ?>
    <?= f_text('tagline', 'Tagline', $a['tagline'], 'Under your name on the home page and in the footer.') ?>
    <?= f_area('intro', 'Short intro', $a['intro'], 'Two or three sentences for the home page.', 4) ?>
    <?= f_area('bio', 'Full bio', $a['bio'], 'For the About page. Blank line between paragraphs. If empty, the short intro is used.', 10) ?>
    <div class="coveredit">
      <div class="coveredit__preview coveredit__preview--round">
        <?php if ($a['photo']): ?><img src="<?= e(media_url($a['photo'])) ?>" alt=""><?php else: ?><span>No photo</span><?php endif; ?>
      </div>
      <div>
        <div class="field">
          <label for="f-photo"><?= $a['photo'] ? 'Replace photo or avatar' : 'Upload a photo or avatar' ?></label>
          <p class="hint" id="f-photo-hint">Optional. Without one, the site shows your initials. Square images work best.</p>
          <input type="file" id="f-photo" name="photo" accept="image/jpeg,image/png,image/webp" aria-describedby="f-photo-hint">
        </div>
        <?= f_text('photo_alt', 'Photo description', $a['photo_alt'], 'For screen readers.') ?>
        <?php if ($a['photo']): ?><?= f_check('remove_photo', 'Remove the current photo', false) ?><?php endif; ?>
      </div>
    </div>
  </section>

  <section class="panel">
    <h2>Where readers find you</h2>
    <p class="hint">Only filled-in links appear in the footer and on the About page.</p>
    <div class="grid2">
      <?= f_text('link_amazon', 'Amazon author page', $l['amazon'], null, 'url') ?>
      <?= f_text('link_goodreads', 'Goodreads', $l['goodreads'], null, 'url') ?>
      <?= f_text('link_bookbub', 'BookBub', $l['bookbub'], null, 'url') ?>
      <?= f_text('link_facebook', 'Facebook', $l['facebook'], null, 'url') ?>
      <?= f_text('link_instagram', 'Instagram', $l['instagram'], null, 'url') ?>
      <?= f_text('link_tiktok', 'TikTok', $l['tiktok'], null, 'url') ?>
      <?= f_text('link_threads', 'Threads', $l['threads'], null, 'url') ?>
      <?= f_text('link_email', 'Public email', $l['email'], null, 'email') ?>
    </div>
  </section>

  <section class="panel">
    <h2>Newsletter</h2>
    <?= f_check('newsletter_enabled', 'Show the newsletter sign-up', (bool) $site['newsletter']['enabled'], 'Adds a Newsletter link to the top menu and a sign-up block in the footer.') ?>
    <?= f_text('newsletter_url', 'Sign-up page link', $site['newsletter']['url'], 'The sign-up page from your newsletter tool (MailerLite, Kit, BookFunnel…).', 'url') ?>
    <div class="grid2">
      <?= f_text('newsletter_heading', 'Heading', $site['newsletter']['heading'], 'Defaults to “Get new releases first”.') ?>
      <?= f_text('newsletter_button', 'Button text', $site['newsletter']['button']) ?>
    </div>
    <?= f_area('newsletter_text', 'Short pitch', $site['newsletter']['text'], null, 2) ?>
  </section>

  <section class="panel">
    <h2>ARC team</h2>
    <?= f_check('arc_enabled', 'Show the ARC team sign-up on the home page', (bool) $site['arc']['enabled']) ?>
    <?= f_text('arc_url', 'Application link', $site['arc']['url'], 'A form or BookFunnel link.', 'url') ?>
    <div class="grid2">
      <?= f_text('arc_heading', 'Heading', $site['arc']['heading'], 'Defaults to “Read the next one early”.') ?>
      <?= f_text('arc_button', 'Button text', $site['arc']['button']) ?>
    </div>
    <?= f_area('arc_text', 'What readers get, and what you ask of them', $site['arc']['text'], null, 3) ?>
  </section>

  <section class="panel">
    <h2>Adult content</h2>
    <?= f_check('age_gate_enabled', 'Ask visitors to confirm they’re 18+', (bool) $site['age_gate']['enabled'], 'Shown once per browser.') ?>
    <?= f_area('age_gate_text', 'Message', $site['age_gate']['text'], 'Optional. A sensible default is used if empty.', 2) ?>
    <?= f_text('footer_note', 'Footer note', $site['footer_note'], 'Optional small print above the copyright line.') ?>
  </section>

  <section class="panel"><?= f_review($site['needs_review']) ?></section>

  <div class="savebar">
    <button class="btn" type="submit">Save changes</button>
    <a class="textlink" href="<?= e(url('studio')) ?>">Cancel</a>
  </div>
</form>
