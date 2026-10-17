<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);
csrf_verify('modules/attendance/index.php');

$employeeId = (int) ($_POST['employee_id'] ?? 0);
if (!$employeeId) redirect_with_flash('modules/attendance/index.php', 'error', 'Please choose an employee.');

$stmt = $pdo->prepare('SELECT * FROM employees WHERE employee_id = ?');
$stmt->execute([$employeeId]);
$emp = $stmt->fetch();
if (!$emp || $emp['status'] === 'inactive') {
    redirect_with_flash('modules/attendance/index.php', 'error', 'Please choose an active employee.');
}

$onLeave = $pdo->prepare("SELECT COUNT(*) FROM employee_leave WHERE employee_id = ? AND status = 'approved' AND CURDATE() BETWEEN start_date AND end_date");
$onLeave->execute([$employeeId]);
if ($onLeave->fetchColumn() > 0) {
    redirect_with_flash('modules/attendance/index.php', 'error', "{$emp['full_name']} is on approved leave today, so a check-in cannot be recorded.");
}

// One check-in per employee per day; the check-out is recorded on the same row (check_out.php).
$dup = $pdo->prepare('SELECT COUNT(*) FROM employee_attendance WHERE employee_id = ? AND attendance_date = CURDATE()');
$dup->execute([$employeeId]);
if ($dup->fetchColumn() > 0) {
    redirect_with_flash('modules/attendance/index.php', 'error', 'This employee already has a check-in recorded for today.');
}

$pdo->prepare('INSERT INTO employee_attendance (employee_id, attendance_date, check_in_time) VALUES (?, CURDATE(), CURTIME())')
    ->execute([$employeeId]);
redirect_with_flash('modules/attendance/index.php', 'success', 'Check-in recorded.');
