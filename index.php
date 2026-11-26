<?php
require 'includes/functions.php';

$featured = db()->query("SELECT * FROM products WHERE type = 'beans' AND featured = 1")->fetchAll();
$tools = db()->query("SELECT * FROM products WHERE type = 'tool' AND featured = 1")->fetchAll();
$heroBags = db()->query("SELECT * FROM products WHERE type = 'beans' AND featured = 1 LIMIT 3")->fetchAll();

$title = '';
$active = 'home';
include 'includes/header.php';
?>

<section class="hero">
  <div><?= content('home-hero') ?></div>
  <div class="crate">
    <span class="crate-tag">3 roasters → 1 shipment</span>
    <div class="crate-bags"><?php foreach ($heroBags as $p) echo art($p); ?></div>
    <div class="crate-box"><span>QAHWA CRAFT · 1 BOX</span></div>
  </div>
</section>

<section><?= content('home-how') ?></section>

<section>
  <div class="sec-head"><h2>Shop by flavor</h2></div>
  <div class="flavors">
    <a class="flavor" data-f="fruity" href="shop.php?flavor=fruity"><h3>Fruity</h3><span class="muted">Apricot, blueberry, blackcurrant</span><span class="go">See coffees →</span></a>
    <a class="flavor" data-f="floral" href="shop.php?flavor=floral"><h3>Floral</h3><span class="muted">Jasmine, rose, bergamot</span><span class="go">See coffees →</span></a>
    <a class="flavor" data-f="chocolatey" href="shop.php?flavor=chocolatey"><h3>Chocolatey</h3><span class="muted">Cocoa, hazelnut, toffee</span><span class="go">See coffees →</span></a>
  </div>
</section>

<section>
  <div class="sec-head"><h2>Fresh this week</h2><a class="btn ghost sm" href="shop.php?type=beans">All coffee</a></div>
  <div class="grid"><?php foreach ($featured as $p) echo product_card($p); ?></div>
</section>

<section class="band"><?= content('home-subscribe') ?></section>

<section>
  <div class="sec-head"><h2>Brew it right</h2><a class="btn ghost sm" href="shop.php?type=tool">All tools</a></div>
  <div class="grid"><?php foreach ($tools as $p) echo product_card($p); ?></div>
</section>

<?php include 'includes/footer.php'; ?>
