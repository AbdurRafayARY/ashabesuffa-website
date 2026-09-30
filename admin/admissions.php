<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/security.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    db()->prepare("UPDATE admission_applications SET status=? WHERE id=?")
        ->execute([$_POST['status'], (int)$_POST['id']]);
    redirect(BASE_URL . 'admin/admissions.php');
}
$items = db()->query("SELECT aa.*, ap.name_en AS program FROM admission_applications aa LEFT JOIN admission_programs ap ON aa.program_id=ap.id ORDER BY aa.created_at DESC")->fetchAll();
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Admissions</title>
<link rel="stylesheet" href="<?= BASE_URL ?>css/style.css"><link rel="stylesheet" href="<?= BASE_URL ?>css/admin.css"></head>
<body class="admin-body">
<?php require __DIR__ . '/includes/admin-header.php'; ?>
<main class="admin-main">
  <h1>Admission Applications</h1>
  <table class="admin-table">
    <thead><tr><th>App #</th><th>Student</th><th>Program</th><th>Phone</th><th>Status</th><th>Date</th><th>Update</th></tr></thead>
    <tbody>
    <?php foreach ($items as $a): ?>
      <tr>
        <td><?= e($a['application_no']) ?></td>
        <td><?= e($a['student_name']) ?><br><small><?= e($a['father_name']) ?></small></td>
        <td><?= e($a['program']) ?></td>
        <td><?= e($a['phone']) ?></td>
        <td><span class="badge"><?= e($a['status']) ?></span></td>
        <td><?= e(date('M j, Y', strtotime($a['created_at']))) ?></td>
        <td>
          <form method="post" style="background:none;padding:0;box-shadow:none;display:flex;gap:.25rem;">
            <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
            <select name="status" style="padding:.25rem;">
              <?php foreach (['pending','reviewing','approved','rejected','enrolled'] as $s): ?>
                <option <?= $a['status']===$s?'selected':'' ?>><?= $s ?></option>
              <?php endforeach; ?>
            </select>
            <button class="btn btn-sm btn-primary">Save</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</main>
<?php require __DIR__ . '/includes/admin-footer.php'; ?>
