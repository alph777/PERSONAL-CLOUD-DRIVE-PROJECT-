<?php
// ---------- Settings ----------
session_start();
const DB_HOST = 'localhost';
const DB_NAME = 'cloud_drive';
const DB_USER = 'root';      // XAMPP default
const DB_PASS = '';          // XAMPP default (empty)
const UPLOAD_DIR = __DIR__ . '/uploads/';
const QUOTA = 5 * 1024 * 1024*1024; // 500 MB per user

// ---------- Database connection (PDO) ----------
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $ex) {
    die('Database connection failed. Check that MySQL is running in XAMPP and that you imported database.sql.');
}

// ---------- Helpers ----------
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function require_login() {
    if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }
}

function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function check_csrf($t) { return hash_equals($_SESSION['csrf'] ?? '', (string)$t); }

function format_size($bytes) {
    $u = ['B', 'KB', 'MB', 'GB']; $i = 0;
    while ($bytes >= 1024 && $i < 3) { $bytes /= 1024; $i++; }
    return round($bytes, $i ? 1 : 0) . ' ' . $u[$i];
}

function file_icon($name) {
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $map = [
        'pdf'=>'📕','doc'=>'📘','docx'=>'📘','xls'=>'📗','xlsx'=>'📗','csv'=>'📗','ppt'=>'📙','pptx'=>'📙',
        'txt'=>'📄','zip'=>'🗜️','rar'=>'🗜️','7z'=>'🗜️','mp3'=>'🎵','wav'=>'🎵','mp4'=>'🎬','mkv'=>'🎬','mov'=>'🎬',
        'php'=>'🧩','html'=>'🧩','css'=>'🧩','js'=>'🧩','py'=>'🐍','java'=>'☕','c'=>'🧩','cpp'=>'🧩',
    ];
    return $map[$ext] ?? '📁';
}
function is_image($name) {
    return in_array(strtolower(pathinfo($name, PATHINFO_EXTENSION)), ['jpg','jpeg','png','gif','webp'], true);
}

// ---------- Shared page start (head + drifting clouds) ----------
function page_start($title, $extraHead = '') {
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">'
       . '<meta name="viewport" content="width=device-width, initial-scale=1">'
       . '<title>' . e($title) . ' · AlphCloud</title>'
       . '<link rel="preconnect" href="https://fonts.googleapis.com">'
       . '<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@500;700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">'
       . '<link rel="stylesheet" href="assets/style.css">' . $extraHead . '</head><body>'
       . '<div class="sky" aria-hidden="true">'
       . '<i class="cloud" style="top:8%;--s:1.6;animation-duration:70s;animation-delay:-20s"></i>'
       . '<i class="cloud" style="top:28%;--s:1;animation-duration:50s;animation-delay:-5s"></i>'
       . '<i class="cloud" style="top:55%;--s:2.1;animation-duration:95s;animation-delay:-60s"></i>'
       . '<i class="cloud" style="top:75%;--s:1.2;animation-duration:60s;animation-delay:-35s"></i>'
       . '</div>';
}
