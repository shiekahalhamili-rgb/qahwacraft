<?php
require 'includes/functions.php';

$user = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_token();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '') $errors[] = 'Enter your name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email.';
    if (strlen($message) < 10) $errors[] = 'Write a message of at least 10 characters.';

    if (!$errors) {
        db()->prepare('INSERT INTO messages (name, email, order_no, topic, message) VALUES (?, ?, ?, ?, ?)')
            ->execute([$name, $email, trim($_POST['order_no'] ?? ''), $_POST['topic'] ?? 'Other', $message]);
        flash('Thank you! We received your message and will reply within 24 hours.');
        redirect('contact.php');
    }
}

$title = 'Contact us';
$active = 'contact';
include 'includes/header.php';
?>

<h1 style="font-size:clamp(30px,4vw,44px);margin-bottom:20px">Contact us</h1>
<div class="two">
  <div class="contact-grid"><?= content('contact-channels') ?></div>
  <form class="panel form" method="post">
    <?= csrf_field() ?>
    <h3>Send us a message</h3>
    <?php if ($errors): ?><ul class="errors"><?php foreach ($errors as $err) echo '<li>' . e($err) . '</li>'; ?></ul><?php endif; ?>
    <div class="field"><label for="name">Name</label><input id="name" name="name" value="<?= e($_POST['name'] ?? $user['name'] ?? '') ?>" required></div>
    <div class="field"><label for="email">Email</label><input id="email" name="email" type="email" value="<?= e($_POST['email'] ?? $user['email'] ?? '') ?>" required></div>
    <div class="field"><label for="order_no">Order number (optional)</label><input id="order_no" name="order_no" placeholder="QC-10001"></div>
    <div class="field"><label for="topic">Topic</label>
      <select id="topic" name="topic"><option>Where is my order?</option><option>Return or refund</option><option>Subscription</option><option>Brewing advice</option><option>Other</option></select></div>
    <div class="field"><label for="message">Message</label><textarea id="message" name="message" rows="5" required><?= e($_POST['message'] ?? '') ?></textarea></div>
    <button class="btn" type="submit">Send message</button>
  </form>
</div>

<?php include 'includes/footer.php'; ?>
