<?php
require_once __DIR__ . '/config/config.php';
$pageTitle = '404 Not Found';
require_once __DIR__ . '/includes/header.php';
?>
<main>
<section class="section" style="padding:8rem 0;text-align:center;">
  <div class="container">
    <h1 style="font-size:6rem;color:var(--primary);">404</h1>
    <h2><?= e(t('misc.noresults')) ?></h2>
    <p>The page you are looking for does not exist.</p>
    <a href="<?= BASE_URL ?>index.php" class="btn btn-primary mt-3"><?= e(t('nav.home')) ?></a>
  </div>
</section>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
