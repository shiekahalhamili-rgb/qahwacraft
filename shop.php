<?php
require 'includes/functions.php';

// Filters from the address bar, e.g. shop.php?type=beans&flavor=fruity
$type = $_GET['type'] ?? '';
$flavor = $_GET['flavor'] ?? '';
$roaster = $_GET['roaster'] ?? '';
$search = trim($_GET['q'] ?? '');
$sort = $_GET['sort'] ?? '';

$sql = 'SELECT * FROM products WHERE 1';
$params = [];
if ($type !== '') {
    $sql .= ' AND type = ?';
    $params[] = $type;
}
if ($flavor !== '') {
    $sql .= ' AND flavor = ?';
    $params[] = $flavor;
}
if ($roaster !== '') {
    $sql .= ' AND roaster = ?';
    $params[] = $roaster;
}
if ($search !== '') {
    $sql .= ' AND (name LIKE ? OR notes LIKE ? OR origin LIKE ? OR description LIKE ?)';
    array_push($params, "%$search%", "%$search%", "%$search%", "%$search%");
}
if ($sort === 'low') {
    $sql .= ' ORDER BY price';
} elseif ($sort === 'high') {
    $sql .= ' ORDER BY price DESC';
} else {
    $sql .= " ORDER BY FIELD(type, 'beans', 'tool', 'accessory'), featured DESC, name";
}
$st = db()->prepare($sql);
$st->execute($params);
$products = $st->fetchAll();

// Makes a filter link that keeps the other filters.
function filter_link($key, $value, $label)
{
    $query = $_GET;
    if ($value === '') {
        unset($query[$key]);
    } else {
        $query[$key] = $value;
    }
    $pressed = ($_GET[$key] ?? '') === $value ? 'true' : 'false';
    return '<a class="fopt" aria-pressed="' . $pressed . '" href="shop.php?' . e(http_build_query($query)) . '">' . $label . '</a>';
}

$title = 'Shop';
$active = $type === 'beans' ? 'beans' : ($type === 'tool' ? 'tool' : 'shop');
include 'includes/header.php';
?>

<h1 style="font-size:clamp(30px,4vw,44px);margin-bottom:20px"><?= $type ? type_name($type) : 'Shop all' ?></h1>

<div class="shop">
  <aside class="filters">
    <div class="fgroup"><span class="eyebrow">Category</span><div class="fopts">
      <?= filter_link('type', '', 'All') ?>
      <?= filter_link('type', 'beans', 'Beans') ?>
      <?= filter_link('type', 'tool', 'Brewing tools') ?>
      <?= filter_link('type', 'accessory', 'Accessories') ?>
    </div></div>
    <div class="fgroup"><span class="eyebrow">Flavor</span><div class="fopts">
      <?= filter_link('flavor', '', 'Any') ?>
      <?= filter_link('flavor', 'fruity', 'Fruity') ?>
      <?= filter_link('flavor', 'floral', 'Floral') ?>
      <?= filter_link('flavor', 'chocolatey', 'Chocolatey') ?>
    </div></div>
    <div class="fgroup"><span class="eyebrow">Roaster</span><div class="fopts">
      <?= filter_link('roaster', '', 'All') ?>
      <?php foreach ($ROASTERS as $key => $r) echo filter_link('roaster', $key, $r[0]); ?>
    </div></div>
  </aside>

  <div>
    <div class="toolbar">
      <span class="muted"><?= count($products) ?> products<?= $search ? ' for “' . e($search) . '”' : '' ?></span>
      <span>Sort: <?= filter_link('sort', '', 'Featured') ?> <?= filter_link('sort', 'low', 'Price ↑') ?> <?= filter_link('sort', 'high', 'Price ↓') ?></span>
    </div>
    <?php if ($products): ?>
      <div class="grid three"><?php foreach ($products as $p) echo product_card($p); ?></div>
    <?php else: ?>
      <div class="empty"><h3>No products found</h3><a class="btn ghost sm" href="shop.php">Clear filters</a></div>
    <?php endif; ?>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
