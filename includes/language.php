<?php
/**
 * Language loader - loads translations from /lang/*.php
 */

function load_lang($lang) {
    static $cache = [];
    if (isset($cache[$lang])) return $cache[$lang];

    $file = BASE_PATH . 'lang/' . $lang . '.php';
    if (!file_exists($file)) {
        $file = BASE_PATH . 'lang/en.php';
    }
    $cache[$lang] = require $file;
    return $cache[$lang];
}

function t($key, $default = null) {
    static $strings = null;
    if ($strings === null) {
        $strings = load_lang(current_lang());
    }
    return $strings[$key] ?? ($default ?? $key);
}

function t_html($key) {
    return e(t($key));
}
