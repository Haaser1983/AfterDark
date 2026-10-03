<?php
$urls = ['', 'books', 'about'];
foreach (all_series() as $s) {
    $urls[] = 'series/' . $s['slug'];
}
foreach (all_books() as $b) {
    $urls[] = 'books/' . $b['slug'];
}
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $u): ?>
  <url><loc><?= e(absolute_url($u)) ?></loc></url>
<?php endforeach; ?>
</urlset>
