<?php
session_start();
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'Ibce2026!');
define('DATA_DIR', __DIR__ . '/../data/');

function requireAuth() {
    if (empty($_SESSION['ibce_admin'])) {
        header('Location: login.php');
        exit;
    }
}

function getNews() {
    $file = DATA_DIR . 'news.json';
    if (!file_exists($file)) return [];
    return json_decode(file_get_contents($file), true) ?? [];
}

function saveNews($news) {
    file_put_contents(DATA_DIR . 'news.json', json_encode(array_values($news), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function getStats() {
    $file = DATA_DIR . 'stats.json';
    if (!file_exists($file)) return ['hectares'=>1200,'arbres'=>75000,'especes'=>180,'familles'=>320];
    return json_decode(file_get_contents($file), true) ?? [];
}

function saveStats($stats) {
    file_put_contents(DATA_DIR . 'stats.json', json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
