<?php
require_once __DIR__ . '/config/config.php';
$pageTitle = t('contact.title');
$metaDescription = t('contact.subtitle');

$success = null; $error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) $error = 'Invalid session.';
    elseif (!rate_limit('contact', 5, 600)) $error = 'Too many submissions. Please wait.';
    else {
        $name = clean_input($_POST['name'] ?? '');
        $email = clean_input($_POST['email'] ?? '');
        $phone = clean_input($_POST['phone'] ?? '');
        $subject = clean_input($_POST['subject'] ?? '');
        $dept = clean_input($_POST['department'] ?? '');
        $msg = clean_input($_POST['message'] ?? '');
        if (!$name || !$email || !$msg) $error = 'Please fill all required fields.';
        elseif (!valid_email($email)) $error = 'Invalid email address.';
        else {
            $stmt = db()->prepare("INSERT INTO contact_messages (name, email, phone, subject, department, message, ip_address) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$name, $email, $phone, $subject, $dept, $msg, $_SERVER['REMOTE_ADDR'] ?? '']);
            $success = t('form.success.contact');
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<main>

<section class="page-header">
  <div class="container">
    <h1><?= e(t('contact.title')) ?></h1>
    <p><?= e(t('contact.subtitle')) ?></p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="grid grid-2">
      <div>
        <h2><?= e(t('contact.getintouch')) ?></h2>
        <p><?= e(t('contact.getintouch.text')) ?></p>
        <div class="contact-info mt-3" style="display:grid;gap:1rem;">
          <div class="card"><h4><?= e(t('contact.address')) ?></h4><p><?= e(get_setting('contact_address_' . current_lang(), get_setting('contact_address_en'))) ?></p></div>
          <div class="card"><h4><?= e(t('contact.phone')) ?></h4><p><?= e(get_setting('contact_phone')) ?></p></div>
          <div class="card"><h4><?= e(t('contact.email')) ?></h4><p><?= e(get_setting('contact_email')) ?></p></div>
          <div class="card"><h4><?= e(t('contact.hours')) ?></h4><p><?= e(t('contact.hours.text')) ?></p></div>
        </div>
      </div>
      <div>
        <h2><?= e(t('contact.form.title')) ?></h2>
        <p><?= e(t('contact.form.subtitle')) ?></p>
        <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <form method="post" class="mt-3" data-validate>
          <?= csrf_field() ?>
          <div class="form-group"><label><?= e(t('form.name')) ?> *</label><input type="text" name="name" required></div>
          <div class="form-group"><label><?= e(t('form.email')) ?> *</label><input type="email" name="email" required></div>
          <div class="form-group"><label><?= e(t('form.phone')) ?></label><input type="tel" name="phone"></div>
          <div class="form-group"><label><?= e(t('form.subject')) ?></label><input type="text" name="subject"></div>
          <div class="form-group">
            <label><?= e(t('form.department')) ?></label>
            <select name="department">
              <option value=""><?= e(t('form.select')) ?></option>
              <option><?= e(t('nav.admissions')) ?></option>
              <option><?= e(t('nav.departments')) ?></option>
              <option><?= e(t('nav.magazine')) ?></option>
              <option><?= e(t('nav.questions')) ?></option>
              <option><?= e(t('nav.donate')) ?></option>
            </select>
          </div>
          <div class="form-group"><label><?= e(t('form.message')) ?> *</label><textarea name="message" required></textarea></div>
          <button type="submit" class="btn btn-primary"><?= e(t('form.send')) ?></button>
        </form>
      </div>
    </div>
  </div>
</section>

</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
