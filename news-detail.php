<?php
/**
 * Ashabesuffa Foundation - Single News Article Page
 * URL: /news-detail.php?slug=your-article-slug
 */

require_once __DIR__ . '/config/config.php';

$slug = clean_input($_GET['slug'] ?? '');
if (!$slug) {
    redirect(BASE_URL . 'news.php');
}

$news = get_news_by_slug($slug);
if (!$news) {
    http_response_code(404);
    redirect(BASE_URL . '404.php');
}

increment_news_views($news['id']);

$pageTitle = tf($news, 'title');
$metaDescription = excerpt(tf($news, 'excerpt'), 160);
$ogImage = $news['featured_image']
    ? UPLOAD_URL . $news['featured_image']
    : BASE_URL . 'images/logo.png';

// Related news (same category, excluding this one)
$related = [];
if (!empty($news['category_id'])) {
    $stmt = db()->prepare("SELECT * FROM news
        WHERE status='published' AND category_id=? AND id<>?
        ORDER BY published_at DESC LIMIT 3");
    $stmt->execute([$news['category_id'], $news['id']]);
    $related = $stmt->fetchAll();
}
// Fallback: latest news if no related
if (empty($related)) {
    $stmt = db()->prepare("SELECT * FROM news
        WHERE status='published' AND id<>?
        ORDER BY published_at DESC LIMIT 3");
    $stmt->execute([$news['id']]);
    $related = $stmt->fetchAll();
}

require_once __DIR__ . '/includes/header.php';
?>

<main>

  <!-- Page Header -->
  <section class="page-header">
    <div class="container">
      <h1><?= e(tf($news, 'title')) ?></h1>
      <p>
        <?= e(t('news.published')) ?>:
        <?= e(format_date($news['published_at'])) ?>
        · <?= (int)$news['views'] ?> <?= e(t('news.views')) ?>
      </p>
    </div>
  </section>

  <!-- Article Body -->
  <section class="section">
    <div class="container" style="max-width:900px;">

      <?php if ($news['featured_image']): ?>
        <img
          src="<?= UPLOAD_URL . e($news['featured_image']) ?>"
          alt="<?= e(tf($news, 'title')) ?>"
          style="border-radius:12px;margin-bottom:2rem;width:100%;height:auto;">
      <?php endif; ?>

      <article style="line-height:1.9;font-size:1.05rem;">
        <?= nl2br(e(tf($news, 'content'))) ?>
      </article>

      <!-- Share buttons -->
      <div class="mt-4" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;">
        <strong><?= e(t('misc.share')) ?>:</strong>
        <?php
          $shareUrl = urlencode(BASE_URL . 'news-detail.php?slug=' . $news['slug']);
          $shareTitle = urlencode(tf($news, 'title'));
        ?>
        <a class="btn btn-outline btn-sm" target="_blank" rel="noopener"
           href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>">Facebook</a>
        <a class="btn btn-outline btn-sm" target="_blank" rel="noopener"
           href="https://twitter.com/intent/tweet?url=<?= $shareUrl ?>&text=<?= $shareTitle ?>">Twitter</a>
        <a class="btn btn-outline btn-sm" target="_blank" rel="noopener"
           href="https://wa.me/?text=<?= $shareTitle ?>%20<?= $shareUrl ?>">WhatsApp</a>
        <button class="btn btn-outline btn-sm" onclick="window.print()"><?= e(t('misc.print')) ?></button>
      </div>

      <div class="mt-4">
        <a href="<?= BASE_URL ?>news.php" class="btn btn-outline">← <?= e(t('misc.back')) ?></a>
      </div>
    </div>
  </section>

  <!-- Related News -->
  <?php if ($related): ?>
    <section class="section section-alt">
      <div class="container">
        <div class="section-title">
          <h2><?= e(t('news.title')) ?></h2>
          <div class="underline"></div>
        </div>
        <div class="grid grid-3">
          <?php foreach ($related as $r): ?>
            <article class="card">
              <span class="news-date"><?= e(format_date($r['published_at'])) ?></span>
              <h3><?= e(tf($r, 'title')) ?></h3>
              <p><?= e(excerpt(tf($r, 'excerpt'), 140)) ?></p>
              <a class="btn btn-outline btn-sm mt-2"
                 href="<?= BASE_URL ?>news-detail.php?slug=<?= e($r['slug']) ?>">
                <?= e(t('news.readmore')) ?>
              </a>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
