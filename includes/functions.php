<?php
/**
 * Global helper functions
 */

// ---------------- Escape ----------------
function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ---------------- Get setting ----------------
function get_setting($key, $default = '') {
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        $rows = db()->query("SELECT setting_key, setting_value FROM settings")->fetchAll();
        foreach ($rows as $r) $cache[$r['setting_key']] = $r['setting_value'];
    }
    return $cache[$key] ?? $default;
}

// ---------------- Slugify ----------------
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text) ?: 'n-a';
}

// ---------------- Get current language ----------------
function current_lang() {
    if (isset($_GET['lang']) && in_array($_GET['lang'], SUPPORTED_LANGS)) {
        $_SESSION['lang'] = $_GET['lang'];
    }
    if (!empty($_SESSION['lang']) && in_array($_SESSION['lang'], SUPPORTED_LANGS)) {
        return $_SESSION['lang'];
    }
    return DEFAULT_LANG;
}

// ---------------- Translatable field ----------------
function tf($row, $field) {
    $lang = current_lang();
    $key = $field . '_' . $lang;
    if (!empty($row[$key])) return $row[$key];
    $fallback = $field . '_en';
    return $row[$fallback] ?? ($row[$field] ?? '');
}

// ---------------- Redirect ----------------
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

// ---------------- Flash messages ----------------
function set_flash($type, $msg) {
    $_SESSION['flash'][$type] = $msg;
}

function get_flash($type) {
    if (!empty($_SESSION['flash'][$type])) {
        $msg = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $msg;
    }
    return null;
}

// ---------------- Generate application number ----------------
function generate_application_no() {
    return 'ASF-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(3)));
}

// ---------------- Pagination ----------------
function paginate($total, $perPage, $currentPage) {
    $totalPages = max(1, (int)ceil($total / $perPage));
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;
    return [
        'total' => $total,
        'per_page' => $perPage,
        'current' => $currentPage,
        'total_pages' => $totalPages,
        'offset' => $offset
    ];
}

// ---------------- Truncate text ----------------
function excerpt($text, $length = 160) {
    $text = strip_tags($text);
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . '…';
}

// ---------------- Format date ----------------
function format_date($date, $lang = null) {
    if (!$date) return '';
    $lang = $lang ?: current_lang();
    $ts = strtotime($date);
    $formats = [
        'en' => 'F j, Y',
        'ur' => 'j F Y',
        'ar' => 'j F Y'
    ];
    return date($formats[$lang] ?? 'F j, Y', $ts);
}

// ---------------- Get all departments ----------------
function get_departments() {
    return db()->query("SELECT * FROM departments WHERE is_active=1 ORDER BY sort_order")->fetchAll();
}

// ---------------- Get latest news ----------------
function get_latest_news($limit = 6) {
    $stmt = db()->prepare("SELECT * FROM news WHERE status='published' ORDER BY published_at DESC LIMIT ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

// ---------------- Get paginated news ----------------
function get_news_paginated($page = 1, $perPage = 10) {
    $total = (int) db()->query("SELECT COUNT(*) FROM news WHERE status='published'")->fetchColumn();
    $p = paginate($total, $perPage, $page);
    $stmt = db()->prepare("SELECT * FROM news WHERE status='published' ORDER BY published_at DESC LIMIT ? OFFSET ?");
    $stmt->bindValue(1, $p['per_page'], PDO::PARAM_INT);
    $stmt->bindValue(2, $p['offset'], PDO::PARAM_INT);
    $stmt->execute();
    $p['items'] = $stmt->fetchAll();
    return $p;
}

// ---------------- Get single news by slug ----------------
function get_news_by_slug($slug) {
    $stmt = db()->prepare("SELECT * FROM news WHERE slug=? AND status='published' LIMIT 1");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

// ---------------- Increment news views ----------------
function increment_news_views($id) {
    db()->prepare("UPDATE news SET views = views + 1 WHERE id=?")->execute([$id]);
}

// ---------------- Get magazine issues ----------------
function get_magazine_issues($limit = null) {
    $sql = "SELECT * FROM magazine_issues WHERE is_published=1 ORDER BY issue_year DESC, issue_month DESC";
    if ($limit) $sql .= " LIMIT " . (int)$limit;
    return db()->query($sql)->fetchAll();
}

// ---------------- Get public Q&A ----------------
function get_public_questions($limit = 20, $page = 1) {
    $total = (int) db()->query("SELECT COUNT(*) FROM questions WHERE is_public=1 AND status='answered'")->fetchColumn();
    $p = paginate($total, $limit, $page);
    $stmt = db()->prepare("SELECT * FROM questions WHERE is_public=1 AND status='answered' ORDER BY answered_at DESC LIMIT ? OFFSET ?");
    $stmt->bindValue(1, $p['per_page'], PDO::PARAM_INT);
    $stmt->bindValue(2, $p['offset'], PDO::PARAM_INT);
    $stmt->execute();
    $p['items'] = $stmt->fetchAll();
    return $p;
}

// ---------------- Get gallery albums ----------------
function get_gallery_albums() {
    return db()->query("SELECT * FROM gallery_albums ORDER BY sort_order, id DESC")->fetchAll();
}

function get_album_images($albumId) {
    $stmt = db()->prepare("SELECT * FROM gallery_images WHERE album_id=? ORDER BY sort_order, id");
    $stmt->execute([$albumId]);
    return $stmt->fetchAll();
}

// ---------------- Get admission programs ----------------
function get_admission_programs() {
    return db()->query("SELECT * FROM admission_programs WHERE is_active=1 ORDER BY sort_order")->fetchAll();
}

// ---------------- Get question categories ----------------
function get_question_categories() {
    return db()->query("SELECT * FROM question_categories")->fetchAll();
}

// ---------------- Get menu items ----------------
function get_menu() {
    return [
        ['url' => 'index.php',       'key' => 'nav.home'],
        ['url' => 'about.php',       'key' => 'nav.about'],
        ['url' => 'departments.php', 'key' => 'nav.departments'],
        ['url' => 'admissions.php',  'key' => 'nav.admissions'],
        ['url' => 'magazine.php',    'key' => 'nav.magazine'],
        ['url' => 'questions.php',   'key' => 'nav.questions'],
        ['url' => 'gallery.php',     'key' => 'nav.gallery'],
        ['url' => 'news.php',        'key' => 'nav.news'],
        ['url' => 'contact.php',     'key' => 'nav.contact'],
    ];
}

// ---------------- Active menu detection ----------------
function is_active_menu($url) {
    $current = basename($_SERVER['PHP_SELF']);
    return $current === $url ? 'active' : '';
}

// ---------------- Build language URL ----------------
function lang_url($lang) {
    $params = $_GET;
    $params['lang'] = $lang;
    return basename($_SERVER['PHP_SELF']) . '?' . http_build_query($params);
}

// ---------------- CSRF token ----------------
function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function verify_csrf() {
    $token = $_POST['csrf'] ?? '';
    return !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}
