<?php
require_once __DIR__ . '/config/config.php';
$pageTitle = t('nav.about');
$metaDescription = t('about.subtitle');
require_once __DIR__ . '/includes/header.php';
?>
<main>

<section class="page-header">
  <div class="container">
    <h1><?= e(t('about.title')) ?></h1>
    <p><?= e(t('about.subtitle')) ?></p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="about-grid">
      <div>
        <h2><?= e(t('about.story')) ?></h2>
        <p><?= e(t('about.story.text')) ?></p>
        <h3 class="mt-3"><?= e(t('about.vision')) ?></h3>
        <p><?= e(t('about.vision.text')) ?></p>
        <h3 class="mt-3"><?= e(t('about.values')) ?></h3>
        <ul style="display:grid;gap:.5rem;margin-top:.5rem;">
          <li>✅ <?= e(t('about.value1')) ?></li>
          <li>✅ <?= e(t('about.value2')) ?></li>
          <li>✅ <?= e(t('about.value3')) ?></li>
          <li>✅ <?= e(t('about.value4')) ?></li>
          <li>✅ <?= e(t('about.value5')) ?></li>
        </ul>
      </div>
      <div class="about-img">
        <img src="<?= BASE_URL ?>images/about-2.jpg" alt="<?= e(t('about.title')) ?>">
      </div>
    </div>
  </div>
</section>

<section class="section section-alt">
  <div class="container">
    <div class="section-title">
      <h2><?= e(t('about.impact')) ?></h2>
      <div class="underline"></div>
    </div>
    <div class="grid grid-4">
      <div class="stat"><strong>25+</strong><span><?= e(t('stats.years')) ?></span></div>
      <div class="stat"><strong>1,200+</strong><span><?= e(t('stats.students')) ?></span></div>
      <div class="stat"><strong>40+</strong><span><?= e(t('stats.teachers')) ?></span></div>
      <div class="stat"><strong>500+</strong><span><?= e(t('stats.families')) ?></span></div>
    </div>
  </div>
</section>

</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
