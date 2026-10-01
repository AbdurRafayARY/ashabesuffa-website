<?php
/**
 * ============================================================
 * Ashabesuffa Foundation - Admin Entry Point / Dashboard
 * File: admin/index.php
 * ============================================================
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/security.php';

// Not logged in → go to login
if (empty($_SESSION['admin_id'])) {
    redirect(BASE_URL . 'admin/login.php');
}

/* ------------------------------------------------------------
 * Dashboard statistics
 * ---------------------------------------------------------- */
$stats = [
    'news_total'         => (int) db()->query("SELECT COUNT(*) FROM news")->fetchColumn(),
    'news_published'     => (int) db()->query("SELECT COUNT(*) FROM news WHERE status='published'")->fetchColumn(),
    'news_draft'         => (int) db()->query("SELECT COUNT(*) FROM news WHERE status='draft'")->fetchColumn(),

    'mag_issues'         => (int) db()->query("SELECT COUNT(*) FROM magazine_issues")->fetchColumn(),
    'mag_articles'       => (int) db()->query("SELECT COUNT(*) FROM magazine_articles")->fetchColumn(),

    'q_total'            => (int) db()->query("SELECT COUNT(*) FROM questions")->fetchColumn(),
    'q_pending'          => (int) db()->query("SELECT COUNT(*) FROM questions WHERE status='pending'")->fetchColumn(),
    'q_answered'         => (int) db()->query("SELECT COUNT(*) FROM questions WHERE status='answered'")->fetchColumn(),

    'adm_total'          => (int) db()->query("SELECT COUNT(*) FROM admission_applications")->fetchColumn(),
    'adm_pending'        => (int) db()->query("SELECT COUNT(*) FROM admission_applications WHERE status='pending'")->fetchColumn(),
    'adm_approved'       => (int) db()->query("SELECT COUNT(*) FROM admission_applications WHERE status='approved'")->fetchColumn(),

    'messages_unread'    => (int) db()->query("SELECT COUNT(*) FROM contact_messages WHERE is_read=0")->fetchColumn(),
    'messages_total'     => (int) db()->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn(),

    'subs_active'        => (int) db()->query("SELECT COUNT(*) FROM subscribers WHERE is_active=1")->fetchColumn(),

    'donations_count'    => (int) db()->query("SELECT COUNT(*) FROM donations")->fetchColumn(),
    'donations_sum'      => (float) (db()->query("SELECT COALESCE(SUM(amount),0) FROM donations WHERE status='completed'")->fetchColumn() ?: 0),
];

/* ------------------------------------------------------------
 * Recent items
 * ---------------------------------------------------------- */
$recentNews = db()->query("SELECT id, title_en, status, created_at FROM news ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recentQuestions = db()->query("SELECT id, questioner_name, status, created_at FROM questions ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recentApplications = db()->query("SELECT id, application_no, student_name, status, created_at FROM admission_applications ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recentMessages = db()->query("SELECT id, name, email, subject, is_read, created_at FROM contact_messages ORDER BY created_at DESC LIMIT 5")->fetchAll();

/* ------------------------------------------------------------
 * Activity log (if table populated)
 * ---------------------------------------------------------- */
$recentActivity = db()->query("SELECT * FROM activity_log ORDER BY created_at DESC LIMIT 8")->fetchAll();

$adminName = $_SESSION['admin_name'] ?? 'Admin';
$adminRole = $_SESSION['admin_role'] ?? 'editor';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - Ashabesuffa Foundation</title>
<link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>css/admin.css">
<style>
  .dash-hero {
    background: linear-gradient(135deg, #064e3b, #0b6e4f);
    color: #fff; border-radius: 14px; padding: 2rem;
    display: flex; justify-content: space-between; align-items: center;
    gap: 1.5rem; flex-wrap: wrap; margin-bottom: 2rem;
    box-shadow: 0 10px 30px rgba(0,0,0,.15);
  }
  .dash-hero h1 { color: #fff; margin: 0 0 .35rem; }
  .dash-hero p  { color: rgba(255,255,255,.85); margin: 0; }
  .dash-hero .role {
    display: inline-block; padding: .2rem .65rem; border-radius: 999px;
    background: rgba(255,255,255,.18); font-size: .75rem; font-weight: 700;
    text-transform: uppercase; margin-top: .5rem;
  }
  .quick-links { display: flex; gap: .5rem; flex-wrap: wrap; }
  .quick-links .btn { white-space: nowrap; }

  .stat-grid {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem; margin-bottom: 2rem;
  }
  .stat-card {
    background: #fff; padding: 1.25rem 1.35rem; border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,.05); border-top: 4px solid #d4af37;
    display: flex; flex-direction: column; gap: .25rem;
  }
  .stat-card .label { color: #6b7280; font-size: .85rem; font-weight: 600; letter-spacing: .3px; text-transform: uppercase; }
  .stat-card .value { font-size: 2rem; color: #0b6e4f; font-weight: 800; line-height: 1.1; }
  .stat-card .sub   { color: #9ca3af; font-size: .8rem; }
  .stat-card.gold   { border-top-color: #b8941f; }
  .stat-card.green  { border-top-color: #0b6e4f; }
  .stat-card.red    { border-top-color: #ef4444; }
  .stat-card.blue   { border-top-color: #2563eb; }

  .panels {
    display: grid; grid-template-columns: 1.2fr .8fr; gap: 1.5rem;
  }
  @media (max-width: 900px) {
    .panels { grid-template-columns: 1fr; }
  }
  .panel {
    background: #fff; border-radius: 12px; padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,.05);
  }
  .panel h3 {
    margin: 0 0 1rem; color: #064e3b; font-size: 1.05rem;
    display: flex; justify-content: space-between; align-items: center;
  }
  .panel h3 a { font-size: .8rem; font-weight: 600; color: #0b6e4f; }
  .mini-list { display: grid; gap: .6rem; }
  .mini-item {
    padding: .75rem .9rem; background: #f9fafb; border-radius: 8px;
    display: flex; justify-content: space-between; gap: 1rem; align-items: flex-start;
    border-inline-start: 4px solid #d4af37;
  }
  .mini-item .title { font-weight: 700; color: #1f2937; font-size: .9rem; }
  .mini-item .meta  { color: #6b7280; font-size: .78rem; margin-top: .15rem; }
  .mini-item .badge { font-size: .7rem; }
  .empty { color: #9ca3af; font-size: .9rem; padding: 1rem 0; text-align: center; }
</style>
</head>
<body class="admin-body">
<?php require __DIR__ . '/includes/admin-header.php'; ?>
<main class="admin-main">

  <!-- Hero -->
  <div class="dash-hero">
    <div>
      <h1>Welcome back, <?= e($adminName) ?> 👋</h1>
      <p>Here's what's happening at Ashabesuffa Foundation today.</p>
      <span class="role"><?= e($adminRole) ?></span>
    </div>
    <div class="quick-links">
      <a class="btn btn-accent btn-sm" href="<?= BASE_URL ?>admin/news.php?action=new">+ News</a>
      <a class="btn btn-accent btn-sm" href="<?= BASE_URL ?>admin/magazine.php?view=issues&action=new">+ Issue</a>
      <a class="btn btn-accent btn-sm" href="<?= BASE_URL ?>admin/magazine.php?view=articles&action=new">+ Article</a>
      <a class="btn btn-white btn-sm" href="<?= BASE_URL ?>" target="_blank">View Site ↗</a>
    </div>
  </div>

  <!-- Stats grid -->
  <div class="stat-grid">
    <div class="stat-card green">
      <span class="label">News</span>
      <span class="value"><?= $stats['news_total'] ?></span>
      <span class="sub"><?= $stats['news_published'] ?> published · <?= $stats['news_draft'] ?> draft</span>
    </div>

    <div class="stat-card gold">
      <span class="label">Magazine</span>
      <span class="value"><?= $stats['mag_issues'] ?></span>
      <span class="sub"><?= $stats['mag_articles'] ?> articles total</span>
    </div>

    <div class="stat-card blue">
      <span class="label">Q&amp;A</span>
      <span class="value"><?= $stats['q_total'] ?></span>
      <span class="sub"><?= $stats['q_answered'] ?> answered · <?= $stats['q_pending'] ?> pending</span>
    </div>

    <div class="stat-card <?= $stats['adm_pending'] > 0 ? 'red' : 'green' ?>">
      <span class="label">Applications</span>
      <span class="value"><?= $stats['adm_total'] ?></span>
      <span class="sub"><?= $stats['adm_approved'] ?> approved · <?= $stats['adm_pending'] ?> pending</span>
    </div>

    <div class="stat-card <?= $stats['messages_unread'] > 0 ? 'red' : 'gold' ?>">
      <span class="label">Messages</span>
      <span class="value"><?= $stats['messages_total'] ?></span>
      <span class="sub"><?= $stats['messages_unread'] ?> unread</span>
    </div>

    <div class="stat-card green">
      <span class="label">Subscribers</span>
      <span class="value"><?= $stats['subs_active'] ?></span>
      <span class="sub">active newsletter subscribers</span>
    </div>

    <div class="stat-card gold">
      <span class="label">Donations</span>
      <span class="value"><?= number_format($stats['donations_sum'], 0) ?></span>
      <span class="sub">PKR · <?= $stats['donations_count'] ?> donations</span>
    </div>

    <div class="stat-card blue">
      <span class="label">Activity</span>
      <span class="value"><?= count($recentActivity) ?></span>
      <span class="sub">recent actions logged</span>
    </div>
  </div>

  <!-- Panels -->
  <div class="panels">

    <!-- Left: Recent content -->
    <div>
      <div class="panel mb-3" style="margin-bottom:1.5rem;">
        <h3>
          Recent News
          <a href="<?= BASE_URL ?>admin/news.php">View all →</a>
        </h3>
        <?php if (empty($recentNews)): ?>
          <p class="empty">No news items yet.</p>
        <?php else: ?>
          <div class="mini-list">
            <?php foreach ($recentNews as $n): ?>
              <div class="mini-item">
                <div>
                  <div class="title"><?= e($n['title_en']) ?></div>
                  <div class="meta"><?= e(date('M j, Y · H:i', strtotime($n['created_at']))) ?></div>
                </div>
                <span class="badge badge-<?= e($n['status']) ?>"><?= e($n['status']) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="panel mb-3" style="margin-bottom:1.5rem;">
        <h3>
          Recent Questions
          <a href="<?= BASE_URL ?>admin/questions.php">View all →</a>
        </h3>
        <?php if (empty($recentQuestions)): ?>
          <p class="empty">No questions yet.</p>
        <?php else: ?>
          <div class="mini-list">
            <?php foreach ($recentQuestions as $q): ?>
              <div class="mini-item">
                <div>
                  <div class="title"><?= e($q['questioner_name'] ?: 'Anonymous') ?></div>
                  <div class="meta"><?= e(date('M j, Y · H:i', strtotime($q['created_at']))) ?></div>
                </div>
                <span class="badge <?= $q['status']==='pending' ? 'badge-draft' : 'badge-published' ?>"><?= e($q['status']) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="panel">
        <h3>
          Recent Applications
          <a href="<?= BASE_URL ?>admin/admissions.php">View all →</a>
        </h3>
        <?php if (empty($recentApplications)): ?>
          <p class="empty">No applications yet.</p>
        <?php else: ?>
          <div class="mini-list">
            <?php foreach ($recentApplications as $a): ?>
              <div class="mini-item">
                <div>
                  <div class="title"><?= e($a['student_name']) ?></div>
                  <div class="meta"><?= e($a['application_no']) ?> · <?= e(date('M j, Y', strtotime($a['created_at']))) ?></div>
                </div>
                <span class="badge"><?= e($a['status']) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Right: Messages + Activity -->
    <div>
      <div class="panel mb-3" style="margin-bottom:1.5rem;">
        <h3>
          Recent Messages
          <a href="<?= BASE_URL ?>admin/messages.php">View all →</a>
        </h3>
        <?php if (empty($recentMessages)): ?>
          <p class="empty">No messages yet.</p>
        <?php else: ?>
          <div class="mini-list">
            <?php foreach ($recentMessages as $m): ?>
              <div class="mini-item" style="border-inline-start-color: <?= $m['is_read'] ? '#d4af37' : '#ef4444' ?>;">
                <div>
                  <div class="title"><?= e($m['name']) ?></div>
                  <div class="meta"><?= e($m['email']) ?></div>
                  <div class="meta"><?= e($m['subject'] ?: '(no subject)') ?></div>
                </div>
                <?php if (!$m['is_read']): ?>
                  <span class="badge badge-draft">new</span>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="panel">
        <h3>Recent Activity</h3>
        <?php if (empty($recentActivity)): ?>
          <p class="empty">No activity logged yet.</p>
        <?php else: ?>
          <div class="mini-list">
            <?php foreach ($recentActivity as $a): ?>
              <div class="mini-item" style="border-inline-start-color:#0b6e4f;">
                <div>
                  <div class="title"><?= e($a['action']) ?></div>
                  <div class="meta">
                    <?= e($a['entity'] ?? '') ?><?= $a['entity_id'] ? ' #' . (int)$a['entity_id'] : '' ?>
                    · <?= e(date('M j · H:i', strtotime($a['created_at']))) ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div>

</main>
<?php require __DIR__ . '/includes/admin-footer.php'; ?>
