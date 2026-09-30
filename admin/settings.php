<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/security.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $k => $v) {
        if (strpos($k, 'setting_') === 0) {
            $key = substr($k, 8);
            db()->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)")
                ->execute([$key, $v]);
        }
    }
    redirect(BASE_URL . 'admin/settings.php');
}
$settings = db()->query("SELECT * FROM settings ORDER BY setting_key")->fetchAll();
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Settings</title>
<link rel="stylesheet" href="<?= BASE_URL ?>css/style.css"><link rel="stylesheet" href="<?= BASE_URL ?>css/admin.css"></head>
<body class="admin-body">
<?php require __DIR__ . '/includes/admin-header.php'; ?>
<main class="admin-main">
  <h1>Site Settings</h1>
  <form method="post" class="admin-form">
    <?php foreach ($settings as $s): ?>
      <div class="form-group">
        <label><?= e($s['setting_key']) ?></label>
        <input name="setting_<?= e($s['setting_key']) ?>" value="<?= e($s['setting_value']) ?>">
      </div>
    <?php endforeach; ?>
    <button class="btn btn-primary">Save Settings</button>
  </form>
</main>
<?php require __DIR__ . '/includes/admin-footer.php'; ?>
