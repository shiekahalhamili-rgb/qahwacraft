<?php
// Shows a text page from content.html: page.php?p=about, faq, privacy, terms, returns, shipping, cookies, subscriptions
require 'includes/functions.php';

$pages = [
    'about'         => 'About us',
    'faq'           => 'Frequently asked questions',
    'privacy'       => 'Privacy policy',
    'terms'         => 'Terms & conditions',
    'returns'       => 'Returns & refunds',
    'shipping'      => 'Shipping & delivery',
    'cookies'       => 'Cookie policy',
    'subscriptions' => 'Subscription terms',
];
$p = isset($pages[$_GET['p'] ?? '']) ? $_GET['p'] : 'about';

$title = $pages[$p];
include 'includes/header.php';
?>

<?php if ($p !== 'about'): ?><h1 style="font-size:clamp(30px,4vw,44px);margin-bottom:18px"><?= $pages[$p] ?></h1><?php endif; ?>
<div class="<?= $p === 'faq' ? 'faq' : 'prose' ?>"><?= content($p) ?></div>

<?php if ($p === 'cookies'): ?>
  <p style="margin-top:16px">Your choice: <b><?= e($_COOKIE['cookie_choice'] ?? 'not chosen yet') ?></b></p>
  <button class="btn ghost sm" id="resetCookie" type="button">Change cookie choice</button>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
