<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $message === '') {
    redirect_with_flash('public/index.php', 'error', 'Please fill in your name and message.');
}

// FR-02 / UC-18: visitor enquiry, does NOT create a member account.
$pdo->prepare('INSERT INTO enquiries (name, phone, email, message, status) VALUES (?, ?, ?, ?, "new")')
    ->execute([$name, $phone, $email, $message]);

redirect_with_flash('public/index.php', 'success', 'Thanks ' . $name . ' - your enquiry has been sent. We will get back to you soon.');
