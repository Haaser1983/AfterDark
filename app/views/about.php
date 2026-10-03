<?php
$site = site();
$a = $site['author'];
$socials = social_links();
?>
<section class="pagehead wrap">
  <h1 class="pagehead__title">About <?= e($a['name']) ?></h1>
</section>
<div class="about wrap">
  <div class="about__mark">
    <?php if ($a['photo']): ?>
      <img src="<?= e(media_url($a['photo'])) ?>" alt="<?= e($a['photo_alt'] ?: $a['name']) ?>">
    <?php else: ?>
      <span aria-hidden="true"><?= e(implode('', array_map(fn ($w) => mb_substr($w, 0, 1), array_filter(explode(' ', str_replace('.', '', $a['name'])))))) ?></span>
    <?php endif; ?>
  </div>
  <div class="about__text">
    <?= prose($a['bio'] ?: $a['intro']) ?>
    <?php if ($socials): ?>
      <p class="about__find">Find Eliza on
        <?php foreach ($socials as $i => $s): ?><?= $i ? ($i === count($socials) - 1 ? ' and ' : ', ') : ' ' ?><a href="<?= e($s['url']) ?>" rel="noopener me"><?= e($s['label']) ?></a><?php endforeach; ?>.
      </p>
    <?php endif; ?>
  </div>
</div>
