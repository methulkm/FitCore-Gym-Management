<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$equipment = $pdo->query('SELECT * FROM equipment ORDER BY equipment_id')->fetchAll();
$rows = array_map(fn($eq) => [
    $eq['equipment_code'], $eq['equipment_name'], $eq['supplier'], $eq['purchase_date'],
    $eq['condition_status'], $eq['location'], $eq['status'],
], $equipment);

csv_download('equipment_' . date('Ymd') . '.csv',
    ['Equipment Code', 'Name', 'Supplier', 'Purchase Date', 'Condition', 'Location', 'Status'], $rows);
