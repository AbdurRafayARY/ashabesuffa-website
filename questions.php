<?php
require_once __DIR__ . '/config/config.php';
$pageTitle = t('questions.title');
$metaDescription = t('questions.subtitle');

$page = max(1, (int)($_GET['page'] ?? 1));
$result = get_public_questions(10, $page);
$categories = get_question_categories();

require_once __DIR__ . '/includes/header.php';
?>
<main>

<section class="page-header">
  <div class="container">
    <h1><?= e(t('questions.title')) ?></h1>
    <p><?= e(t('questions.subtitle')) ?></p>
  </div>
</section>

<section class="section">
  <div class="container" style="max-width:900px;">
    <div class="text-center mb-4">
      <a href="<?= BASE_URL ?>ask-question.php" class="btn btn-accent"><?= e(t('questions.ask')) ?></a>
    </div>

    <?php if (empty($result['items'])): ?>
      <p class="text-center"><?= e(t('misc.noresults')) ?></p>
    <?php else: ?>
      <?php foreach ($result['items'] as $q): ?>
        <div class="card mb-3">
          <span class="news-date"><?= e(t('questions.asked')) ?>: <?= e(format_date($q['created_at'])) ?></span>
          <h3><?= e(t('questions.question')) ?></h3>
          <p><?= nl2br(e(tf($q, 'question'))) ?></p>
          <h4 style="margin-top:1rem;color:var(--primary);"><?= e(t('questions.answer')) ?></h4>
          <p><?= nl2br(e(tf($q, 'answer'))) ?></p>
        </div>
      <?php endforeach; ?>

      <?php if ($result['total_pages'] > 1): ?>
      <div class="pagination">
        <?php for ($i = 1; $i <= $result['total_pages']; $i++): ?>
          <?php if ($i === $result['current']): ?>
            <span class="active"><?= $i ?></span>
          <?php else: ?>
            <a href="?page=<?= $i ?>"><?= $i ?></a>
          <?php endif; ?>
        <?php endfor; ?>
      </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>

</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
