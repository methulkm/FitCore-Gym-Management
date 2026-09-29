<?php
// Every protected page starts with: require_once __DIR__ . '/../includes/bootstrap.php';
if (session_status() === PHP_SESSION_NONE) {
    // Harden the session cookie: JS can't read it (httponly), it isn't sent on cross-site requests
    // like an <img> tag pointing at one of our action links (samesite=Lax), and it only travels over
    // HTTPS once the site is actually served over HTTPS (secure, conditional so localhost still works).
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/components.php';
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/uploads.php';
require_once __DIR__ . '/notifications.php';
require_once __DIR__ . '/csv_export.php';
