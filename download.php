<?php
require 'config.php';
require_login();
$uid = (int)$_SESSION['user_id'];
$id  = (int)($_GET['id'] ?? 0);

// The WHERE user_id = ? part means you can only ever open your own files.
$st = $pdo->prepare('SELECT * FROM files WHERE id = ? AND user_id = ?');
$st->execute([$id, $uid]);
$f = $st->fetch();
$path = $f ? UPLOAD_DIR . $uid . '/' . $f['stored_name'] : '';

if (!$f || !is_file($path)) { http_response_code(404); die('File not found.'); }

$ext = strtolower(pathinfo($f['original_name'], PATHINFO_EXTENSION));
$imageTypes = ['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','gif'=>'image/gif','webp'=>'image/webp'];
$inline = isset($_GET['inline']) && isset($imageTypes[$ext]);

header('X-Content-Type-Options: nosniff');
header('Content-Type: ' . ($inline ? $imageTypes[$ext] : 'application/octet-stream'));
header('Content-Length: ' . filesize($path));
header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . "; filename*=UTF-8''" . rawurlencode($f['original_name']));
readfile($path);
exit;
