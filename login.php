<?php
require 'config.php';
if (!empty($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pw    = $_POST['password'] ?? '';
    $st = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $st->execute([$email]);
    $u = $st->fetch();
    if ($u && password_verify($pw, $u['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$u['id'];
        $_SESSION['user_name'] = $u['name'];
        header('Location: dashboard.php'); exit;
    }
    $error = 'Wrong email or password. Try again.';
}
page_start('Log in');
?>
<main class="auth">
  <form class="card" method="post">
    <a class="brand" href="index.php"><svg viewBox="0 0 64 44" class="logo"><path d="M18 42a14 14 0 0 1-1-28 18 18 0 0 1 34 5 11.5 11.5 0 0 1-2 23z" fill="currentColor"/></svg> AlphCloud</a>
    <h2>Welcome back</h2>
    <?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
    <label>Email<input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required></label>
    <label>Password<input type="password" name="password" required></label>
    <button class="btn primary full" type="submit">Log in</button>
    <p class="switch">New here? <a href="register.php">Create a drive</a></p>
  </form>
</main>
</body></html>
