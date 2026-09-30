<?php
require_once __DIR__ . '/config/config.php';
$pageTitle = t('news.title');
$metaDescription = t('news.subtitle');

$page = max(1, (int)($_GET['page'] ?? 1));
$result = get_news_paginated($page, 9);

require_once __DIR__ . '/includes/header.php';
?>
<main>

<section class="page-header">
  <div class="container">
    <h1><?= e(t('news.title')) ?></h1>
    <p><?= e(t('news.subtitle')) ?></p>
  </div>
</section>

<section class="section">
  <div class="container">
    <?php if (empty($result['items'])): ?>
      <p class="text-center"><?= e(t('misc.noresults')) ?></p>
    <?php else: ?>
      <div class="grid grid-3">
        <?php foreach ($result['items'] as $n): ?>
          <article class="card">
            <span class="news-date"><?= e(format_date($n['published_at'])) ?></span>
            <h3><?= e(tf($n, 'title')) ?></h3>
            <p><?= e(excerpt(tf($n, 'excerpt'), 140)) ?></p>
            <a class="btn btn-outline btn-sm mt-2" href="<?= BASE_URL ?>news-detail.php?slug=<?= e($n['slug']) ?>"><?= e(t('news.readmore')) ?></a>
          </article>
        <?php endforeach; ?>
      </div>

      <?php if ($result['total_pages'] > 1): ?>
      <div class="pagination">
        <?php if ($result['current'] > 1): ?>
          <a href="?page=<?= $result['current'] - 1 ?>">← <?= e(t('misc.prev')) ?></a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $result['total_pages']; $i++): ?>
          <?php if ($i === $result['current']): ?>
            <span class="active"><?= $i ?></span>
          <?php else: ?>
            <a href="?page=<?= $i ?>"><?= $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <?php if ($result['current'] < $result['total_pages']): ?>
          <a href="?page=<?= $result['current'] + 1 ?>"><?= e(t('misc.next')) ?> →</a>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>

</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
