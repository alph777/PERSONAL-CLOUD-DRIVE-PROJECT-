<?php
require 'config.php';
if (!empty($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }
page_start('Welcome');
?>
<main class="landing">
  <div class="brand big"><svg viewBox="0 0 64 44" class="logo"><path d="M18 42a14 14 0 0 1-1-28 18 18 0 0 1 34 5 11.5 11.5 0 0 1-2 23z" fill="currentColor"/></svg> AlphCloud</div>
  <h1>Your files,<br>in your own cloud.</h1>
  <p class="lead">Upload from any device on your network, find files with search, and download them again whenever you need. Every file stays private to your account.</p>
  <div class="row">
    <a class="btn primary" href="register.php">Create your drive</a>
    <a class="btn ghost" href="login.php">Log in</a>
  </div>
  <ul class="points">
    <li><b>Drag and drop</b> uploads with live progress</li>
    <li><b>Private</b> files that only you can open</li>
    <li><b>5 GB</b> of space on your own computer</li>
  </ul>
</main>
</body></html>
