<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/security.php';
require_admin();

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);
$msg = null;

// Handle delete
if ($action === 'delete' && $id) {
    db()->prepare("DELETE FROM news WHERE id=?")->execute([$id]);
    redirect(BASE_URL . 'admin/news.php');
}

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'slug' => slugify($_POST['title_en'] ?? ''),
        'title_en' => clean_input($_POST['title_en'] ?? ''),
        'title_ur' => clean_input($_POST['title_ur'] ?? ''),
        'title_ar' => clean_input($_POST['title_ar'] ?? ''),
        'excerpt_en' => clean_input($_POST['excerpt_en'] ?? ''),
        'excerpt_ur' => clean_input($_POST['excerpt_ur'] ?? ''),
        'excerpt_ar' => clean_input($_POST['excerpt_ar'] ?? ''),
        'content_en' => $_POST['content_en'] ?? '',
        'content_ur' => $_POST['content_ur'] ?? '',
        'content_ar' => $_POST['content_ar'] ?? '',
        'status' => $_POST['status'] ?? 'draft',
        'published_at' => $_POST['published_at'] ?: date('Y-m-d H:i:s'),
    ];

    if ($id) {
        db()->prepare("UPDATE news SET slug=?, title_en=?, title_ur=?, title_ar=?, excerpt_en=?, excerpt_ur=?, excerpt_ar=?, content_en=?, content_ur=?, content_ar=?, status=?, published_at=? WHERE id=?")
            ->execute([...array_values($data), $id]);
    } else {
        db()->prepare("INSERT INTO news (slug, title_en, title_ur, title_ar, excerpt_en, excerpt_ur, excerpt_ar, content_en, content_ur, content_ar, status, published_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)")
            ->execute(array_values($data));
    }
    redirect(BASE_URL . 'admin/news.php');
}

$news = db()->query("SELECT * FROM news ORDER BY created_at DESC")->fetchAll();
$editItem = null;
if ($action === 'edit' && $id) {
    $stmt = db()->prepare("SELECT * FROM news WHERE id=?");
    $stmt->execute([$id]);
    $editItem = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>News - Admin</title>
<link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>css/admin.css">
</head>
<body class="admin-body">
<?php require __DIR__ . '/includes/admin-header.php'; ?>
<main class="admin-main">

<?php if ($action === 'edit' || $action === 'new'): ?>
  <h1><?= $editItem ? 'Edit' : 'Add' ?> News</h1>
  <form method="post" class="admin-form">
    <div class="form-group"><label>Title (English) *</label><input type="text" name="title_en" value="<?= e($editItem['title_en'] ?? '') ?>" required></div>
    <div class="form-group"><label>Title (Urdu)</label><input type="text" name="title_ur" dir="rtl" value="<?= e($editItem['title_ur'] ?? '') ?>"></div>
    <div class="form-group"><label>Title (Arabic)</label><input type="text" name="title_ar" dir="rtl" value="<?= e($editItem['title_ar'] ?? '') ?>"></div>
    <div class="form-group"><label>Excerpt (English)</label><textarea name="excerpt_en"><?= e($editItem['excerpt_en'] ?? '') ?></textarea></div>
    <div class="form-group"><label>Excerpt (Urdu)</label><textarea name="excerpt_ur" dir="rtl"><?= e($editItem['excerpt_ur'] ?? '') ?></textarea></div>
    <div class="form-group"><label>Excerpt (Arabic)</label><textarea name="excerpt_ar" dir="rtl"><?= e($editItem['excerpt_ar'] ?? '') ?></textarea></div>
    <div class="form-group"><label>Content (English)</label><textarea name="content_en" rows="8"><?= e($editItem['content_en'] ?? '') ?></textarea></div>
    <div class="form-group"><label>Content (Urdu)</label><textarea name="content_ur" rows="8" dir="rtl"><?= e($editItem['content_ur'] ?? '') ?></textarea></div>
    <div class="form-group"><label>Content (Arabic)</label><textarea name="content_ar" rows="8" dir="rtl"><?= e($editItem['content_ar'] ?? '') ?></textarea></div>
    <div class="form-group">
      <label>Status</label>
      <select name="status">
        <option value="draft" <?= ($editItem['status'] ?? '')==='draft'?'selected':'' ?>>Draft</option>
        <option value="published" <?= ($editItem['status'] ?? '')==='published'?'selected':'' ?>>Published</option>
        <option value="archived" <?= ($editItem['status'] ?? '')==='archived'?'selected':'' ?>>Archived</option>
      </select>
    </div>
    <div class="form-group"><label>Published At</label><input type="datetime-local" name="published_at" value="<?= e(date('Y-m-d\TH:i', strtotime($editItem['published_at'] ?? 'now'))) ?>"></div>
    <button type="submit" class="btn btn-primary">Save</button>
    <a href="<?= BASE_URL ?>admin/news.php" class="btn btn-outline">Cancel</a>
  </form>
<?php else: ?>
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <h1>News</h1>
    <a href="?action=new" class="btn btn-primary">+ Add News</a>
  </div>
  <table class="admin-table">
    <thead><tr><th>ID</th><th>Title</th><th>Status</th><th>Views</th><th>Created</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($news as $n): ?>
      <tr>
        <td><?= (int)$n['id'] ?></td>
        <td><?= e($n['title_en']) ?></td>
        <td><span class="badge badge-<?= e($n['status']) ?>"><?= e($n['status']) ?></span></td>
        <td><?= (int)$n['views'] ?></td>
        <td><?= e(date('M j, Y', strtotime($n['created_at']))) ?></td>
        <td>
          <a href="?action=edit&id=<?= (int)$n['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
          <a href="?action=delete&id=<?= (int)$n['id'] ?>" class="btn btn-sm btn-accent" onclick="return confirm('Delete?')">Delete</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

</main>
<?php require __DIR__ . '/includes/admin-footer.php'; ?>
