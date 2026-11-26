<?php
require 'includes/functions.php';

$user = require_login('checkout.php');
if (!cart()) {
    redirect('cart.php');
}

// Saves the order in the database and empties the cart. Returns the order number.
function place_order($user, $info)
{
    global $COURIERS;
    $t = cart_totals($info['courier']);
    $pdo = db();
    $tracking = 'QCT ' . rand(1000, 9999) . ' ' . rand(1000, 9999);

    $pdo->prepare('INSERT INTO orders (user_id, name, phone, email, city, address, courier, tracking_no, payment, coupon, subtotal, discount, shipping, total)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
        ->execute([$user['id'], $info['name'], $info['phone'], $info['email'], $info['city'], $info['address'], $info['courier'], $tracking,
                   $info['payment'], $t['coupon'], $t['subtotal'], $t['discount'], $t['shipping'], $t['total']]);
    $orderId = $pdo->lastInsertId();
    $orderNo = 'QC-' . (10000 + $orderId);
    $pdo->prepare('UPDATE orders SET order_no = ? WHERE id = ?')->execute([$orderNo, $orderId]);

    foreach (cart() as $line) {
        $productId = $line['id'] === 'subscription' ? null : $line['id'];
        $pdo->prepare('INSERT INTO order_items (order_id, product_id, name, details, qty, price) VALUES (?, ?, ?, ?, ?, ?)')
            ->execute([$orderId, $productId, $line['name'], $line['details'], $line['qty'], $line['price']]);
        if ($productId) {
            $pdo->prepare('UPDATE products SET stock = GREATEST(stock - ?, 0) WHERE id = ?')->execute([$line['qty'], $productId]);
        } else {
            $pdo->prepare('INSERT INTO subscriptions (user_id, name, details, price, next_date) VALUES (?, ?, ?, ?, ?)')
                ->execute([$user['id'], $line['name'], $line['details'], $line['price'], date('Y-m-d', strtotime('+' . ($line['every'] ?? '1 month')))]);
        }
    }
    // 1 loyalty point for every SAR 1
    $pdo->prepare('UPDATE users SET points = points + ? WHERE id = ?')->execute([floor($t['total']), $user['id']]);

    unset($_SESSION['cart'], $_SESSION['coupon'], $_SESSION['checkout']);
    return $orderNo;
}

$step = $_GET['step'] ?? 'details';
$info = $_SESSION['checkout'] ?? ['name' => $user['name'], 'phone' => $user['phone'], 'email' => $user['email'], 'city' => 'Riyadh', 'address' => '', 'courier' => 'standard', 'payment' => 'mada'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_token();

    if ($step === 'otp') {
        // mada: bank verification code
        if (trim($_POST['otp'] ?? '') === DEMO_OTP) {
            $orderNo = place_order($user, $info);
            redirect('order.php?no=' . $orderNo . '&new=1');
        }
        $errors[] = 'Wrong code. In this demo the code is ' . DEMO_OTP . '.';
    } elseif ($step === 'applepay') {
        // Apple Pay: the customer confirms on the device (Face ID, Touch ID or passcode)
        $orderNo = place_order($user, $info);
        redirect('order.php?no=' . $orderNo . '&new=1');
    } else {
        $info = [
            'name'    => trim($_POST['name'] ?? ''),
            'phone'   => trim($_POST['phone'] ?? ''),
            'email'   => trim($_POST['email'] ?? ''),
            'city'    => trim($_POST['city'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'courier' => 'standard',
            'payment' => isset($PAYMENTS[$_POST['payment'] ?? '']) ? $_POST['payment'] : 'mada',
        ];
        if ($info['name'] === '') $errors[] = 'Enter your name.';
        if (strlen($info['phone']) < 9) $errors[] = 'Enter your mobile number.';
        if (!filter_var($info['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email.';
        if ($info['address'] === '') $errors[] = 'Enter your address.';

        if (!$errors) {
            $_SESSION['checkout'] = $info;
            redirect($info['payment'] === 'mada' ? 'checkout.php?step=otp' : 'checkout.php?step=applepay');
        }
    }
}

$t = cart_totals($info['courier']);
$title = 'Checkout';
$active = 'cart';
include 'includes/header.php';
?>

<h1 style="font-size:clamp(28px,3.6vw,40px);margin-bottom:18px">Checkout</h1>
<?php if ($errors): ?><ul class="errors"><?php foreach ($errors as $err) echo '<li>' . e($err) . '</li>'; ?></ul><?php endif; ?>

<div class="two">
<?php if ($step === 'otp'): ?>
  <form class="panel form" method="post">
    <?= csrf_field() ?>
    <h3>Payment verification</h3>
    <p>Your bank sent a 6-digit code to your phone.</p>
    <div class="note demo">Demo: the code is <b><?= DEMO_OTP ?></b>. No real payment is made.</div>
    <div class="field"><label for="otp">Code</label><input id="otp" name="otp" class="otp" maxlength="6" required autofocus></div>
    <button class="btn" type="submit">Verify and pay <?= money($t['total']) ?></button>
    <a href="checkout.php">← Back</a>
  </form>
<?php elseif ($step === 'applepay'): ?>
  <form class="panel form" method="post">
    <?= csrf_field() ?>
    <h3>Confirm with Apple Pay</h3>
    <p>Check your order and total, then confirm the payment on your device with Face ID, Touch ID or your passcode.</p>
    <div class="note demo">Demo: press the button to confirm. No real payment is made, and your card number is never shared with the store.</div>
    <button class="btn" type="submit">Confirm and pay <?= money($t['total']) ?></button>
    <a href="checkout.php">← Back</a>
  </form>
<?php else: ?>
  <form class="form" method="post">
    <?= csrf_field() ?>
    <div class="panel form"><h3>Delivery details</h3>
      <div class="field"><label for="name">Full name</label><input id="name" name="name" value="<?= e($info['name']) ?>" required></div>
      <div class="row2">
        <div class="field"><label for="phone">Mobile</label><input id="phone" name="phone" value="<?= e($info['phone']) ?>" required></div>
        <div class="field"><label for="email">Email</label><input id="email" name="email" type="email" value="<?= e($info['email']) ?>" required></div>
      </div>
      <div class="row2">
        <div class="field"><label for="city">City</label>
          <select id="city" name="city"><?php foreach (['Riyadh', 'Jeddah', 'Dammam', 'Makkah', 'Madinah', 'Abha', 'Jazan', 'Other'] as $c) echo '<option' . ($info['city'] === $c ? ' selected' : '') . ">$c</option>"; ?></select></div>
        <div class="field"><label for="address">District, street and building</label><input id="address" name="address" value="<?= e($info['address']) ?>" required></div>
      </div>
    </div>

    <div class="panel"><h3>Delivery</h3><div class="radiocards">
      <?php $c = $COURIERS['standard']; ?>
      <label class="rc"><input type="radio" name="courier" value="standard" checked>
        <span class="rc-main"><b><?= $c['name'] ?></b><small><?= $c['time'] ?></small></span><span class="rc-side"><?= $t['shipping'] ? money($t['shipping']) : 'Included' ?></span></label>
    </div></div>

    <div class="panel"><h3>Payment method</h3><div class="radiocards">
      <?php foreach ($PAYMENTS as $key => $name): ?>
        <label class="rc"><input type="radio" name="payment" value="<?= $key ?>" <?= $info['payment'] === $key ? 'checked' : '' ?>><span class="rc-main"><b><?= $name ?></b></span></label>
      <?php endforeach; ?>
    </div></div>

    <!-- Card fields have no name attribute, so card details are never sent to or stored by the store. -->
    <div class="panel form paypanel" data-method="mada">
      <div class="field"><label for="cardno">mada card number</label><input id="cardno" value="5297 4100 0000 0010" readonly></div>
      <div class="row2">
        <div class="field"><label for="cardexp">Expiry</label><input id="cardexp" value="12/29" readonly></div>
        <div class="field"><label for="cardcvv">CVV</label><input id="cardcvv" value="•••" readonly></div>
      </div>
      <div class="note demo">Demo test card. Card details go to the payment gateway and are not saved by Qahwa Craft. Your bank sends a verification code next.</div>
    </div>
    <div class="panel form paypanel" data-method="applepay"><div class="note demo">You confirm the payment on your Apple device with Face ID, Touch ID or your passcode. Your card number is never shared with the store.</div></div>

    <button class="btn" type="submit">Continue</button>
  </form>
<?php endif; ?>

  <aside class="summary panel">
    <h3>Order summary</h3>
    <?php foreach (cart() as $line): ?>
      <div class="sumrow"><span><?= $line['qty'] ?> × <?= e($line['name']) ?></span><span><?= money($line['price'] * $line['qty']) ?></span></div>
    <?php endforeach; ?>
    <?php if ($t['discount']): ?><div class="sumrow" style="color:var(--good)"><span>Coupon <?= e($t['coupon']) ?></span><span>−<?= money($t['discount']) ?></span></div><?php endif; ?>
    <div class="sumrow"><span>Shipping</span><span><?= $t['shipping'] ? money($t['shipping']) : 'Free' ?></span></div>
    <div class="sumrow total"><span>Total</span><span><?= money($t['total']) ?></span></div>
    <span class="muted" style="font-size:13px">Includes 15% VAT: <?= money($t['vat']) ?></span>
  </aside>
</div>

<?php include 'includes/footer.php'; ?>
