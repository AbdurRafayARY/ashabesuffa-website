<?php
require_once __DIR__ . '/config/config.php';

$pageTitle = t('nav.home');
$metaDescription = t('hero.subtitle');

$latestNews = get_latest_news(3);
$departments = get_departments();
$albums = array_slice(get_gallery_albums(), 0, 4);
$magazineIssues = get_magazine_issues(3);

require_once __DIR__ . '/includes/header.php';
?>

<main>

  <!-- Hero -->
  <section class="hero">
    <div class="container">
      <h1><?= e(t('hero.title')) ?></h1>
      <p><?= e(t('hero.subtitle')) ?></p>
      <div class="hero-actions">
        <a href="<?= BASE_URL ?>admissions.php" class="btn btn-accent"><?= e(t('hero.cta1')) ?></a>
        <a href="<?= BASE_URL ?>about.php" class="btn btn-white"><?= e(t('hero.cta2')) ?></a>
      </div>
    </div>
  </section>

  <!-- About -->
  <section class="section">
    <div class="container">
      <div class="section-title">
        <h2><?= e(t('about.title')) ?></h2>
        <div class="underline"></div>
        <p><?= e(t('about.subtitle')) ?></p>
      </div>
      <div class="about-grid">
        <div>
          <h3><?= e(t('about.mission')) ?></h3>
          <p><?= e(t('about.mission.text')) ?></p>
          <p><?= e(t('about.mission.text2')) ?></p>
          <div class="stats">
            <div class="stat"><strong>25+</strong><span><?= e(t('stats.years')) ?></span></div>
            <div class="stat"><strong>1,200+</strong><span><?= e(t('stats.students')) ?></span></div>
            <div class="stat"><strong>40+</strong><span><?= e(t('stats.teachers')) ?></span></div>
          </div>
        </div>
        <div class="about-img">
          <img src="<?= BASE_URL ?>images/about.jpg" alt="<?= e(t('about.title')) ?>">
        </div>
      </div>
    </div>
  </section>

  <!-- Departments -->
  <section class="section section-alt">
    <div class="container">
      <div class="section-title">
        <h2><?= e(t('departments.title')) ?></h2>
        <div class="underline"></div>
        <p><?= e(t('departments.subtitle')) ?></p>
      </div>
      <div class="grid grid-4">
        <?php foreach ($departments as $d): ?>
          <div class="card">
            <div class="card-icon">📖</div>
            <h3><?= e(tf($d, 'name')) ?></h3>
            <p><?= e(excerpt(tf($d, 'description'), 130)) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- News -->
  <section class="section">
    <div class="container">
      <div class="section-title">
        <h2><?= e(t('news.title')) ?></h2>
        <div class="underline"></div>
        <p><?= e(t('news.subtitle')) ?></p>
      </div>
      <div class="grid grid-3">
        <?php foreach ($latestNews as $n): ?>
          <article class="card">
            <span class="news-date"><?= e(format_date($n['published_at'])) ?></span>
            <h3><?= e(tf($n, 'title')) ?></h3>
            <p><?= e(excerpt(tf($n, 'excerpt'))) ?></p>
            <a class="btn btn-outline btn-sm mt-2" href="<?= BASE_URL ?>news-detail.php?slug=<?= e($n['slug']) ?>"><?= e(t('news.readmore')) ?></a>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Magazine -->
  <?php if ($magazineIssues): ?>
  <section class="section section-alt">
    <div class="container">
      <div class="section-title">
        <h2><?= e(t('magazine.title')) ?></h2>
        <div class="underline"></div>
        <p><?= e(t('magazine.subtitle')) ?></p>
      </div>
      <div class="grid grid-3">
        <?php foreach ($magazineIssues as $m): ?>
          <div class="card">
            <?php if ($m['cover_image']): ?>
              <img src="<?= UPLOAD_URL . e($m['cover_image']) ?>" alt="Issue" style="border-radius:8px;margin-bottom:1rem;">
            <?php endif; ?>
            <h3><?= e(tf($m, 'title')) ?></h3>
            <p><?= e(excerpt(tf($m, 'description'), 100)) ?></p>
            <a class="btn btn-outline btn-sm mt-2" href="<?= BASE_URL ?>magazine.php?issue=<?= (int)$m['id'] ?>"><?= e(t('magazine.read')) ?></a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Gallery -->
  <?php if ($albums): ?>
  <section class="section">
    <div class="container">
      <div class="section-title">
        <h2><?= e(t('gallery.title')) ?></h2>
        <div class="underline"></div>
        <p><?= e(t('gallery.subtitle')) ?></p>
      </div>
      <div class="gallery-grid">
        <?php foreach ($albums as $a): ?>
          <div class="gallery-item">
            <img src="<?= UPLOAD_URL . e($a['cover_image'] ?: 'gallery/placeholder.jpg') ?>" alt="<?= e(tf($a,'title')) ?>">
            <div class="overlay"><?= e(tf($a, 'title')) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Donation CTA -->
  <section class="section">
    <div class="container">
      <div class="donation-cta">
        <h2><?= e(t('donate.title')) ?></h2>
        <p><?= e(t('donate.subtitle')) ?></p>
        <a href="<?= BASE_URL ?>donate.php" class="btn btn-accent"><?= e(t('donate.cta')) ?></a>
      </div>
    </div>
  </section>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
