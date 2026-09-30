<?php
require_once __DIR__ . '/config/config.php';
$pageTitle = t('magazine.title');
$metaDescription = t('magazine.subtitle');

$issueId = (int)($_GET['issue'] ?? 0);
$issues = get_magazine_issues();
$currentIssue = null; $articles = [];

if ($issueId) {
    $stmt = db()->prepare("SELECT * FROM magazine_issues WHERE id=? AND is_published=1");
    $stmt->execute([$issueId]);
    $currentIssue = $stmt->fetch();
    if ($currentIssue) {
        $stmt = db()->prepare("SELECT * FROM magazine_articles WHERE issue_id=? ORDER BY page_number");
        $stmt->execute([$issueId]);
        $articles = $stmt->fetchAll();
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<main>

<section class="page-header">
  <div class="container">
    <h1><?= e(t('magazine.title')) ?></h1>
    <p><?= e(t('magazine.subtitle')) ?></p>
  </div>
</section>

<section class="section">
  <div class="container">
    <?php if ($currentIssue): ?>
      <a href="<?= BASE_URL ?>magazine.php" class="btn btn-outline btn-sm mb-3">← <?= e(t('misc.back')) ?></a>
      <h2><?= e(tf($currentIssue, 'title')) ?></h2>
      <p><?= e(tf($currentIssue, 'description')) ?></p>
      <?php if ($currentIssue['pdf_file']): ?>
        <a href="<?= UPLOAD_URL . e($currentIssue['pdf_file']) ?>" target="_blank" class="btn btn-accent mb-3"><?= e(t('magazine.download')) ?></a>
      <?php endif; ?>
      <div class="grid grid-2 mt-3">
        <?php foreach ($articles as $a): ?>
          <div class="card">
            <h3><?= e(tf($a, 'title')) ?></h3>
            <p style="font-size:.85rem;color:var(--accent-dark);">By <?= e($a['author_name']) ?> · Page <?= (int)$a['page_number'] ?></p>
            <div style="margin-top:.75rem;"><?= nl2br(e(excerpt(tf($a, 'content'), 300))) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="grid grid-3">
        <?php foreach ($issues as $m): ?>
          <div class="card">
            <?php if ($m['cover_image']): ?>
              <img src="<?= UPLOAD_URL . e($m['cover_image']) ?>" alt="Issue" style="border-radius:8px;margin-bottom:1rem;">
            <?php endif; ?>
            <h3><?= e(tf($m, 'title')) ?></h3>
            <p><?= e(excerpt(tf($m, 'description'), 120)) ?></p>
            <a class="btn btn-outline btn-sm mt-2" href="?issue=<?= (int)$m['id'] ?>"><?= e(t('magazine.read')) ?></a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
