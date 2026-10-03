<p class="crumbs"><a href="<?= e(url('studio')) ?>">Books</a></p>
<div class="head"><h1>Change password</h1></div>
<?= f_errors($errors) ?>
<form method="post" class="editor narrow">
  <?= csrf_field() ?>
  <section class="panel">
    <?= f_text('current', 'Current password', '', null, 'password', ['autocomplete' => 'current-password', 'required' => true]) ?>
    <?= f_text('new', 'New password', '', 'At least 10 characters.', 'password', ['autocomplete' => 'new-password', 'required' => true, 'minlength' => 10]) ?>
    <?= f_text('confirm', 'New password again', '', null, 'password', ['autocomplete' => 'new-password', 'required' => true]) ?>
  </section>
  <div class="savebar"><button class="btn" type="submit">Change password</button></div>
</form>
