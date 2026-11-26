<?php
// Shared functions. Every page starts with: require 'includes/functions.php';

session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
date_default_timezone_set('Asia/Riyadh');

/* ---------- small helpers ---------- */

function e($text)
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

function money($amount)
{
    $amount = round((float) $amount, 2);
    return 'SAR ' . ($amount == floor($amount) ? number_format($amount) : number_format($amount, 2));
}

function redirect($page)
{
    header('Location: ' . $page);
    exit;
}

function flash($message, $type = 'ok')
{
    $_SESSION['flash'][] = [$message, $type];
}

/* ---------- security ---------- */

// A secret token in every form so other websites cannot send forms for the user.
function token()
{
    if (empty($_SESSION['token'])) {
        $_SESSION['token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['token'];
}

function csrf_field()
{
    return '<input type="hidden" name="token" value="' . token() . '">';
}

function check_token()
{
    if (!isset($_POST['token'], $_SESSION['token']) || $_POST['token'] !== $_SESSION['token']) {
        flash('Your session expired. Please try again.', 'err');
        redirect($_SERVER['PHP_SELF']);
    }
}

/* ---------- users ---------- */

function current_user()
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $st = db()->prepare('SELECT * FROM users WHERE id = ?');
    $st->execute([$_SESSION['user_id']]);
    return $st->fetch() ?: null;
}

function require_login($back = 'account.php')
{
    $user = current_user();
    if (!$user) {
        flash('Please sign in to continue.', 'info');
        redirect('login.php?back=' . urlencode($back));
    }
    return $user;
}

function require_admin()
{
    $user = require_login('admin.php');
    if ($user['role'] !== 'admin') {
        flash('That page is only for admins.', 'err');
        redirect('account.php');
    }
    return $user;
}

/* ---------- products ---------- */

function get_product($id)
{
    $st = db()->prepare('SELECT * FROM products WHERE id = ?');
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

function roaster_name($p)
{
    global $ROASTERS;
    return $ROASTERS[$p['roaster']][0] ?? '';
}

function type_name($type)
{
    $names = ['beans' => 'Coffee beans', 'tool' => 'Brewing tools', 'accessory' => 'Accessories'];
    return $names[$type] ?? $type;
}

// Product picture: a coffee bag in the roaster's color, or a simple drawing for tools.
function art($p)
{
    global $ROASTERS;
    if ($p['type'] === 'beans') {
        $roaster = $ROASTERS[$p['roaster']] ?? ['Roaster', '', '#1F3D2B'];
        $dots = '';
        for ($i = 0; $i < 5; $i++) {
            $dots .= $i <= $p['roast_level'] ? '<i class="on"></i>' : '<i></i>';
        }
        $label = explode(' ', $p['name'])[count(explode(' ', $p['name'])) > 1 ? 1 : 0];
        return '<div class="art" data-f="' . e($p['flavor']) . '"><div class="bag" style="--lab:' . $roaster[2] . '">
            <div class="bag-label"><span class="r">' . e(explode(' ', $roaster[0])[0]) . '</span><span class="o">' . e($label) . '</span><span class="w">' . e($p['weight']) . '</span></div>
            <div class="bag-roast">' . $dots . '</div></div></div>';
    }
    $icons = [
        'dripper'  => '<path d="M28 38h64L74 82H46zM40 82h40v8H40zM92 46c14 0 14 22-2 22"/>',
        'filters'  => '<path d="M24 44h64L72 86H40zM32 34h64L80 76M40 24h64L88 66"/>',
        'grinder'  => '<rect x="44" y="46" width="32" height="50" rx="5"/><path d="M44 62h32M60 46V30l26-8"/><circle cx="90" cy="21" r="6"/>',
        'egrinder' => '<path d="M40 20h40l-6 22H46z"/><rect x="36" y="42" width="48" height="56" rx="7"/><circle cx="60" cy="60" r="7"/>',
        'kettle'   => '<rect x="38" y="48" width="44" height="44" rx="8"/><path d="M50 48v-6h20v6M38 62c-16 0-16-22-28-30M82 56c16 0 16 28 0 28"/>',
        'scale'    => '<rect x="18" y="58" width="84" height="28" rx="5"/><rect x="46" y="66" width="28" height="12" rx="2"/>',
        'server'   => '<path d="M36 36h46l-4 56H40zM82 46c16 2 16 26-2 28M44 60h30"/>',
        'kit'      => '<path d="M34 22h52L72 52H48zM44 52h32v6H44zM38 62h44l-4 34H42z"/>',
        'canister' => '<rect x="38" y="36" width="44" height="58" rx="7"/><rect x="35" y="26" width="50" height="10" rx="3"/>',
        'gift'     => '<rect x="26" y="54" width="68" height="40" rx="3"/><rect x="22" y="42" width="76" height="12" rx="3"/><path d="M60 42v52"/>',
    ];
    $drawing = $icons[$p['icon']] ?? $icons['gift'];
    return '<div class="art"><svg class="tool-ic" viewBox="0 0 120 120" fill="none" stroke="currentColor" stroke-width="3" stroke-linejoin="round" stroke-linecap="round">' . $drawing . '</svg></div>';
}

function product_card($p)
{
    $link = 'product.php?id=' . urlencode($p['id']);
    $html = '<article class="card">';
    $html .= '<a class="card-art" href="' . $link . '">' . art($p) . '</a>';
    $html .= '<div class="card-body">';
    $html .= '<div class="card-meta">' . e($p['type'] === 'beans' ? roaster_name($p) : type_name($p['type'])) . '</div>';
    $html .= '<h3><a href="' . $link . '">' . e($p['name']) . '</a></h3>';
    if ($p['type'] === 'beans') {
        $html .= '<div class="notes">';
        foreach (explode(',', $p['notes']) as $note) {
            $html .= '<span class="chip">' . e(trim($note)) . '</span>';
        }
        $html .= '</div>';
    }
    $html .= '<div class="card-foot"><span class="price">' . money($p['price']) . '</span>';
    $html .= '<form method="post" action="cart.php">' . csrf_field() . '<input type="hidden" name="action" value="add">';
    $html .= '<input type="hidden" name="id" value="' . e($p['id']) . '"><button class="btn sm" type="submit">Add to cart</button></form>';
    $html .= '</div></div></article>';
    return $html;
}

function stock_label($p)
{
    if ($p['stock'] <= 0) {
        return '<span class="pill bad">Out of stock</span>';
    }
    if ($p['stock'] <= 5) {
        return '<span class="pill warn">Only ' . $p['stock'] . ' left</span>';
    }
    return '<span class="pill good">In stock</span>';
}

/* ---------- cart (saved in the session) ---------- */

// Each cart line: ['id' => product id or 'subscription', 'name', 'details', 'price', 'qty']
function cart()
{
    return $_SESSION['cart'] ?? [];
}

function cart_count()
{
    $count = 0;
    foreach (cart() as $line) {
        $count += $line['qty'];
    }
    return $count;
}

function cart_totals($courier = null)
{
    global $COUPONS, $COURIERS;
    $subtotal = 0;
    foreach (cart() as $line) {
        $subtotal += $line['price'] * $line['qty'];
    }
    $coupon = $_SESSION['coupon'] ?? null;
    $discount = $coupon ? round($subtotal * $COUPONS[$coupon], 2) : 0;
    $after = $subtotal - $discount;
    // One shipping fee per order when it has products; subscription deliveries include delivery.
    $hasProducts = false;
    foreach (cart() as $line) {
        if ($line['id'] !== 'subscription') {
            $hasProducts = true;
        }
    }
    $shipping = $hasProducts ? ($COURIERS[$courier]['fee'] ?? SHIPPING_FEE) : 0;
    $total = $after + $shipping;
    return [
        'subtotal' => $subtotal,
        'coupon'   => $coupon,
        'discount' => $discount,
        'shipping' => $shipping,
        'total'    => $total,
        'vat'      => round($total * 15 / 115, 2),   // prices include 15% VAT
    ];
}

/* ---------- page text from content.html ---------- */

// Returns one part of content.html, the part between <!-- START name --> and <!-- END name -->.
function content($name)
{
    $html = file_get_contents(__DIR__ . '/../content.html');
    $start = strpos($html, '<!-- START ' . $name . ' -->');
    $end = strpos($html, '<!-- END ' . $name . ' -->');
    if ($start === false || $end === false) {
        return '<p>Missing part "' . e($name) . '" in content.html</p>';
    }
    $start += strlen('<!-- START ' . $name . ' -->');
    return substr($html, $start, $end - $start);
}
