<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$employeeId = (int) ($_POST['employee_id'] ?? 0);
if (!$employeeId) redirect_with_flash('modules/attendance/index.php', 'error', 'Please choose an employee.');

try {
    // BR-15: check-in time only, no check-out. uq_emp_att prevents a duplicate same-day check-in.
    $pdo->prepare('INSERT INTO employee_attendance (employee_id, attendance_date, check_in_time) VALUES (?, CURDATE(), CURTIME())')
        ->execute([$employeeId]);
    redirect_with_flash('modules/attendance/index.php', 'success', 'Check-in recorded.');
} catch (PDOException $e) {
    redirect_with_flash('modules/attendance/index.php', 'error', 'This employee already has a check-in recorded for today.');
}
