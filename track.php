<?php
require 'includes/functions.php';

$user = current_user();
$number = strtoupper(trim($_GET['no'] ?? ''));
$email = trim($_GET['email'] ?? '');
$order = null;

if ($number !== '') {
    $st = db()->prepare('SELECT * FROM orders WHERE order_no = ?');
    $st->execute([$number]);
    $found = $st->fetch();
    // Show the order to its owner, an admin, or anyone who knows the order's email.
    $isOwner = $user && ($found['user_id'] ?? 0) == $user['id'];
    $isAdmin = $user && $user['role'] === 'admin';
    if ($found && ($isOwner || $isAdmin || strcasecmp($found['email'], $email) === 0)) {
        $order = $found;
    }
}

$title = 'Track order';
$active = 'track';
include 'includes/header.php';
?>

<h1 style="font-size:clamp(30px,4vw,44px)">Track your order</h1>
<form class="trackform" method="get" style="margin:16px 0">
  <input name="no" placeholder="Order number, e.g. QC-10001" value="<?= e($number) ?>" required>
  <?php if (!$user): ?><input name="email" type="email" placeholder="Email used for the order" value="<?= e($email) ?>" style="text-transform:none"><?php endif; ?>
  <button class="btn">Track</button>
</form>

<?php if ($number !== '' && !$order): ?>
  <div class="empty"><h3>Order not found</h3><p class="muted">Check the order number<?= $user ? '' : ' and email' ?>.</p></div>
<?php elseif ($order): ?>
  <?php $current = array_search($order['status'], $STATUSES); ?>
  <div class="panel">
    <div class="eyebrow">Order <?= e($order['order_no']) ?> · <?= date('j M Y', strtotime($order['created_at'])) ?></div>
    <h2 style="margin:6px 0 18px"><?= e($order['status']) ?></h2>
    <ol class="timeline">
      <?php foreach ($STATUSES as $i => $status): ?>
        <li class="<?= $i < $current || $order['status'] === 'Delivered' ? 'done' : ($i == $current ? 'now' : '') ?>">
          <span class="dot"><?= $i <= $current ? '✓' : '' ?></span>
          <div><b><?= $status ?></b></div>
        </li>
      <?php endforeach; ?>
    </ol>
    <p class="muted" style="margin-top:16px"><?= e($COURIERS[$order['courier']]['name'] ?? 'Standard delivery') ?> · tracking number <?= e($order['tracking_no']) ?> · <?= e($COURIERS[$order['courier']]['time'] ?? '') ?></p>
  </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
