<?php
require_once __DIR__ . '/config/config.php';
$pageTitle = t('prayer.title');
$metaDescription = t('prayer.subtitle');

$city = clean_input($_GET['city'] ?? 'Karachi');
$country = clean_input($_GET['country'] ?? 'Pakistan');
$times = null; $error = null;

if ($city && $country) {
    // Check cache first
    $stmt = db()->prepare("SELECT * FROM prayer_times WHERE city=? AND country=? AND prayer_date=CURDATE()");
    $stmt->execute([$city, $country]);
    $cached = $stmt->fetch();
    if ($cached) {
        $times = $cached;
    } else {
        // Fetch from Aladhan API
        $url = "https://api.aladhan.com/v1/timingsByCity?city=" . urlencode($city) . "&country=" . urlencode($country) . "&method=2";
        $ctx = stream_context_create(['http' => ['timeout' => 8]]);
        $resp = @file_get_contents($url, false, $ctx);
        if ($resp) {
            $json = json_decode($resp, true);
            if (!empty($json['data']['timings'])) {
                $t = $json['data']['timings'];
                $stmt = db()->prepare("INSERT INTO prayer_times (city, country, prayer_date, fajr, sunrise, dhuhr, asr, maghrib, isha) VALUES (?,?,CURDATE(),?,?,?,?,?,?)");
                $stmt->execute([$city, $country, $t['Fajr'], $t['Sunrise'], $t['Dhuhr'], $t['Asr'], $t['Maghrib'], $t['Isha']]);
                $times = ['fajr'=>$t['Fajr'],'sunrise'=>$t['Sunrise'],'dhuhr'=>$t['Dhuhr'],'asr'=>$t['Asr'],'maghrib'=>$t['Maghrib'],'isha'=>$t['Isha']];
            }
        }
        if (!$times) $error = 'Could not fetch prayer times. Please try again.';
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<main>

<section class="page-header">
  <div class="container">
    <h1><?= e(t('prayer.title')) ?></h1>
    <p><?= e(t('prayer.subtitle')) ?></p>
  </div>
</section>

<section class="section">
  <div class="container" style="max-width:760px;">
    <form method="get" class="mb-3">
      <div class="form-group">
        <label><?= e(t('prayer.city')) ?></label>
        <input type="text" name="city" value="<?= e($city) ?>" required>
      </div>
      <div class="form-group">
        <label><?= e(t('prayer.country')) ?></label>
        <input type="text" name="country" value="<?= e($country) ?>" required>
      </div>
      <button type="submit" class="btn btn-primary"><?= e(t('prayer.get')) ?></button>
    </form>

    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <?php if ($times): ?>
      <div class="card">
        <h3><?= e($city) ?>, <?= e($country) ?> — <?= e(format_date(date('Y-m-d'))) ?></h3>
        <ul class="admission-list mt-3">
          <li><strong><?= e(t('prayer.fajr')) ?></strong> <span><?= e($times['fajr']) ?></span></li>
          <li><strong><?= e(t('prayer.sunrise')) ?></strong> <span><?= e($times['sunrise']) ?></span></li>
          <li><strong><?= e(t('prayer.dhuhr')) ?></strong> <span><?= e($times['dhuhr']) ?></span></li>
          <li><strong><?= e(t('prayer.asr')) ?></strong> <span><?= e($times['asr']) ?></span></li>
          <li><strong><?= e(t('prayer.maghrib')) ?></strong> <span><?= e($times['maghrib']) ?></span></li>
          <li><strong><?= e(t('prayer.isha')) ?></strong> <span><?= e($times['isha']) ?></span></li>
        </ul>
      </div>
    <?php endif; ?>
  </div>
</section>

</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
