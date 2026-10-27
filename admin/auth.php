<?php
if (session_status() === PHP_SESSION_NONE) session_start();
// Identifiants dans config.php (non versionné, voir config.example.php)
if (file_exists(__DIR__ . '/config.php')) require_once __DIR__ . '/config.php';
define('DATA_DIR', __DIR__ . '/../data/');

function checkCredentials($username, $password) {
    if (!defined('ADMIN_USER') || !defined('ADMIN_PASS') || ADMIN_PASS === '') return false;
    return hash_equals(ADMIN_USER, (string)$username) && hash_equals(ADMIN_PASS, (string)$password);
}

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
