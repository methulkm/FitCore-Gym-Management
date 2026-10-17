<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$employees = $pdo->query('SELECT * FROM employees ORDER BY employee_id')->fetchAll();
$rows = array_map(fn($e) => [
    $e['employee_code'], $e['full_name'], $e['phone'], $e['job_role'], $e['shift'], $e['status'],
], $employees);

csv_download('employees_' . date('Ymd') . '.csv',
    ['Employee Code', 'Full Name', 'Phone', 'Job Role', 'Shift', 'Status'], $rows);
