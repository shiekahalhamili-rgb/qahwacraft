<?php
require 'includes/functions.php';

$beans = db()->query("SELECT * FROM products WHERE type = 'beans' ORDER BY name")->fetchAll();
$chosenBean = $_GET['bean'] ?? '';

$title = 'Subscriptions';
$active = 'subscribe';
include 'includes/header.php';
?>

<h1 style="font-size:clamp(30px,4vw,44px)">Coffee subscriptions</h1>
<p class="muted" style="margin:8px 0 20px">Freshly roasted coffee matched to your flavor, on the schedule you choose. Delivery is included, and you can pause or cancel any time from My Account. Pay with mada or Apple Pay.</p>

<div class="two">
  <form class="panel form" method="post" action="cart.php">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="subscribe">
    <h3>Explorer Box · SAR 149 per delivery</h3>
    <p class="muted">Two 250 g bags from two different roasters in every box, chosen by flavor.</p>
    <div class="field"><label for="flavor">Flavor</label>
      <select id="flavor" name="flavor"><option>Fruity</option><option>Floral</option><option>Chocolatey</option></select></div>
    <div class="field"><label for="grind1">Grind</label>
      <select id="grind1" name="grind"><?php foreach ($GRINDS as $label) echo "<option>$label</option>"; ?></select></div>
    <div class="field"><label for="every1">Delivery schedule</label>
      <select id="every1" name="every"><?php foreach ($SCHEDULES as $key => $label) echo "<option value=\"$key\">$label</option>"; ?></select></div>
    <input type="hidden" name="plan" value="explorer">
    <button class="btn" type="submit">Add Explorer Box to cart</button>
  </form>

  <form class="panel form" method="post" action="cart.php">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="subscribe">
    <input type="hidden" name="plan" value="ritual">
    <h3>Daily Ritual · 10% off</h3>
    <p class="muted">Your favourite coffee, 250 g, on your schedule.</p>
    <div class="field"><label for="bean">Coffee</label>
      <select id="bean" name="bean">
        <?php foreach ($beans as $b): ?>
          <option value="<?= e($b['id']) ?>" <?= $b['id'] === $chosenBean ? 'selected' : '' ?>><?= e($b['name']) ?> · <?= money($b['price'] * 0.9) ?></option>
        <?php endforeach; ?>
      </select></div>
    <div class="field"><label for="grind2">Grind</label>
      <select id="grind2" name="grind"><?php foreach ($GRINDS as $label) echo "<option>$label</option>"; ?></select></div>
    <div class="field"><label for="every2">Delivery schedule</label>
      <select id="every2" name="every"><?php foreach ($SCHEDULES as $key => $label) echo "<option value=\"$key\">$label</option>"; ?></select></div>
    <button class="btn" type="submit">Add Daily Ritual to cart</button>
  </form>
</div>

<?php include 'includes/footer.php'; ?>
