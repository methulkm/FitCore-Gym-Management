<?php
// Single shared PDO connection used by every module (NFR-S03: prepared statements only).

$DB_HOST = 'localhost';
$DB_NAME = 'fitcore';
$DB_USER = 'root';
$DB_PASS = ''; // default XAMPP MySQL root has no password

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die('Database connection failed. Make sure MySQL is running in XAMPP and the "fitcore" database has been imported (database/schema.sql). Details: ' . htmlspecialchars($e->getMessage()));
}
