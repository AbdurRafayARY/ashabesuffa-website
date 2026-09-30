<?php
require_once __DIR__ . '/../config/config.php';
session_destroy();
redirect(BASE_URL . 'admin/login.php');
