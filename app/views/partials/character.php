<?php /** @var array $c */ $sw = swatch_css($c['color'] ?? ''); ?>
<li class="character">
  <?php if ($sw): ?><span class="character__swatch" style="background: <?= e($sw) ?>" aria-hidden="true"></span><?php endif; ?>
  <p class="character__name"><?= e($c['name'] ?? '') ?></p>
  <?php if (!empty($c['role'])): ?><p class="character__role"><?= e($c['role']) ?></p><?php endif; ?>
  <?php if (!empty($c['description'])): ?><?= prose($c['description'], 'character__desc') ?><?php endif; ?>
</li>
