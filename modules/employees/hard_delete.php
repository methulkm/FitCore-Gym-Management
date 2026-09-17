<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM employees WHERE employee_id = ?');
$stmt->execute([$id]);
$employee = $stmt->fetch();
if (!$employee) redirect_with_flash('modules/employees/index.php', 'error', 'Employee not found.');

// Never trust the list page's flag alone - re-check server-side before allowing a real DELETE.
$countStmt = $pdo->prepare(
    'SELECT
        (SELECT COUNT(*) FROM employee_attendance WHERE employee_id = :id1) +
        (SELECT COUNT(*) FROM employee_leave WHERE employee_id = :id2) AS history_count'
);
$countStmt->execute(['id1' => $id, 'id2' => $id]);
$historyCount = (int) $countStmt->fetchColumn();

if ($historyCount > 0) {
    redirect_with_flash('modules/employees/index.php', 'error', 'Cannot permanently delete this employee - they have attendance/leave history. Use Deactivate instead.');
}

$pdo->prepare('DELETE FROM employees WHERE employee_id = ?')->execute([$id]);
redirect_with_flash('modules/employees/index.php', 'success', "Employee {$employee['employee_code']} permanently deleted.");
