<?php
require_once __DIR__ . '/config/config.php';
$pageTitle = t('donate.title');
$metaDescription = t('donate.subtitle');

$success = null; $error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) $error = 'Invalid session.';
    else {
        $name = clean_input($_POST['name'] ?? '');
        $email = clean_input($_POST['email'] ?? '');
        $phone = clean_input($_POST['phone'] ?? '');
        $amount = (float)($_POST['amount'] ?? 0);
        $purpose = clean_input($_POST['purpose'] ?? '');
        $anon = !empty($_POST['anonymous']) ? 1 : 0;
        if ($amount <= 0) $error = 'Please enter a valid amount.';
        else {
            $stmt = db()->prepare("INSERT INTO donations (donor_name, donor_email, donor_phone, amount, currency, purpose, is_anonymous) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$anon ? null : $name, $email, $phone, $amount, 'PKR', $purpose, $anon]);
            $success = 'Thank you! Your donation has been recorded.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<main>

<section class="page-header">
  <div class="container">
    <h1><?= e(t('donate.title')) ?></h1>
    <p><?= e(t('donate.subtitle')) ?></p>
  </div>
</section>

<section class="section">
  <div class="container" style="max-width:640px;">
    <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <form method="post">
      <?= csrf_field() ?>
      <div class="form-group"><label><?= e(t('form.name')) ?></label><input type="text" name="name"></div>
      <div class="form-group"><label><?= e(t('form.email')) ?></label><input type="email" name="email"></div>
      <div class="form-group"><label><?= e(t('form.phone')) ?></label><input type="tel" name="phone"></div>
      <div class="form-group"><label><?= e(t('donate.amount')) ?> (PKR) *</label><input type="number" name="amount" min="100" step="100" required></div>
      <div class="form-group">
        <label><?= e(t('donate.purpose')) ?></label>
        <select name="purpose">
          <option>General Fund</option>
          <option>Education</option>
          <option>Welfare</option>
          <option>Orphan Support</option>
          <option>Building Fund</option>
        </select>
      </div>
      <div class="form-group">
        <label><input type="checkbox" name="anonymous" style="width:auto;display:inline-block;"> <?= e(t('donate.anonymous')) ?></label>
      </div>
      <button type="submit" class="btn btn-accent"><?= e(t('donate.cta')) ?></button>
    </form>
  </div>
</section>

</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
