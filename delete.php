<?php
require 'config.php';
header('Content-Type: application/json');

if (empty($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST' || !check_csrf($_POST['csrf'] ?? '')) {
    http_response_code(403);
    echo json_encode(['ok' => false]); exit;
}
$uid = (int)$_SESSION['user_id'];
$id  = (int)($_POST['id'] ?? 0);

$st = $pdo->prepare('SELECT * FROM files WHERE id = ? AND user_id = ?');
$st->execute([$id, $uid]);
$f = $st->fetch();
if (!$f) { echo json_encode(['ok' => false]); exit; }

@unlink(UPLOAD_DIR . $uid . '/' . $f['stored_name']);
$pdo->prepare('DELETE FROM files WHERE id = ?')->execute([$id]);

$used = (int)$pdo->query("SELECT COALESCE(SUM(size),0) FROM files WHERE user_id = $uid")->fetchColumn();
echo json_encode([
    'ok' => true,
    'used_text' => format_size($used) . ' of ' . format_size(QUOTA) . ' used',
    'pct' => min(100, round($used / QUOTA * 100, 1)),
]);
