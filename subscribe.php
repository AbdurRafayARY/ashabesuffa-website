<?php
require_once __DIR__ . '/config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $email = clean_input($_POST['email'] ?? '');
    if (valid_email($email)) {
        try {
            db()->prepare("INSERT INTO subscribers (email) VALUES (?) ON DUPLICATE KEY UPDATE is_active=1")
                ->execute([$email]);
            set_flash('success', 'Subscribed successfully!');
        } catch (Exception $e) {
            set_flash('error', 'Subscription failed.');
        }
    }
}
redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL . 'index.php');
