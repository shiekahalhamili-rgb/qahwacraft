<?php
// Top of every page. Set $title and $active before including this file.
$user = current_user();
$count = cart_count();
$active = $active ?? '';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(($title ?? '') ? $title . ' · Qahwa Craft' : 'Qahwa Craft · Specialty Coffee') ?></title>
<link rel="icon" href="assets/logo.svg">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Instrument+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap">
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="<?= e($bodyClass ?? '') ?>">

<div class="topbar"><div class="wrap">
  <span>One cart, one box, one shipping fee</span>
  <span>Pay with mada or Apple Pay</span>
  <span>IS 332 · Group 5i6</span>
</div></div>

<header class="site">
  <div class="wrap">
    <a class="brand" href="index.php">
      <img class="brand-mark" src="assets/logo.svg" alt="" width="34" height="34">
      <span><span class="brand-name">Qahwa <b>Craft</b></span><span class="brand-tag">SPECIALTY COFFEE</span></span>
    </a>
    <nav class="main">
      <a href="shop.php?type=beans" <?= $active === 'beans' ? 'aria-current="page"' : '' ?>>Coffee beans</a>
      <a href="shop.php?type=tool" <?= $active === 'tool' ? 'aria-current="page"' : '' ?>>Brewing tools</a>
      <a href="subscribe.php" <?= $active === 'subscribe' ? 'aria-current="page"' : '' ?>>Subscriptions</a>
      <a href="track.php" <?= $active === 'track' ? 'aria-current="page"' : '' ?>>Track order</a>
      <a href="contact.php" <?= $active === 'contact' ? 'aria-current="page"' : '' ?>>Contact</a>
    </nav>
    <form class="search" action="shop.php" method="get">
      <input name="q" type="search" placeholder="Search coffee and tools" value="<?= e($_GET['q'] ?? '') ?>">
    </form>
    <div class="head-actions">
      <?php if ($user): ?>
        <div class="userchip">
          <span class="hello">Hi, <b><?= e(explode(' ', $user['name'])[0]) ?></b></span>
          <?php if ($user['role'] === 'admin'): ?><a class="admin-link" href="admin.php">Admin</a><?php endif; ?>
          <a class="signin-link" href="account.php">My account</a>
          <form class="logout-form" method="post" action="logout.php"><?= csrf_field() ?><button class="logout-btn" type="submit">Log out</button></form>
        </div>
      <?php else: ?>
        <a class="signin-link" href="login.php">Sign in</a>
      <?php endif; ?>
      <a class="iconbtn" href="cart.php" aria-label="Cart"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 4h2l2.2 11h11.3L21 7H6.2"/><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/></svg><span class="badge" data-n="<?= $count ?>"><?= $count ?></span></a>
    </div>
  </div>
</header>

<main class="wrap">
<?php if (!empty($_SESSION['flash'])): ?>
  <div class="flashes">
    <?php foreach ($_SESSION['flash'] as $f): ?><div class="flash <?= $f[1] ?>"><?= e($f[0]) ?></div><?php endforeach; ?>
  </div>
  <?php unset($_SESSION['flash']); ?>
<?php endif; ?>
