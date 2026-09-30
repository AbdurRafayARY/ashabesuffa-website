<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/security.php';
require_admin();

$stats = [
    'news' => (int) db()->query("SELECT COUNT(*) FROM news")->fetchColumn(),
    'published_news' => (int) db()->query("SELECT COUNT(*) FROM news WHERE status='published'")->fetchColumn(),
    'questions' => (int) db()->query("SELECT COUNT(*) FROM questions")->fetchColumn(),
    'pending_questions' => (int) db()->query("SELECT COUNT(*) FROM questions WHERE status='pending'")->fetchColumn(),
    'applications' => (int) db()->query("SELECT COUNT(*) FROM admission_applications")->fetchColumn(),
    'pending_applications' => (int) db()->query("SELECT COUNT(*) FROM admission_applications WHERE status='pending'")->fetchColumn(),
    'messages' => (int) db()->query("SELECT COUNT(*) FROM contact_messages WHERE is_read=0")->fetchColumn(),
    'subscribers' => (int) db()->query("SELECT COUNT(*) FROM subscribers WHERE is_active=1")->fetchColumn(),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - Admin</title>
<link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>css/admin.css">
</head>
<body class="admin-body">
<?php require __DIR__ . '/includes/admin-header.php'; ?>
<main class="admin-main">
  <h1>Dashboard</h1>
  <div class="admin-stats">
    <div class="stat-card"><strong><?= $stats['news'] ?></strong><span>Total News</span></div>
    <div class="stat-card"><strong><?= $stats['published_news'] ?></strong><span>Published</span></div>
    <div class="stat-card"><strong><?= $stats['questions'] ?></strong><span>Total Q&A</span></div>
    <div class="stat-card"><strong><?= $stats['pending_questions'] ?></strong><span>Pending Q&A</span></div>
    <div class="stat-card"><strong><?= $stats['applications'] ?></strong><span>Applications</span></div>
    <div class="stat-card"><strong><?= $stats['pending_applications'] ?></strong><span>Pending Apps</span></div>
    <div class="stat-card"><strong><?= $stats['messages'] ?></strong><span>Unread Messages</span></div>
    <div class="stat-card"><strong><?= $stats['subscribers'] ?></strong><span>Subscribers</span></div>
  </div>
</main>
<?php require __DIR__ . '/includes/admin-footer.php'; ?>
