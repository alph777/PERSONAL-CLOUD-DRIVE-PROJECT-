<?php
require 'config.php';
require_login();
$uid = (int)$_SESSION['user_id'];

$st = $pdo->prepare('SELECT * FROM files WHERE user_id = ? ORDER BY uploaded_at DESC');
$st->execute([$uid]);
$files = $st->fetchAll();

$used = array_sum(array_column($files, 'size'));
$pct  = min(100, round($used / QUOTA * 100, 1));
$first = explode(' ', $_SESSION['user_name'])[0];

page_start('Your files', '<meta name="csrf" content="' . csrf_token() . '">');
?>
<header class="topbar">
  <a class="brand" href="dashboard.php"><svg viewBox="0 0 64 44" class="logo"><path d="M18 42a14 14 0 0 1-1-28 18 18 0 0 1 34 5 11.5 11.5 0 0 1-2 23z" fill="currentColor"/></svg> AlphCloud</a>
  <div class="who"><span><?= e($first) ?></span><a class="btn ghost small" href="logout.php">Log out</a></div>
</header>

<main class="wrap">
  <section class="top">
    <div>
      <h1>Hi <?= e($first) ?>, here are your files</h1>
      <p class="muted" id="usedText"><?= format_size($used) ?> of <?= format_size(QUOTA) ?> used</p>
      <div class="meter"><div class="fill" id="fill" style="--w:<?= $pct ?>%"></div></div>
    </div>
  </section>

  <section class="drop" id="drop" tabindex="0" role="button" aria-label="Upload files">
    <svg viewBox="0 0 64 56" class="up"><path d="M18 42a14 14 0 0 1-1-28 18 18 0 0 1 34 5 11.5 11.5 0 0 1-2 23" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/><path d="M32 52V28m0 0-8 8m8-8 8 8" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
    <div class="drop-text"><b>Drop files here</b><span>or click to choose from your computer</span></div>
    <div class="progress" id="progress"><div class="bar" id="bar"></div><span id="pctText">0%</span></div>
    <input type="file" id="fileInput" multiple hidden>
  </section>

  <section class="tools">
    <input type="search" id="search" placeholder="Search your files" aria-label="Search your files">
    <span class="muted" id="count"><?= count($files) ?> file<?= count($files) === 1 ? '' : 's' ?></span>
  </section>

  <section class="grid" id="grid">
    <?php foreach ($files as $i => $f): ?>
      <article class="file" style="--i:<?= $i ?>" data-id="<?= (int)$f['id'] ?>" data-name="<?= e($f['original_name']) ?>">
        <div class="thumb">
          <?php if (is_image($f['original_name'])): ?>
            <img loading="lazy" alt="" src="download.php?id=<?= (int)$f['id'] ?>&inline=1">
          <?php else: ?>
            <span class="emoji"><?= file_icon($f['original_name']) ?></span>
          <?php endif; ?>
        </div>
        <div class="meta">
          <div class="name" title="<?= e($f['original_name']) ?>"><?= e($f['original_name']) ?></div>
          <div class="sub"><?= format_size($f['size']) ?>, <?= date('d M Y', strtotime($f['uploaded_at'])) ?></div>
        </div>
        <div class="actions">
          <a class="icon-btn" href="download.php?id=<?= (int)$f['id'] ?>" title="Download" aria-label="Download <?= e($f['original_name']) ?>">⬇</a>
          <button class="icon-btn danger del" title="Delete" aria-label="Delete <?= e($f['original_name']) ?>">🗑</button>
        </div>
      </article>
    <?php endforeach; ?>
  </section>

  <div class="empty" id="empty" <?= $files ? 'hidden' : '' ?>>
    <div class="big">☁️</div>
    <p id="emptyMsg">Your drive is empty. Drop a file above to upload your first one.</p>
  </div>
</main>

<div class="modal" id="modal">
  <div class="dialog">
    <h3>Delete this file?</h3>
    <p><b id="modalName"></b> will be removed from your drive for good.</p>
    <div class="row">
      <button class="btn ghost" id="cancel">Keep file</button>
      <button class="btn danger" id="confirm">Delete file</button>
    </div>
  </div>
</div>
<div class="toasts" id="toasts"></div>
<script src="assets/app.js"></script>
</body></html>
