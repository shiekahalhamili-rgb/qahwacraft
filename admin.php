<?php
require 'includes/functions.php';

$admin = require_admin();
$section = $_GET['s'] ?? 'dashboard';
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_token();
    $action = $_POST['action'] ?? '';

    if ($action === 'status' && in_array($_POST['status'], $STATUSES)) {
        $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$_POST['status'], (int) $_POST['id']]);
        flash('Order status updated.');
        redirect('admin.php?s=orders');
    }

    if ($action === 'save') {
        $data = [
            trim($_POST['name']), $_POST['type'], $_POST['roaster'] ?: null, trim($_POST['origin']) ?: null, trim($_POST['process']) ?: null,
            $_POST['type'] === 'beans' ? (int) $_POST['roast_level'] : null, $_POST['flavor'] ?: null, trim($_POST['notes']) ?: null,
            (float) $_POST['price'], trim($_POST['weight']) ?: null, trim($_POST['description']), $_POST['icon'] ?: null,
            isset($_POST['featured']) ? 1 : 0, (int) $_POST['stock'],
        ];
        if ($data[0] === '' || $data[8] <= 0) {
            flash('Enter a name and a price above 0.', 'err');
            redirect('admin.php?s=products');
        }
        if ($_POST['id'] !== '') {
            $data[] = $_POST['id'];
            $pdo->prepare('UPDATE products SET name=?, type=?, roaster=?, origin=?, process=?, roast_level=?, flavor=?, notes=?, price=?, weight=?, description=?, icon=?, featured=?, stock=? WHERE id=?')
                ->execute($data);
            flash('Product saved.');
        } else {
            // The id is made from the name, e.g. "Saudi Asir Coffee" -> "saudi-asir-coffee"
            $id = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($data[0])), '-') . '-' . rand(10, 99);
            array_unshift($data, $id);
            $pdo->prepare('INSERT INTO products (id, name, type, roaster, origin, process, roast_level, flavor, notes, price, weight, description, icon, featured, stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
                ->execute($data);
            flash('Product added.');
        }
        redirect('admin.php?s=products');
    }

    if ($action === 'delete') {
        $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$_POST['id']]);
        flash('Product deleted.');
        redirect('admin.php?s=products');
    }

    if ($action === 'delete_message') {
        $pdo->prepare('DELETE FROM messages WHERE id = ?')->execute([(int) $_POST['id']]);
        flash('Message deleted.');
        redirect('admin.php?s=messages');
    }
}

$title = 'Admin';
$active = 'admin';
include 'includes/header.php';
?>

<h1 style="font-size:clamp(30px,4vw,44px)">Admin panel</h1>
<nav class="tabs" style="margin:16px 0 20px">
  <?php foreach (['dashboard' => 'Dashboard', 'orders' => 'Orders', 'products' => 'Products', 'messages' => 'Messages', 'customers' => 'Customers'] as $key => $label): ?>
    <a href="admin.php?s=<?= $key ?>" aria-selected="<?= $section === $key ? 'true' : 'false' ?>"><?= $label ?></a>
  <?php endforeach; ?>
</nav>

<?php if ($section === 'dashboard'): ?>
  <div class="stats">
    <div class="stat"><span class="eyebrow">Revenue</span><b><?= money($pdo->query('SELECT COALESCE(SUM(total), 0) FROM orders')->fetchColumn()) ?></b></div>
    <div class="stat"><span class="eyebrow">Orders</span><b><?= $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn() ?></b></div>
    <div class="stat"><span class="eyebrow">Customers</span><b><?= $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn() ?></b></div>
    <div class="stat"><span class="eyebrow">Low stock</span><b><?= $pdo->query('SELECT COUNT(*) FROM products WHERE stock <= 5')->fetchColumn() ?></b></div>
    <div class="stat"><span class="eyebrow">Messages</span><b><?= $pdo->query('SELECT COUNT(*) FROM messages')->fetchColumn() ?></b></div>
  </div>

<?php elseif ($section === 'orders'): ?>
  <div class="tablewrap"><table class="team">
    <tr><th>Order</th><th>Date</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th></tr>
    <?php foreach ($pdo->query('SELECT * FROM orders ORDER BY id DESC') as $o): ?>
      <tr>
        <td><?= e($o['order_no']) ?></td>
        <td><?= date('j M Y', strtotime($o['created_at'])) ?></td>
        <td><?= e($o['name']) ?><br><small class="muted"><?= e($o['email']) ?></small></td>
        <td><?= money($o['total']) ?></td>
        <td><?= e($PAYMENTS[$o['payment']]) ?></td>
        <td><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?= $o['id'] ?>">
          <select name="status" onchange="this.form.submit()"><?php foreach ($STATUSES as $s) echo '<option' . ($o['status'] === $s ? ' selected' : '') . ">$s</option>"; ?></select>
        </form></td>
      </tr>
    <?php endforeach; ?>
  </table></div>

<?php elseif ($section === 'products' && isset($_GET['edit'])): ?>
  <?php
  $p = get_product($_GET['edit']) ?: ['id' => '', 'name' => '', 'type' => 'beans', 'roaster' => '', 'origin' => '', 'process' => '', 'roast_level' => 1,
      'flavor' => '', 'notes' => '', 'price' => '', 'weight' => '250 g', 'description' => '', 'icon' => '', 'featured' => 0, 'stock' => 50];
  ?>
  <p><a href="admin.php?s=products">← All products</a></p>
  <form class="panel form" method="post" style="max-width:720px">
    <?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= e($p['id']) ?>">
    <h3><?= $p['id'] ? 'Edit product' : 'Add product' ?></h3>
    <div class="row2">
      <div class="field"><label for="name">Name</label><input id="name" name="name" value="<?= e($p['name']) ?>" required></div>
      <div class="field"><label for="type">Category</label><select id="type" name="type">
        <?php foreach (['beans', 'tool', 'accessory'] as $t) echo '<option value="' . $t . '"' . ($p['type'] === $t ? ' selected' : '') . '>' . type_name($t) . '</option>'; ?></select></div>
    </div>
    <div class="row3">
      <div class="field"><label for="price">Price (SAR)</label><input id="price" name="price" type="number" step="0.01" value="<?= e($p['price']) ?>" required></div>
      <div class="field"><label for="stock">Stock</label><input id="stock" name="stock" type="number" value="<?= e($p['stock']) ?>"></div>
      <div class="field"><label for="weight">Size</label><input id="weight" name="weight" value="<?= e($p['weight']) ?>"></div>
    </div>
    <p class="muted">Coffee only:</p>
    <div class="row3">
      <div class="field"><label for="roaster">Roaster</label><select id="roaster" name="roaster"><option value="">—</option>
        <?php foreach ($ROASTERS as $key => $r) echo '<option value="' . $key . '"' . ($p['roaster'] === $key ? ' selected' : '') . '>' . $r[0] . '</option>'; ?></select></div>
      <div class="field"><label for="flavor">Flavor</label><select id="flavor" name="flavor"><option value="">—</option>
        <?php foreach (['fruity', 'floral', 'chocolatey'] as $f) echo '<option' . ($p['flavor'] === $f ? ' selected' : '') . ">$f</option>"; ?></select></div>
      <div class="field"><label for="roast_level">Roast (0 light – 4 dark)</label><input id="roast_level" name="roast_level" type="number" min="0" max="4" value="<?= e($p['roast_level']) ?>"></div>
    </div>
    <div class="row3">
      <div class="field"><label for="origin">Origin</label><input id="origin" name="origin" value="<?= e($p['origin']) ?>"></div>
      <div class="field"><label for="process">Process</label><input id="process" name="process" value="<?= e($p['process']) ?>"></div>
      <div class="field"><label for="notes">Tasting notes</label><input id="notes" name="notes" value="<?= e($p['notes']) ?>"></div>
    </div>
    <div class="field"><label for="icon">Picture for tools</label><select id="icon" name="icon"><option value="">—</option>
      <?php foreach (['dripper', 'filters', 'grinder', 'egrinder', 'kettle', 'scale', 'server', 'kit', 'canister', 'gift'] as $i) echo '<option' . ($p['icon'] === $i ? ' selected' : '') . ">$i</option>"; ?></select></div>
    <div class="field"><label for="description">Description</label><textarea id="description" name="description" rows="4" required><?= e($p['description']) ?></textarea></div>
    <label><input type="checkbox" name="featured" <?= $p['featured'] ? 'checked' : '' ?>> Show on the home page</label>
    <button class="btn" type="submit">Save</button>
  </form>
  <?php if ($p['id']): ?>
    <form method="post" style="margin-top:12px" onsubmit="return confirm('Delete this product?')"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e($p['id']) ?>"><button class="btn ghost sm">Delete product</button></form>
  <?php endif; ?>

<?php elseif ($section === 'products'): ?>
  <p><a class="btn sm" href="admin.php?s=products&edit=new">Add product</a></p>
  <div class="tablewrap"><table class="team">
    <tr><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th></th></tr>
    <?php foreach ($pdo->query("SELECT * FROM products ORDER BY FIELD(type, 'beans', 'tool', 'accessory'), name") as $p): ?>
      <tr><td><?= e($p['name']) ?></td><td><?= type_name($p['type']) ?></td><td><?= money($p['price']) ?></td><td><?= stock_label($p) ?> <?= $p['stock'] ?></td>
        <td><a class="btn ghost sm" href="admin.php?s=products&edit=<?= e($p['id']) ?>">Edit</a></td></tr>
    <?php endforeach; ?>
  </table></div>

<?php elseif ($section === 'messages'): ?>
  <div class="list">
    <?php foreach ($pdo->query('SELECT * FROM messages ORDER BY id DESC') as $m): ?>
      <div class="item">
        <div class="grow"><b><?= e($m['topic']) ?></b><span class="muted"><?= e($m['name']) ?> · <?= e($m['email']) ?> · <?= e($m['order_no']) ?> · <?= date('j M Y', strtotime($m['created_at'])) ?></span><p><?= nl2br(e($m['message'])) ?></p></div>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="delete_message"><input type="hidden" name="id" value="<?= $m['id'] ?>"><button class="btn ghost sm">Delete</button></form>
      </div>
    <?php endforeach; ?>
  </div>

<?php else: ?>
  <div class="tablewrap"><table class="team">
    <tr><th>Name</th><th>Email</th><th>Role</th><th>Points</th><th>Joined</th></tr>
    <?php foreach ($pdo->query('SELECT * FROM users ORDER BY id DESC') as $u): ?>
      <tr><td><?= e($u['name']) ?></td><td><?= e($u['email']) ?></td><td><?= e($u['role']) ?></td><td><?= $u['points'] ?></td><td><?= date('j M Y', strtotime($u['created_at'])) ?></td></tr>
    <?php endforeach; ?>
  </table></div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
