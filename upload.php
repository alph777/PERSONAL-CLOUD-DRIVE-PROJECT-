<?php
require 'config.php';
header('Content-Type: application/json');

function out($arr, $code = 200) { http_response_code($code); echo json_encode($arr); exit; }

if (empty($_SESSION['user_id'])) out(['ok' => false, 'error' => 'Your session ended. Log in again.'], 401);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(['ok' => false, 'error' => 'Invalid request.'], 405);
// If the upload is bigger than post_max_size, PHP drops everything, including $_POST and $_FILES.
if (empty($_FILES['files'])) out(['ok' => false, 'error' => 'Nothing arrived. The file may be bigger than the PHP upload limit (see the README).']);
if (!check_csrf($_POST['csrf'] ?? '')) out(['ok' => false, 'error' => 'Security check failed. Refresh the page.'], 403);

$uid = (int)$_SESSION['user_id'];
$used = (int)$pdo->query("SELECT COALESCE(SUM(size),0) FROM files WHERE user_id = $uid")->fetchColumn();

$dir = UPLOAD_DIR . $uid . '/';
if (!is_dir($dir)) mkdir($dir, 0777, true);

$uploaded = 0;
$errors = [];
$ins = $pdo->prepare('INSERT INTO files (user_id, original_name, stored_name, size, mime) VALUES (?, ?, ?, ?, ?)');

foreach ($_FILES['files']['name'] as $i => $rawName) {
    $name = basename($rawName);
    $err  = $_FILES['files']['error'][$i];
    $tmp  = $_FILES['files']['tmp_name'][$i];
    $size = (int)$_FILES['files']['size'][$i];

    if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) { $errors[] = "$name is too big for the server limit."; continue; }
    if ($err !== UPLOAD_ERR_OK) { $errors[] = "$name could not be uploaded."; continue; }
    if ($used + $size > QUOTA) { $errors[] = "$name does not fit in your remaining space."; continue; }

    // Random name with no extension: nobody can guess it or run it as code.
    $stored = bin2hex(random_bytes(16));
    if (move_uploaded_file($tmp, $dir . $stored)) {
        $mime = function_exists('mime_content_type') ? (mime_content_type($dir . $stored) ?: 'application/octet-stream') : 'application/octet-stream';
        $ins->execute([$uid, $name, $stored, $size, $mime]);
        $used += $size;
        $uploaded++;
    } else {
        $errors[] = "$name could not be saved.";
    }
}
out(['ok' => $uploaded > 0, 'uploaded' => $uploaded, 'errors' => $errors]);
