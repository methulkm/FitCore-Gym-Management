<?php
require_once __DIR__ . '/../includes/bootstrap.php';
csrf_verify('public/contact.php');

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$message = trim($_POST['message'] ?? '');

$errors = [];
v_push($errors, v_required($name, 'Full name'));
v_push($errors, v_person_name($name));
v_push($errors, v_phone($phone));
v_push($errors, v_email($email));
if ($phone === '' && $email === '') $errors[] = 'Please give a phone number or an email so we can reply to you.';
v_push($errors, v_required($message, 'Message'));
v_push($errors, v_free_text($message, 'Message', 2000, 10));

if ($errors) {
    // The form lives on another page, so carry the errors and typed values back through the session.
    $_SESSION['enquiry_errors'] = $errors;
    $_SESSION['enquiry_old'] = ['name' => $name, 'phone' => $phone, 'email' => $email, 'message' => $message];
    header('Location: ' . base_url('public/contact.php'));
    exit;
}

// FR-02 / UC-18: visitor enquiry, does NOT create a member account.
$pdo->prepare('INSERT INTO enquiries (name, phone, email, message, status) VALUES (?, ?, ?, ?, "new")')
    ->execute([$name, $phone ?: null, $email ?: null, $message]);

notify_admin($pdo, 'enquiry', "New visitor enquiry from {$name}.", 'modules/enquiries/index.php');

redirect_with_flash('public/contact.php', 'success', 'Thanks ' . $name . ' - your enquiry has been sent. We will get back to you soon.');
