<?php
require_once __DIR__ . '/config/config.php';

$slug = $_GET['slug'] ?? '';
if (!$slug) redirect(BASE_URL . 'news.php');

$news = get_news_by_slug($slug);
if (!$news) { http_response_code(404); redirect(BASE_URL . '404.php'); }

increment_news_views($news['id']);

$pageTitle = tf($news, 'title');
$metaDescription = excerpt(tf($news, 'excerpt'), 160);
$ogImage = $news['featured_image'] ? UPLOAD_URL . $news['featured_image'] : null;

require_once __DIR__ . '/includes/header.php';
?>
<main>

<section class="page-header">
  <div class="container">
    <h1><?= e(tf($news, 'title')) ?></h1>
    <p><?= e(t('news.published')) ?>: <?= e(format_date($news['published_at'])) ?> · <?= (int)$news['views'] ?> <?= e(t('news.views')) ?></p>
  </div>
</section>

<section class="section">
  <div class="container" style="max-width:900px;">
    <?php if ($news['featured_image']): ?>
      <img src="<?= UPLOAD_URL . e($news['featured_image']) ?>" alt="<?= e(tf($news,'title')) ?>" style="border-radius:12px;margin-bottom:2rem;">
    <?php endif; ?>
    <div style="line-height:1.9;font-size:1.05rem;"><?= nl2br(e(tf($news, 'content'))) ?></div>
    <div class="mt-4">
      <a href="<?= BASE_URL ?>news.php" class="btn btn-outline">← <?= e(t('misc.back')) ?></a>
    </div>
  </div>
</section>

</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
