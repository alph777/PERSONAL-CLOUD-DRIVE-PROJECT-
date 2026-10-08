<?php
require 'config.php';
if (!empty($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pw    = $_POST['password'] ?? '';
    $pw2   = $_POST['confirm'] ?? '';
    if ($name === '' || $email === '' || $pw === '')       $error = 'Fill in every field.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))    $error = 'Enter a valid email address.';
    elseif (strlen($pw) < 6)                               $error = 'Your password needs at least 6 characters.';
    elseif ($pw !== $pw2)                                  $error = 'The two passwords do not match.';
    else {
        $st = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $st->execute([$email]);
        if ($st->fetch()) {
            $error = 'That email already has an account. Log in instead.';
        } else {
            $st = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
            $st->execute([$name, $email, password_hash($pw, PASSWORD_DEFAULT)]);
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$pdo->lastInsertId();
            $_SESSION['user_name'] = $name;
            header('Location: dashboard.php'); exit;
        }
    }
}
page_start('Create account');
?>
<main class="auth">
  <form class="card" method="post" autocomplete="off">
    <a class="brand" href="index.php"><svg viewBox="0 0 64 44" class="logo"><path d="M18 42a14 14 0 0 1-1-28 18 18 0 0 1 34 5 11.5 11.5 0 0 1-2 23z" fill="currentColor"/></svg> AlphCloud</a>
    <h2>Create your drive</h2>
    <?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
    <label>Name<input type="text" name="name" value="<?= e($_POST['name'] ?? '') ?>" required></label>
    <label>Email<input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required></label>
    <label>Password<input type="password" name="password" required></label>
    <label>Confirm password<input type="password" name="confirm" required></label>
    <button class="btn primary full" type="submit">Create account</button>
    <p class="switch">Already have a drive? <a href="login.php">Log in</a></p>
  </form>
</main>
</body></html>
