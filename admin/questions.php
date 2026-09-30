<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/security.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    db()->prepare("UPDATE questions SET answer_en=?, answer_ur=?, answer_ar=?, status='answered', is_public=?, answered_at=NOW(), answered_by=? WHERE id=?")
        ->execute([
            $_POST['answer_en'] ?? '', $_POST['answer_ur'] ?? '', $_POST['answer_ar'] ?? '',
            !empty($_POST['is_public']) ? 1 : 0, $_SESSION['admin_id'], $id
        ]);
    redirect(BASE_URL . 'admin/questions.php');
}

$filter = $_GET['filter'] ?? 'pending';
$sql = "SELECT * FROM questions";
if ($filter !== 'all') $sql .= " WHERE status='" . ($filter === 'pending' ? 'pending' : 'answered') . "'";
$sql .= " ORDER BY created_at DESC";
$items = db()->query($sql)->fetchAll();
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Q&A Admin</title>
<link rel="stylesheet" href="<?= BASE_URL ?>css/style.css"><link rel="stylesheet" href="<?= BASE_URL ?>css/admin.css"></head>
<body class="admin-body">
<?php require __DIR__ . '/includes/admin-header.php'; ?>
<main class="admin-main">
  <h1>Questions & Answers</h1>
  <div style="margin-bottom:1rem;">
    <a class="btn btn-sm <?= $filter==='pending'?'btn-primary':'btn-outline' ?>" href="?filter=pending">Pending</a>
    <a class="btn btn-sm <?= $filter==='answered'?'btn-primary':'btn-outline' ?>" href="?filter=answered">Answered</a>
    <a class="btn btn-sm <?= $filter==='all'?'btn-primary':'btn-outline' ?>" href="?filter=all">All</a>
  </div>
  <?php foreach ($items as $q): ?>
    <div class="card mb-3">
      <p style="font-size:.8rem;color:#999;">#<?= (int)$q['id'] ?> · <?= e($q['questioner_name'] ?: 'Anonymous') ?> · <?= e($q['created_at']) ?></p>
      <p><strong>Q:</strong> <?= nl2br(e($q['question_en'])) ?></p>
      <form method="post">
        <input type="hidden" name="id" value="<?= (int)$q['id'] ?>">
        <div class="form-group"><label>Answer (English)</label><textarea name="answer_en" rows="4"><?= e($q['answer_en']) ?></textarea></div>
        <div class="form-group"><label>Answer (Urdu)</label><textarea name="answer_ur" rows="4" dir="rtl"><?= e($q['answer_ur']) ?></textarea></div>
        <div class="form-group"><label>Answer (Arabic)</label><textarea name="answer_ar" rows="4" dir="rtl"><?= e($q['answer_ar']) ?></textarea></div>
        <label><input type="checkbox" name="is_public" value="1" <?= $q['is_public']?'checked':'' ?>> Make public</label>
        <button class="btn btn-primary btn-sm mt-2">Save Answer</button>
      </form>
    </div>
  <?php endforeach; ?>
</main>
<?php require __DIR__ . '/includes/admin-footer.php'; ?>
