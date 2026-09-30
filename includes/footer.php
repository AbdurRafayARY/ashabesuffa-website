<!-- Footer -->
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div>
        <h4><?= e(t('brand.name')) ?></h4>
        <p><?= e(t('footer.about')) ?></p>
        <div class="footer-socials">
          <a href="<?= e(get_setting('facebook_url')) ?>" aria-label="Facebook">f</a>
          <a href="<?= e(get_setting('youtube_url')) ?>" aria-label="YouTube">▶</a>
          <a href="<?= e(get_setting('twitter_url')) ?>" aria-label="Twitter">𝕏</a>
        </div>
      </div>
      <div>
        <h4><?= e(t('footer.quicklinks')) ?></h4>
        <div class="footer-links">
          <a href="<?= BASE_URL ?>about.php"><?= e(t('nav.about')) ?></a>
          <a href="<?= BASE_URL ?>departments.php"><?= e(t('nav.departments')) ?></a>
          <a href="<?= BASE_URL ?>admissions.php"><?= e(t('nav.admissions')) ?></a>
          <a href="<?= BASE_URL ?>magazine.php"><?= e(t('nav.magazine')) ?></a>
          <a href="<?= BASE_URL ?>questions.php"><?= e(t('nav.questions')) ?></a>
        </div>
      </div>
      <div>
        <h4><?= e(t('footer.departments')) ?></h4>
        <div class="footer-links">
          <?php foreach (get_departments() as $d): ?>
            <a href="<?= BASE_URL ?>departments.php?slug=<?= e($d['slug']) ?>"><?= e(tf($d, 'name')) ?></a>
          <?php endforeach; ?>
        </div>
      </div>
      <div>
        <h4><?= e(t('footer.newsletter')) ?></h4>
        <p><?= e(t('footer.newsletter.text')) ?></p>
        <form method="post" action="<?= BASE_URL ?>subscribe.php" class="newsletter-form">
          <?= csrf_field() ?>
          <input type="email" name="email" placeholder="<?= e(t('form.email')) ?>" required>
          <button class="btn btn-accent btn-sm" type="submit"><?= e(t('footer.subscribe')) ?></button>
        </form>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© <?= date('Y') ?> <?= e(t('brand.name')) ?>. <?= e(t('footer.rights')) ?></span>
      <span>
        <a href="<?= BASE_URL ?>privacy.php"><?= e(t('footer.privacy')) ?></a> ·
        <a href="<?= BASE_URL ?>terms.php"><?= e(t('footer.terms')) ?></a>
      </span>
    </div>
  </div>
</footer>

<!-- Floating buttons -->
<a class="whatsapp" href="https://wa.me/<?= e(get_setting('whatsapp_number')) ?>" target="_blank" rel="noopener" aria-label="WhatsApp">💬</a>
<button class="back-to-top" id="backToTop" aria-label="Back to top">↑</button>

<script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
<script src="<?= BASE_URL ?>js/main.js"></script>
</body>
</html>
