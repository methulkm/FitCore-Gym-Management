<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$classes = $pdo->query("SELECT c.*, t.full_name AS trainer_name,
    (SELECT COUNT(*) FROM bookings b WHERE b.class_id = c.class_id AND b.status = 'booked') AS booked_count
    FROM classes c JOIN trainers t ON t.trainer_id = c.trainer_id
    ORDER BY c.class_date, c.start_time")->fetchAll();

$rows = array_map(fn($c) => [
    $c['class_name'], $c['trainer_name'], $c['class_date'], $c['start_time'],
    $c['duration_minutes'], $c['booked_count'] . '/' . $c['capacity'], $c['status'],
], $classes);

csv_download('classes_' . date('Ymd') . '.csv',
    ['Class Name', 'Trainer', 'Date', 'Start Time', 'Duration (min)', 'Booked/Capacity', 'Status'], $rows);
