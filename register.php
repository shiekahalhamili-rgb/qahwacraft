<?php
require 'includes/functions.php';

$errors = [];
$name = $email = $phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_token();
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '') $errors[] = 'Enter your name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email.';
    if (strlen($phone) < 9) $errors[] = 'Enter your mobile number.';
    if (strlen($password) < 8) $errors[] = 'The password needs at least 8 characters.';
    if ($password !== ($_POST['confirm'] ?? '')) $errors[] = 'The passwords do not match.';

    $st = db()->prepare('SELECT id FROM users WHERE email = ?');
    $st->execute([$email]);
    if ($st->fetch()) $errors[] = 'This email already has an account.';

    if (!$errors) {
        // The password is saved as a hash, never as plain text.
        db()->prepare('INSERT INTO users (name, email, phone, password, points) VALUES (?, ?, ?, ?, 100)')
            ->execute([$name, $email, $phone, password_hash($password, PASSWORD_DEFAULT)]);
        session_regenerate_id(true);
        $_SESSION['user_id'] = db()->lastInsertId();
        flash('Your account is ready. You got 100 welcome points!');
        redirect(cart() ? 'checkout.php' : 'account.php');
    }
}

$title = 'Create account';
include 'includes/header.php';
?>

<div class="auth">
  <h1>Create your account</h1>
  <?php if ($errors): ?><ul class="errors"><?php foreach ($errors as $err) echo '<li>' . e($err) . '</li>'; ?></ul><?php endif; ?>
  <form class="panel form" method="post">
    <?= csrf_field() ?>
    <div class="field"><label for="name">Full name</label><input id="name" name="name" value="<?= e($name) ?>" required></div>
    <div class="field"><label for="email">Email</label><input id="email" name="email" type="email" value="<?= e($email) ?>" required></div>
    <div class="field"><label for="phone">Mobile</label><input id="phone" name="phone" value="<?= e($phone) ?>" placeholder="+966 5X XXX XXXX" required></div>
    <div class="field"><label for="password">Password (8+ characters)</label><input id="password" name="password" type="password" required></div>
    <div class="field"><label for="confirm">Confirm password</label><input id="confirm" name="confirm" type="password" required></div>
    <button class="btn" type="submit">Create account</button>
  </form>
  <p>Already have an account? <a href="login.php">Sign in</a></p>
</div>

<?php include 'includes/footer.php'; ?>
