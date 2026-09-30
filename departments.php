<?php
require_once __DIR__ . '/config/config.php';
$pageTitle = t('departments.title');
$metaDescription = t('departments.subtitle');

$slug = $_GET['slug'] ?? null;
if ($slug) {
    $stmt = db()->prepare("SELECT * FROM departments WHERE slug=? AND is_active=1");
    $stmt->execute([$slug]);
    $dept = $stmt->fetch();
    if ($dept) {
        $pageTitle = tf($dept, 'name');
        $metaDescription = excerpt(tf($dept, 'description'));
    }
}
require_once __DIR__ . '/includes/header.php';
?>
<main>

<section class="page-header">
  <div class="container">
    <h1><?= e($dept ? tf($dept, 'name') : t('departments.title')) ?></h1>
    <p><?= e($dept ? excerpt(tf($dept,'description'),160) : t('departments.subtitle')) ?></p>
  </div>
</section>

<?php if ($dept): ?>
  <section class="section">
    <div class="container">
      <div class="card" style="max-width:900px;margin:0 auto;">
        <div class="card-icon">📖</div>
        <h3><?= e(tf($dept, 'name')) ?></h3>
        <div style="margin-top:1rem;line-height:1.9;"><?= nl2br(e(tf($dept, 'description'))) ?></div>
      </div>
    </div>
  </section>
<?php else: ?>
  <section class="section">
    <div class="container">
      <div class="grid grid-2">
        <?php foreach (get_departments() as $d): ?>
          <div class="card">
            <div class="card-icon">📖</div>
            <h3><?= e(tf($d, 'name')) ?></h3>
            <p><?= e(excerpt(tf($d, 'description'), 200)) ?></p>
            <a class="btn btn-outline btn-sm mt-2" href="?slug=<?= e($d['slug']) ?>"><?= e(t('misc.readmore')) ?></a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
<?php endif; ?>

</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
