<?php
require 'includes/functions.php';

$user = require_login();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_token();
    $action = $_POST['action'] ?? '';

    if (in_array($action, ['Active', 'Paused', 'Cancelled'])) {
        db()->prepare('UPDATE subscriptions SET status = ? WHERE id = ? AND user_id = ?')
            ->execute([$action, (int) $_POST['id'], $user['id']]);
        flash('Subscription updated.');
        redirect('account.php');
    }

    if ($action === 'profile') {
        db()->prepare('UPDATE users SET name = ?, phone = ? WHERE id = ?')
            ->execute([trim($_POST['name']), trim($_POST['phone']), $user['id']]);
        flash('Profile saved.');
        redirect('account.php');
    }

    if ($action === 'password') {
        if (!password_verify($_POST['current'] ?? '', $user['password'])) {
            $errors[] = 'Your current password is wrong.';
        } elseif (strlen($_POST['new'] ?? '') < 8) {
            $errors[] = 'The new password needs at least 8 characters.';
        } else {
            db()->prepare('UPDATE users SET password = ? WHERE id = ?')
                ->execute([password_hash($_POST['new'], PASSWORD_DEFAULT), $user['id']]);
            flash('Password changed.');
            redirect('account.php');
        }
    }
}

$st = db()->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC');
$st->execute([$user['id']]);
$orders = $st->fetchAll();

$st = db()->prepare('SELECT * FROM subscriptions WHERE user_id = ? ORDER BY id DESC');
$st->execute([$user['id']]);
$subscriptions = $st->fetchAll();

$title = 'My account';
$active = 'account';
include 'includes/header.php';
?>

<div class="sec-head">
  <h1 style="font-size:clamp(30px,4vw,44px)">Hello, <?= e(explode(' ', $user['name'])[0]) ?></h1>
  <span class="pill info"><?= $user['points'] ?> Qahwa Points</span>
</div>
<?php if ($errors): ?><ul class="errors"><?php foreach ($errors as $err) echo '<li>' . e($err) . '</li>'; ?></ul><?php endif; ?>

<section>
  <h2 style="margin-bottom:12px">My orders</h2>
  <?php if (!$orders): ?><p class="muted">No orders yet. <a href="shop.php">Start shopping</a></p><?php endif; ?>
  <div class="list">
    <?php foreach ($orders as $o): ?>
      <div class="item">
        <div class="grow"><b><?= e($o['order_no']) ?></b><span class="muted"><?= date('j M Y', strtotime($o['created_at'])) ?> · <?= money($o['total']) ?></span></div>
        <span class="pill <?= $o['status'] === 'Delivered' ? 'good' : 'info' ?>"><?= e($o['status']) ?></span>
        <a class="btn ghost sm" href="order.php?no=<?= e($o['order_no']) ?>">Details</a>
        <a class="btn ghost sm" href="track.php?no=<?= e($o['order_no']) ?>">Track</a>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section>
  <h2 style="margin-bottom:12px">My subscriptions</h2>
  <?php if (!$subscriptions): ?><p class="muted">No subscriptions. <a href="subscribe.php">See plans</a></p><?php endif; ?>
  <div class="list">
    <?php foreach ($subscriptions as $s): ?>
      <div class="item">
        <div class="grow"><b><?= e($s['name']) ?></b><span class="muted"><?= e($s['details']) ?> · <?= money($s['price']) ?> · next <?= date('j M Y', strtotime($s['next_date'])) ?></span></div>
        <span class="pill <?= $s['status'] === 'Active' ? 'good' : ($s['status'] === 'Paused' ? 'warn' : 'bad') ?>"><?= $s['status'] ?></span>
        <?php if ($s['status'] !== 'Cancelled'): ?>
          <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $s['id'] ?>">
            <?php if ($s['status'] === 'Active'): ?><button class="btn ghost sm" name="action" value="Paused">Pause</button><?php else: ?><button class="btn ghost sm" name="action" value="Active">Resume</button><?php endif; ?>
            <button class="btn ghost sm" name="action" value="Cancelled">Cancel</button>
          </form>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="two">
  <form class="panel form" method="post">
    <?= csrf_field() ?><input type="hidden" name="action" value="profile">
    <h3>Profile</h3>
    <div class="field"><label for="name">Name</label><input id="name" name="name" value="<?= e($user['name']) ?>"></div>
    <div class="field"><label for="phone">Mobile</label><input id="phone" name="phone" value="<?= e($user['phone']) ?>"></div>
    <p class="muted">Email: <?= e($user['email']) ?></p>
    <button class="btn">Save</button>
  </form>
  <form class="panel form" method="post">
    <?= csrf_field() ?><input type="hidden" name="action" value="password">
    <h3>Change password</h3>
    <div class="field"><label for="current">Current password</label><input id="current" name="current" type="password" required></div>
    <div class="field"><label for="new">New password</label><input id="new" name="new" type="password" required></div>
    <button class="btn">Change password</button>
  </form>
</section>

<?php include 'includes/footer.php'; ?>
