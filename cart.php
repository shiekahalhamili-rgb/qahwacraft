<?php
require 'includes/functions.php';

// Adds a line to the cart, or increases its quantity if the same item is already there.
function add_line($line)
{
    $cart = cart();
    foreach ($cart as $i => $old) {
        if ($old['id'] === $line['id'] && $old['details'] === $line['details']) {
            $cart[$i]['qty'] += $line['qty'];
            $_SESSION['cart'] = $cart;
            return;
        }
    }
    $cart[] = $line;
    $_SESSION['cart'] = $cart;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_token();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $p = get_product($_POST['id'] ?? '');
        $qty = max(1, min(20, (int) ($_POST['qty'] ?? 1)));
        if (!$p || $p['stock'] < $qty) {
            flash('Sorry, this product is out of stock.', 'err');
        } else {
            $details = $p['type'] === 'beans' ? roaster_name($p) . ' · ' . ($GRINDS[$_POST['grind'] ?? 'whole'] ?? 'Whole bean') : type_name($p['type']);
            add_line(['id' => $p['id'], 'name' => $p['name'], 'details' => $details, 'price' => (float) $p['price'], 'qty' => $qty]);
            flash($p['name'] . ' added to your cart.');
        }
        $back = $_SERVER['HTTP_REFERER'] ?? '';
        redirect(strpos($back, $_SERVER['HTTP_HOST']) !== false ? $back : 'cart.php');
    }

    if ($action === 'subscribe') {
        $grind = substr($_POST['grind'] ?? '', 0, 30);
        $every = isset($SCHEDULES[$_POST['every'] ?? '']) ? $_POST['every'] : '1 month';
        if (($_POST['plan'] ?? '') === 'ritual') {
            $bean = get_product($_POST['bean'] ?? '');
            if ($bean) {
                add_line(['id' => 'subscription', 'name' => 'Daily Ritual: ' . $bean['name'], 'details' => $SCHEDULES[$every] . ' · ' . $grind, 'price' => round($bean['price'] * 0.9, 2), 'qty' => 1, 'every' => $every]);
            }
        } else {
            $flavor = substr($_POST['flavor'] ?? '', 0, 30);
            add_line(['id' => 'subscription', 'name' => 'Explorer Box', 'details' => $SCHEDULES[$every] . ' · ' . $flavor . ' · ' . $grind, 'price' => 149, 'qty' => 1, 'every' => $every]);
        }
        flash('Subscription added to your cart.');
        redirect('cart.php');
    }

    if ($action === 'plus' || $action === 'minus' || $action === 'remove') {
        $cart = cart();
        $i = (int) $_POST['line'];
        if (isset($cart[$i])) {
            if ($action === 'plus') {
                $cart[$i]['qty']++;
            } elseif ($action === 'minus') {
                $cart[$i]['qty']--;
            }
            if ($action === 'remove' || $cart[$i]['qty'] < 1) {
                unset($cart[$i]);
            }
        }
        $_SESSION['cart'] = array_values($cart);
        redirect('cart.php');
    }

    if ($action === 'coupon') {
        $code = strtoupper(trim($_POST['coupon'] ?? ''));
        if (isset($COUPONS[$code])) {
            $_SESSION['coupon'] = $code;
            flash('Coupon ' . $code . ' applied: ' . ($COUPONS[$code] * 100) . '% off.');
        } else {
            unset($_SESSION['coupon']);
            flash('That coupon code is not valid.', 'err');
        }
        redirect('cart.php');
    }
}

$cart = cart();
$t = cart_totals();
$user = current_user();

$title = 'Your cart';
$active = 'cart';
include 'includes/header.php';
?>

<h1 style="font-size:clamp(28px,3.6vw,40px);margin-bottom:18px">Your cart</h1>

<?php if (!$cart): ?>
  <div class="empty"><h3>Your cart is empty</h3><a class="btn" href="shop.php">Start shopping</a></div>
<?php else: ?>
<div class="two">
  <div class="lines">
    <?php foreach ($cart as $i => $line): ?>
      <?php $p = $line['id'] === 'subscription' ? get_product('gift-box') : get_product($line['id']); ?>
      <div class="line">
        <div><?= $p ? art($p) : '' ?></div>
        <div class="line-info">
          <h3><?= e($line['name']) ?></h3>
          <span class="muted"><?= e($line['details']) ?></span>
          <span><?= money($line['price']) ?> each</span>
        </div>
        <div class="line-side">
          <div class="qty">
            <form method="post"><?= csrf_field() ?><input type="hidden" name="line" value="<?= $i ?>"><button name="action" value="minus">−</button></form>
            <output><?= $line['qty'] ?></output>
            <form method="post"><?= csrf_field() ?><input type="hidden" name="line" value="<?= $i ?>"><button name="action" value="plus">+</button></form>
          </div>
          <b><?= money($line['price'] * $line['qty']) ?></b>
          <form method="post"><?= csrf_field() ?><input type="hidden" name="line" value="<?= $i ?>"><button class="linkbtn" name="action" value="remove">Remove</button></form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <aside class="summary panel">
    <div class="sumrow"><span>Subtotal</span><span><?= money($t['subtotal']) ?></span></div>
    <?php if ($t['discount']): ?><div class="sumrow" style="color:var(--good)"><span>Coupon <?= e($t['coupon']) ?></span><span>−<?= money($t['discount']) ?></span></div><?php endif; ?>
    <div class="sumrow"><span>Shipping (one fee)</span><span><?= $t['shipping'] ? money($t['shipping']) : 'Included' ?></span></div>
    <form class="coupon" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="coupon">
      <input name="coupon" placeholder="Coupon code"><button class="btn ghost sm">Apply</button></form>
    <div class="sumrow total"><span>Total</span><span><?= money($t['total']) ?></span></div>
    <span class="muted" style="font-size:13px">Includes 15% VAT: <?= money($t['vat']) ?></span>
    <a class="btn block" href="checkout.php">Checkout</a>
  </aside>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
