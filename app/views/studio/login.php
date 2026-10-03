<div class="login">
  <p class="login__name"><?= e(site()['site_name'] ?: site()['author']['name']) ?></p>
  <h1 class="login__title">Studio</h1>
  <?php if (!$configured): ?>
    <div class="notice notice--warn">
      <p><strong>The Studio isn’t set up yet.</strong></p>
      <p>Create <code>app/config.local.php</code> on the server from <code>app/config.sample.php</code> and add a password hash. The README explains how.</p>
    </div>
  <?php else: ?>
    <?php if ($error): ?><div class="notice notice--warn" role="alert"><p><?= e($error) ?></p></div><?php endif; ?>
    <form method="post" class="stack">
      <?= csrf_field() ?>
      <?= f_text('username', 'Username', $_POST['username'] ?? '', null, 'text', ['autocomplete' => 'username', 'required' => true, 'autocapitalize' => 'none']) ?>
      <?= f_text('password', 'Password', '', null, 'password', ['autocomplete' => 'current-password', 'required' => true]) ?>
      <button class="btn" type="submit">Log in</button>
    </form>
  <?php endif; ?>
</div>
