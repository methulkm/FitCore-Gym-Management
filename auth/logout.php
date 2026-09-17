<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$_SESSION = [];
session_destroy(); // NFR-S06: sessions terminated on logout
header('Location: ' . base_url('auth/login.php'));
exit;
