<?php
require_once __DIR__ . '/config/config.php';
$pageTitle = t('misc.search');
$metaDescription = t('misc.search.placeholder');

$q = clean_input($_GET['q'] ?? '');
$results = ['news' => [], 'questions' => [], 'magazine' => []];

if ($q && mb_strlen($q) >= 2) {
    $like = '%' . $q . '%';

    $stmt = db()->prepare("SELECT * FROM news WHERE status='published' AND (title_en LIKE ? OR title_ur LIKE ? OR title_ar LIKE ? OR content_en LIKE ? OR content_ur LIKE ? OR content_ar LIKE ?) ORDER BY published_at DESC LIMIT 20");
    $stmt->execute([$like,$like,$like,$like,$like,$like]);
    $results['news'] = $stmt->fetchAll();

    $stmt = db()->prepare("SELECT * FROM questions WHERE is_public=1 AND status='answered' AND (question_en LIKE ? OR question_ur LIKE ? OR question_ar LIKE ? OR answer_en LIKE ? OR answer_ur LIKE ? OR answer_ar LIKE ?) ORDER BY answered_at DESC LIMIT 20");
    $stmt->execute([$like,$like,$like,$like,$like,$like]);
    $results['questions'] = $stmt->fetchAll();

    $stmt = db()->prepare("SELECT ma.*, mi.issue_number, mi.issue_year, mi.issue_month FROM magazine_articles ma JOIN magazine_issues mi ON ma.issue_id=mi.id WHERE ma.title_en LIKE ? OR ma.title_ur LIKE ? OR ma.title_ar LIKE ? LIMIT 20");
    $stmt->execute([$like,$like,$like]);
    $results['magazine'] = $stmt->fetchAll();
}

require_once __DIR__ . '/includes/header.php';
?>
<main>

<section class="page-header">
  <div class="container">
    <h1><?= e(t('misc.search')) ?></h1>
    <form method="get" style="background:transparent;box-shadow:none;padding:0;max-width:600px;margin:1.5rem auto 0;display:flex;gap:.5rem;">
      <input type="text" name="q" value="<?= e($q) ?>" placeholder="<?= e(t('misc.search.placeholder')) ?>" style="flex:1;">
      <button class="btn btn-accent" type="submit"><?= e(t('misc.search')) ?></button>
    </form>
  </div>
</section>

<section class="section">
  <div class="container">
    <?php if (!$q): ?>
      <p class="text-center"><?= e(t('misc.search.placeholder')) ?></p>
    <?php else: ?>
      <h2>News (<?= count($results['news']) ?>)</h2>
      <?php foreach ($results['news'] as $n): ?>
        <div class="card mb-2">
          <a href="<?= BASE_URL ?>news-detail.php?slug=<?= e($n['slug']) ?>"><h3><?= e(tf($n, 'title')) ?></h3></a>
          <p><?= e(excerpt(tf($n, 'excerpt'), 160)) ?></p>
        </div>
      <?php endforeach; ?>

      <h2 class="mt-4">Q&A (<?= count($results['questions']) ?>)</h2>
      <?php foreach ($results['questions'] as $qq): ?>
        <div class="card mb-2">
          <h3><?= e(excerpt(tf($qq, 'question'), 120)) ?></h3>
          <p><?= e(excerpt(tf($qq, 'answer'), 200)) ?></p>
        </div>
      <?php endforeach; ?>

      <h2 class="mt-4">Magazine (<?= count($results['magazine']) ?>)</h2>
      <?php foreach ($results['magazine'] as $m): ?>
        <div class="card mb-2">
          <h3><?= e(tf($m, 'title')) ?></h3>
          <p style="font-size:.85rem;">Issue #<?= (int)$m['issue_number'] ?> · <?= (int)$m['issue_year'] ?></p>
        </div>
      <?php endforeach; ?>

      <?php if (empty($results['news']) && empty($results['questions']) && empty($results['magazine'])): ?>
        <p class="text-center"><?= e(t('misc.noresults')) ?></p>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>

</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
