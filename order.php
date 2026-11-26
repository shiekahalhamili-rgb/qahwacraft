<?php
require 'includes/functions.php';

$user = require_login('account.php');
$st = db()->prepare('SELECT * FROM orders WHERE order_no = ? AND user_id = ?');
$st->execute([$_GET['no'] ?? '', $user['id']]);
$order = $st->fetch();
if (!$order) {
    redirect('account.php');
}
$st = db()->prepare('SELECT * FROM order_items WHERE order_id = ?');
$st->execute([$order['id']]);
$items = $st->fetchAll();

$title = 'Order ' . $order['order_no'];
include 'includes/header.php';
?>

<div class="success">
  <?php if (isset($_GET['new'])): ?>
    <div class="check">✓</div>
    <h1>Thank you, <?= e(explode(' ', $order['name'])[0]) ?>! Your order is confirmed.</h1>
  <?php else: ?>
    <h1>Order <?= e($order['order_no']) ?></h1>
  <?php endif; ?>
  <p>Order <b><?= e($order['order_no']) ?></b> · <?= money($order['total']) ?> paid with <?= e($PAYMENTS[$order['payment']]) ?> · <?= e($COURIERS[$order['courier']]['name'] ?? 'Standard delivery') ?> · tracking <?= e($order['tracking_no']) ?></p>

  <div class="panel" style="width:100%">
    <h3>Electronic invoice</h3>
    <p class="muted" style="margin:0 0 8px">Invoice <?= e($order['order_no']) ?> · <?= date('j M Y, H:i', strtotime($order['created_at'])) ?> · Qahwa Craft, Saudi Arabia</p>
    <?php foreach ($items as $item): ?>
      <div class="sumrow"><span><?= $item['qty'] ?> × <?= e($item['name']) ?><br><small class="muted"><?= e($item['details']) ?></small></span><span><?= money($item['price'] * $item['qty']) ?></span></div>
    <?php endforeach; ?>
    <?php if ($order['discount'] > 0): ?><div class="sumrow"><span>Coupon <?= e($order['coupon']) ?></span><span>−<?= money($order['discount']) ?></span></div><?php endif; ?>
    <div class="sumrow"><span>Shipping (one box)</span><span><?= $order['shipping'] > 0 ? money($order['shipping']) : 'Included' ?></span></div>
    <div class="sumrow total"><span>Total</span><span><?= money($order['total']) ?></span></div>
    <span class="muted" style="font-size:13px">Includes 15% VAT: <?= money(round($order['total'] * 15 / 115, 2)) ?></span>
  </div>

  <div class="buyrow">
    <a class="btn" href="track.php?no=<?= e($order['order_no']) ?>">Track this order</a>
    <a class="btn ghost" href="shop.php">Continue shopping</a>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
