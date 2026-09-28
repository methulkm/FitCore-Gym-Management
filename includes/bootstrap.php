<?php
// Every protected page starts with: require_once __DIR__ . '/../includes/bootstrap.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/components.php';
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/uploads.php';
require_once __DIR__ . '/notifications.php';
require_once __DIR__ . '/csv_export.php';
