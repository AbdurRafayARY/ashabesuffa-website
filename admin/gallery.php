<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/security.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_album') {
    $slug = slugify($_POST['title_en']);
    db()->prepare("INSERT INTO gallery_albums (slug, title_en, title_ur, title_ar, description_en) VALUES (?,?,?,?,?)")
        ->execute([$slug, $_POST['title_en'], $_POST['title_ur'], $_POST['title_ar'], $_POST['description_en']]);
    redirect(BASE_URL . 'admin/gallery.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload') {
    $albumId = (int)$_POST['album_id'];
    $up = upload_file('image', allowed_image_mimes(), 'gallery');
    if ($up['ok']) {
        db()->prepare("INSERT INTO gallery_images (album_id, image_path, caption_en) VALUES (?,?,?)")
            ->execute([$albumId, $up['path'], $_POST['caption_en'] ?? '']);
    }
    redirect(BASE_URL . 'admin/gallery.php?album=' . $albumId);
}

$albums = get_gallery_albums();
$albumId = (int)($_GET['album'] ?? 0);
$images = $albumId ? get_album_images($albumId) : [];
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Gallery</title>
<link rel="stylesheet" href="<?= BASE_URL ?>css/style.css"><link rel="stylesheet" href="<?= BASE_URL ?>css/admin.css"></head>
<body class="admin-body">
<?php require __DIR__ . '/includes/admin-header.php'; ?>
<main class="admin-main">
  <h1>Gallery</h1>
  <h2>Albums</h2>
  <form method="post" class="admin-form">
    <input type="hidden" name="action" value="create_album">
    <div class="form-group"><label>Title (EN)</label><input name="title_en" required></div>
    <div class="form-group"><label>Title (UR)</label><input name="title_ur" dir="rtl"></div>
    <div class="form-group"><label>Title (AR)</label><input name="title_ar" dir="rtl"></div>
    <div class="form-group"><label>Description (EN)</label><textarea name="description_en"></textarea></div>
    <button class="btn btn-primary">Create Album</button>
  </form>

  <h2 class="mt-3">Albums</h2>
  <ul>
    <?php foreach ($albums as $a): ?>
      <li><a href="?album=<?= (int)$a['id'] ?>"><?= e($a['title_en']) ?></a> (<?= count(get_album_images($a['id'])) ?> images)</li>
    <?php endforeach; ?>
  </ul>

  <?php if ($albumId): ?>
    <h2 class="mt-3">Upload Image</h2>
    <form method="post" enctype="multipart/form-data" class="admin-form">
      <input type="hidden" name="action" value="upload">
      <input type="hidden" name="album_id" value="<?= $albumId ?>">
      <div class="form-group"><label>Image *</label><input type="file" name="image" accept="image/*" required></div>
      <div class="form-group"><label>Caption (EN)</label><input name="caption_en"></div>
      <button class="btn btn-primary">Upload</button>
    </form>
    <div class="gallery-grid mt-3">
      <?php foreach ($images as $img): ?>
        <div class="gallery-item"><img src="<?= UPLOAD_URL . e($img['image_path']) ?>" alt=""></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>
<?php require __DIR__ . '/includes/admin-footer.php'; ?>
