<?php
require_once __DIR__ . '/config/config.php';
$pageTitle = t('questions.ask.title');
$metaDescription = t('questions.ask.subtitle');

$success = null; $error = null;
$categories = get_question_categories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) $error = 'Invalid session.';
    elseif (!rate_limit('question', 3, 600)) $error = 'Too many submissions. Please wait.';
    else {
        $name = clean_input($_POST['name'] ?? '');
        $email = clean_input($_POST['email'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0) ?: null;
        $question = trim($_POST['question'] ?? '');
        if (!$question) $error = 'Please enter your question.';
        elseif ($email && !valid_email($email)) $error = 'Invalid email.';
        else {
            try {
                $stmt = db()->prepare("INSERT INTO questions
                    (questioner_name, questioner_email, question_en, category_id, status)
                    VALUES (?,?,?,?, 'pending')");
                $stmt->execute([$name, $email, $question, $categoryId]);
                $success = t('form.success.question');
            } catch (Exception $e) { $error = 'Database error.'; }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<main>

<section class="page-header">
  <div class="container">
    <h1><?= e(t('questions.ask.title')) ?></h1>
    <p><?= e(t('questions.ask.subtitle')) ?></p>
  </div>
</section>

<section class="section">
  <div class="container" style="max-width:760px;">
    <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <form method="post" data-validate>
      <?= csrf_field() ?>
      <div class="form-group">
        <label><?= e(t('form.name')) ?></label>
        <input type="text" name="name">
      </div>
      <div class="form-group">
        <label><?= e(t('form.email')) ?></label>
        <input type="email" name="email">
      </div>
      <div class="form-group">
        <label><?= e(t('questions.category')) ?></label>
        <select name="category_id">
          <option value=""><?= e(t('form.select')) ?></option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= (int)$c['id'] ?>"><?= e(tf($c, 'name')) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label><?= e(t('form.question')) ?> *</label>
        <textarea name="question" required></textarea>
      </div>
      <button type="submit" class="btn btn-primary"><?= e(t('form.submit.question')) ?></button>
    </form>
  </div>
</section>

</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
