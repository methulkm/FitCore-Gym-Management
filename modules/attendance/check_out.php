<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);
csrf_verify('modules/attendance/index.php');

$attendanceId = (int) ($_POST['attendance_id'] ?? 0);
if (!$attendanceId) redirect_with_flash('modules/attendance/index.php', 'error', 'Please choose a check-in to check out.');

// Dates and times come from MySQL, the same clock that stamped the check-in.
$stmt = $pdo->prepare('SELECT a.*, e.full_name, CURDATE() AS today, CURTIME() AS now_time
                       FROM employee_attendance a JOIN employees e ON e.employee_id = a.employee_id
                       WHERE a.attendance_id = ?');
$stmt->execute([$attendanceId]);
$row = $stmt->fetch();
if (!$row) redirect_with_flash('modules/attendance/index.php', 'error', 'Please choose a check-in to check out.');

if ($row['attendance_date'] !== $row['today']) {
    redirect_with_flash('modules/attendance/index.php', 'error', "Only today's check-ins can be checked out.");
}
if ($row['check_out_time'] !== null) {
    redirect_with_flash('modules/attendance/index.php', 'error', "{$row['full_name']} has already checked out today.");
}
// Stops an accidental check-out straight after the check-in click.
if (strtotime($row['now_time']) - strtotime($row['check_in_time']) < 60) {
    redirect_with_flash('modules/attendance/index.php', 'error', 'Check-out must be at least 1 minute after the check-in time.');
}

// The WHERE repeats the checks so a double submit cannot overwrite the first check-out time.
$upd = $pdo->prepare('UPDATE employee_attendance SET check_out_time = ?
                      WHERE attendance_id = ? AND check_out_time IS NULL AND attendance_date = CURDATE()');
$upd->execute([$row['now_time'], $attendanceId]);
if ($upd->rowCount() === 0) {
    redirect_with_flash('modules/attendance/index.php', 'error', "{$row['full_name']} has already checked out today.");
}

redirect_with_flash('modules/attendance/index.php', 'success',
    "{$row['full_name']} checked out. Hours worked today: " . worked_time($row['check_in_time'], $row['now_time']) . '.');
