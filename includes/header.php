<?php
require_once __DIR__ . '/../config/config.php';
$lang = current_lang();
$dir = in_array($lang, RTL_LANGS) ? 'rtl' : 'ltr';
$pageTitle = $pageTitle ?? t('brand.name');
?>
<!DOCTYPE html>
<html lang="<?= e($lang) ?>" dir="<?= $dir ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?> - <?= e(t('brand.name')) ?></title>
<meta name="description" content="<?= e($metaDescription ?? t('hero.subtitle')) ?>">
<meta name="keywords" content="<?= e($metaKeywords ?? 'Islamic, madrasa, foundation, Quran, Hadith, Fiqh') ?>">

<!-- Open Graph -->
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($metaDescription ?? t('hero.subtitle')) ?>">
<meta property="og:image" content="<?= e($ogImage ?? BASE_URL . 'images/logo.png') ?>">
<meta property="og:url" content="<?= e(BASE_URL) ?>">
<meta property="og:type" content="website">
<meta name="twitter:card" content="summary_large_image">

<link rel="icon" type="image/png" href="<?= BASE_URL ?>images/favicon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Noto+Nastaliq+Urdu:wght@400;600;700&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
<?php if ($dir === 'rtl'): ?>
<link rel="stylesheet" href="<?= BASE_URL ?>css/rtl.css">
<?php endif; ?>
</head>
<body class="lang-<?= e($lang) ?>">

<!-- Top Bar -->
<div class="topbar">
  <div class="container topbar-inner">
    <div class="topbar-left">
      <span>🕌 <strong><?= e(t('brand.name')) ?></strong></span>
    </div>
    <div class="topbar-right">
      <a href="tel:<?= e(get_setting('contact_phone')) ?>">📞 <?= e(get_setting('contact_phone')) ?></a>
      <a href="mailto:<?= e(get_setting('contact_email')) ?>">✉ <?= e(get_setting('contact_email')) ?></a>
      <div class="lang-switcher">
        <a class="lang-btn <?= $lang==='en'?'active':'' ?>" href="<?= e(lang_url('en')) ?>">EN</a>
        <a class="lang-btn <?= $lang==='ur'?'active':'' ?>" href="<?= e(lang_url('ur')) ?>">اردو</a>
        <a class="lang-btn <?= $lang==='ar'?'active':'' ?>" href="<?= e(lang_url('ar')) ?>">عربي</a>
      </div>
    </div>
  </div>
</div>

<!-- Header / Navbar -->
<header class="site-header">
  <div class="container nav">
    <a href="<?= BASE_URL ?>index.php" class="logo">
      <img src="<?= BASE_URL ?>images/logo.png" alt="<?= e(t('brand.name')) ?>" class="logo-img">
      <span class="logo-text">
        <strong><?= e(t('brand.name')) ?></strong>
        <small><?= e(t('brand.tagline')) ?></small>
      </span>
    </a>
    <button class="menu-toggle" id="menuToggle" aria-label="Menu">☰</button>
    <ul class="nav-links" id="navLinks">
      <?php foreach (get_menu() as $item): ?>
        <li><a class="<?= is_active_menu($item['url']) ?>" href="<?= BASE_URL . e($item['url']) ?>"><?= e(t($item['key'])) ?></a></li>
      <?php endforeach; ?>
      <li><a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>admissions.php"><?= e(t('nav.apply')) ?></a></li>
    </ul>
  </div>
</header>
