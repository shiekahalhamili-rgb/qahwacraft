<?php
require 'includes/functions.php';

$p = get_product($_GET['id'] ?? '');
if (!$p) {
    flash('Product not found.', 'err');
    redirect('shop.php');
}
$roastNames = ['Light', 'Light-medium', 'Medium', 'Medium-dark', 'Dark'];

$title = $p['name'];
$active = $p['type'] === 'beans' ? 'beans' : 'tool';
include 'includes/header.php';
?>

<p class="crumbs"><a href="shop.php">Shop</a> / <?= e($p['name']) ?></p>

<div class="pdp">
  <div><?= art($p) ?></div>
  <div class="pdp-info">
    <div>
      <div class="eyebrow"><?= e($p['type'] === 'beans' ? roaster_name($p) : type_name($p['type'])) ?></div>
      <h1><?= e($p['name']) ?></h1>
    </div>
    <div class="pdp-price"><?= money($p['price']) ?> <?php if ($p['weight']): ?><span class="unit">/ <?= e($p['weight']) ?></span><?php endif; ?> <span style="margin-left:auto"><?= stock_label($p) ?></span></div>
    <p><?= e($p['description']) ?></p>

    <form class="form" method="post" action="cart.php">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add">
      <input type="hidden" name="id" value="<?= e($p['id']) ?>">

      <?php if ($p['type'] === 'beans'): ?>
        <dl class="specs">
          <dt>Origin</dt><dd><?= e($p['origin']) ?></dd>
          <dt>Process</dt><dd><?= e($p['process']) ?></dd>
          <dt>Roast</dt><dd><?= $roastNames[$p['roast_level']] ?></dd>
          <dt>Tasting notes</dt><dd><?= e($p['notes']) ?></dd>
        </dl>
        <div class="field"><span>Grind</span>
          <div class="opts">
            <?php foreach ($GRINDS as $key => $label): ?>
              <label class="opt"><input type="radio" name="grind" value="<?= $key ?>" <?= $key === 'whole' ? 'checked' : '' ?>><span><?= $label ?></span></label>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <div class="buyrow">
        <label>Quantity <input type="number" name="qty" value="1" min="1" max="20" style="width:70px;padding:8px;border:1px solid var(--line);border-radius:8px"></label>
        <button class="btn" type="submit" <?= $p['stock'] <= 0 ? 'disabled' : '' ?>><?= $p['stock'] <= 0 ? 'Out of stock' : 'Add to cart' ?></button>
      </div>
    </form>

    <?php if ($p['type'] === 'beans'): ?>
      <a class="btn ghost" href="subscribe.php?bean=<?= e($p['id']) ?>">Subscribe and save 10%</a>
    <?php endif; ?>
    <div class="note">Ships in the same box as the rest of your order, with one shipping fee of SAR <?= SHIPPING_FEE ?>.</div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
