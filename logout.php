<?php
require 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_token();
    $_SESSION = [];
    session_regenerate_id(true);
    flash('You signed out.');
}
redirect('index.php');
