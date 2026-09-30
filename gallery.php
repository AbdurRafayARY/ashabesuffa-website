<?php
require_once __DIR__ . '/config/config.php';
$pageTitle = t('gallery.title');
$metaDescription = t('gallery.subtitle');

$albumSlug = $_GET['album'] ?? null;
$albums = get_gallery_albums();
$currentAlbum = null; $images = [];

if ($albumSlug) {
    $stmt = db()->prepare("SELECT * FROM gallery_albums WHERE slug=?");
    $stmt->execute([$albumSlug]);
    $currentAlbum = $stmt->fetch();
    if ($currentAlbum) $images = get_album_images($currentAlbum['id']);
}

require_once __DIR__ . '/includes/header.php';
?>
<main>

<section class="page-header">
  <div class="container">
    <h1><?= e(t('gallery.title')) ?></h1>
    <p><?= e(t('gallery.subtitle')) ?></p>
  </div>
</section>

<section class="section">
  <div class="container">
    <?php if ($currentAlbum): ?>
      <a href="<?= BASE_URL ?>gallery.php" class="btn btn-outline btn-sm mb-3">← <?= e(t('misc.back')) ?></a>
      <h2><?= e(tf($currentAlbum, 'title')) ?></h2>
      <p><?= e(tf($currentAlbum, 'description')) ?></p>
      <div class="gallery-grid mt-3">
        <?php foreach ($images as $img): ?>
          <div class="gallery-item">
            <img src="<?= UPLOAD_URL . e($img['image_path']) ?>" alt="<?= e(tf($img, 'caption')) ?>">
            <div class="overlay"><?= e(tf($img, 'caption')) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="grid grid-3">
        <?php foreach ($albums as $a): ?>
          <a href="?album=<?= e($a['slug']) ?>" class="card" style="text-decoration:none;">
            <img src="<?= UPLOAD_URL . e($a['cover_image'] ?: 'gallery/placeholder.jpg') ?>" alt="<?= e(tf($a,'title')) ?>" style="border-radius:8px;margin-bottom:1rem;">
            <h3><?= e(tf($a, 'title')) ?></h3>
            <p><?= e(excerpt(tf($a, 'description'), 100)) ?></p>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
