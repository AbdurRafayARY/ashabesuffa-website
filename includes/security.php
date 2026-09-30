<?php
/**
 * Security helpers
 */

// Rate limiting per IP for forms
function rate_limit($key, $max = 5, $window = 300) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $file = sys_get_temp_dir() . '/asf_rl_' . md5($key . $ip);
    $now = time();
    $data = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
    $data = array_filter($data ?: [], fn($t) => $t > $now - $window);
    if (count($data) >= $max) return false;
    $data[] = $now;
    file_put_contents($file, json_encode($data));
    return true;
}

// Validate email
function valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Validate phone
function valid_phone($phone) {
    return preg_match('/^[0-9+\-\s()]{7,30}$/', $phone);
}

// Clean input
function clean_input($str) {
    return trim(strip_tags((string)$str));
}

// Allowed upload mime types
function allowed_image_mimes() {
    return ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
}

function allowed_doc_mimes() {
    return ['application/pdf' => 'pdf'];
}

// Secure file upload
function upload_file($fileInput, $allowed, $subdir = '') {
    if (empty($_FILES[$fileInput]) || $_FILES[$fileInput]['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'No file uploaded or upload error.'];
    }
    $f = $_FILES[$fileInput];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($f['tmp_name']);
    if (!isset($allowed[$mime])) {
        return ['ok' => false, 'error' => 'Invalid file type: ' . e($mime)];
    }
    if ($f['size'] > 8 * 1024 * 1024) {
        return ['ok' => false, 'error' => 'File too large (max 8 MB).'];
    }
    $ext = $allowed[$mime];
    $name = bin2hex(random_bytes(12)) . '.' . $ext;
    $dir = UPLOAD_DIR . trim($subdir, '/') . '/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    if (!move_uploaded_file($f['tmp_name'], $dir . $name)) {
        return ['ok' => false, 'error' => 'Failed to move uploaded file.'];
    }
    return ['ok' => true, 'path' => trim($subdir, '/') . '/' . $name];
}

// Check admin login
function require_admin() {
    if (empty($_SESSION['admin_id'])) {
        redirect(BASE_URL . 'admin/login.php');
    }
}
