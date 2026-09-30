<?php
require_once __DIR__ . '/config/config.php';
$pageTitle = t('admissions.title');
$metaDescription = t('admissions.subtitle');

$success = null; $error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Invalid session. Please try again.';
    } elseif (!rate_limit('admission', 3, 600)) {
        $error = 'Too many submissions. Please wait.';
    } else {
        $data = [
            'program_id' => (int)($_POST['program_id'] ?? 0),
            'student_name' => clean_input($_POST['student_name'] ?? ''),
            'father_name' => clean_input($_POST['father_name'] ?? ''),
            'date_of_birth' => clean_input($_POST['dob'] ?? ''),
            'phone' => clean_input($_POST['phone'] ?? ''),
            'email' => clean_input($_POST['email'] ?? ''),
            'address' => clean_input($_POST['address'] ?? ''),
            'previous_school' => clean_input($_POST['previous_school'] ?? ''),
            'previous_grade' => clean_input($_POST['previous_grade'] ?? ''),
            'message' => clean_input($_POST['message'] ?? ''),
        ];
        if (!$data['program_id'] || !$data['student_name'] || !$data['father_name'] || !$data['phone']) {
            $error = 'Please fill all required fields.';
        } elseif ($data['email'] && !valid_email($data['email'])) {
            $error = 'Invalid email address.';
        } elseif (!valid_phone($data['phone'])) {
            $error = 'Invalid phone number.';
        } else {
            try {
                $appNo = generate_application_no();
                $stmt = db()->prepare("INSERT INTO admission_applications
                    (application_no, program_id, student_name, father_name, date_of_birth, phone, email, address, previous_school, previous_grade, message)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([
                    $appNo, $data['program_id'], $data['student_name'], $data['father_name'],
                    $data['date_of_birth'] ?: null, $data['phone'], $data['email'] ?: null,
                    $data['address'], $data['previous_school'], $data['previous_grade'], $data['message']
                ]);
                $success = t('form.success.admission') . ' ' . t('admissions.track.title') . ': ' . $appNo;
            } catch (Exception $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

$programs = get_admission_programs();
require_once __DIR__ . '/includes/header.php';
?>
<main>

<section class="page-header">
  <div class="container">
    <h1><?= e(t('admissions.title')) ?></h1>
    <p><?= e(t('admissions.subtitle')) ?></p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-title">
      <h2><?= e(t('admissions.dates')) ?></h2>
      <div class="underline"></div>
    </div>
    <ul class="admission-list">
      <li><strong><?= e(t('admissions.reg.open')) ?></strong> <span>5 March 2026</span></li>
      <li><strong><?= e(t('admissions.reg.close')) ?></strong> <span>29 March 2026</span></li>
      <li><strong><?= e(t('admissions.written')) ?></strong> <span>31 March 2026</span></li>
      <li><strong><?= e(t('admissions.oral')) ?></strong> <span>4 April 2026</span></li>
      <li><strong><?= e(t('admissions.classes')) ?></strong> <span>6 April 2026</span></li>
    </ul>
  </div>
</section>

<section class="section section-alt">
  <div class="container">
    <div class="section-title">
      <h2><?= e(t('admissions.apply.title')) ?></h2>
      <div class="underline"></div>
      <p><?= e(t('admissions.apply.subtitle')) ?></p>
    </div>

    <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <form method="post" style="max-width:760px;margin:0 auto;" data-validate>
      <?= csrf_field() ?>
      <div class="form-group">
        <label><?= e(t('form.program')) ?> *</label>
        <select name="program_id" required>
          <option value=""><?= e(t('form.select')) ?></option>
          <?php foreach ($programs as $p): ?>
            <option value="<?= (int)$p['id'] ?>"><?= e(tf($p, 'name')) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label><?= e(t('form.student.name')) ?> *</label>
        <input type="text" name="student_name" required>
      </div>
      <div class="form-group">
        <label><?= e(t('form.father.name')) ?> *</label>
        <input type="text" name="father_name" required>
      </div>
      <div class="form-group">
        <label><?= e(t('form.dob')) ?></label>
        <input type="date" name="dob">
      </div>
      <div class="form-group">
        <label><?= e(t('form.phone')) ?> *</label>
        <input type="tel" name="phone" required>
      </div>
      <div class="form-group">
        <label><?= e(t('form.email')) ?></label>
        <input type="email" name="email">
      </div>
      <div class="form-group">
        <label><?= e(t('contact.address')) ?></label>
        <input type="text" name="address">
      </div>
      <div class="form-group">
        <label>Previous School</label>
        <input type="text" name="previous_school">
      </div>
      <div class="form-group">
        <label>Previous Grade</label>
        <input type="text" name="previous_grade">
      </div>
      <div class="form-group">
        <label><?= e(t('form.additional')) ?></label>
        <textarea name="message"></textarea>
      </div>
      <button type="submit" class="btn btn-primary"><?= e(t('form.submit.apply')) ?></button>
    </form>
  </div>
</section>

</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
