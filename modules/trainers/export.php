<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$trainers = $pdo->query('SELECT * FROM trainers ORDER BY trainer_id')->fetchAll();
$rows = array_map(fn($t) => [
    $t['trainer_code'], $t['full_name'], $t['email'], $t['phone'], $t['specialization'], $t['qualification'], $t['status'],
], $trainers);

csv_download('trainers_' . date('Ymd') . '.csv',
    ['Trainer Code', 'Full Name', 'Email', 'Phone', 'Specialization', 'Qualification', 'Status'], $rows);
