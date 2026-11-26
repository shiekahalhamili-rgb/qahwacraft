<?php
require 'includes/functions.php';

$back = $_GET['back'] ?? 'account.php';
if (!preg_match('/^[a-z]+\.php$/', $back)) {
    $back = 'account.php';   // only allow pages of this site
}
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_token();
    $st = db()->prepare('SELECT * FROM users WHERE email = ?');
    $st->execute([trim($_POST['email'] ?? '')]);
    $user = $st->fetch();

    if ($user && password_verify($_POST['password'] ?? '', $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        flash('Welcome back, ' . explode(' ', $user['name'])[0] . '!');
        redirect($user['role'] === 'admin' && $back === 'account.php' ? 'admin.php' : $back);
    }
    $error = 'Wrong email or password.';
}

$title = 'Sign in';
include 'includes/header.php';
?>

<div class="auth">
  <h1>Sign in</h1>
  <?php if ($error): ?><ul class="errors"><li><?= $error ?></li></ul><?php endif; ?>
  <form class="panel form" method="post">
    <?= csrf_field() ?>
    <div class="field"><label for="email">Email</label><input id="email" name="email" type="email" required></div>
    <div class="field"><label for="password">Password</label><input id="password" name="password" type="password" required></div>
    <button class="btn" type="submit">Sign in</button>
  </form>

  <div class="panel form">
    <b>Ready-made accounts</b>
    <?php foreach ($ACCOUNTS as $a): ?>
      <div class="acct-card">
        <span class="eyebrow"><?= $a[4] ?></span>
        <b><?= e($a[0]) ?></b>
        <span class="muted"><?= e($a[1]) ?> · <?= e($a[2]) ?></span>
        <?php if ($a[0] === 'Sammer Aldbass'): ?><span class="muted">Discount code: <b>SAMMER20</b> (20% off)</span><?php endif; ?>
        <button class="btn ghost sm" type="button" data-email="<?= e($a[1]) ?>" data-password="<?= e($a[2]) ?>">Use this account</button>
      </div>
    <?php endforeach; ?>
  </div>
  <p>New customer? <a href="register.php">Create an account</a></p>
</div>

<?php include 'includes/footer.php'; ?>
