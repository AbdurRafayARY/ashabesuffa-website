<?php
require_once __DIR__ . '/../config/config.php';

if (!empty($_SESSION['admin_id'])) redirect(BASE_URL . 'admin/dashboard.php');

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) $error = 'Invalid session.';
    else {
        $u = clean_input($_POST['username'] ?? '');
        $p = $_POST['password'] ?? '';
        if (!$u || !$p) $error = 'Please enter username and password.';
        else {
            $stmt = db()->prepare("SELECT * FROM users WHERE username=? AND is_active=1 LIMIT 1");
            $stmt->execute([$u]);
            $user = $stmt->fetch();
            if ($user && password_verify($p, $user['password_hash'])) {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_name'] = $user['full_name'];
                $_SESSION['admin_role'] = $user['role'];
                db()->prepare("UPDATE users SET last_login=NOW() WHERE id=?")->execute([$user['id']]);
                redirect(BASE_URL . 'admin/dashboard.php');
            } else $error = 'Invalid credentials.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - Ashabesuffa Foundation</title>
<link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>css/admin.css">
</head>
<body class="admin-login">
  <div class="login-box">
    <h1>🕌 Admin Login</h1>
    <p>Ashabesuffa Foundation</p>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
      <?= csrf_field() ?>
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" required autofocus>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-primary">Login</button>
    </form>
    <p style="text-align:center;margin-top:1rem;font-size:.85rem;color:#6b7280;">
      Default: <code>admin</code> / <code>Admin@123</code> (change immediately)
    </p>
  </div>
</body>
</html>
