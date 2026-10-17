<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);
csrf_verify('modules/attendance/index.php');

$id = (int) ($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';
$status = $action === 'approve' ? 'approved' : ($action === 'reject' ? 'rejected' : null);

if (!$status) redirect_with_flash('modules/attendance/index.php', 'error', 'Invalid action.');

$stmt = $pdo->prepare('SELECT status FROM employee_leave WHERE leave_id = ?');
$stmt->execute([$id]);
$current = $stmt->fetchColumn();
if ($current === false) redirect_with_flash('modules/attendance/index.php', 'error', 'Leave request not found.');
if ($current !== 'pending') redirect_with_flash('modules/attendance/index.php', 'error', 'This leave request has already been ' . $current . '.');

$pdo->prepare('UPDATE employee_leave SET status = ? WHERE leave_id = ?')->execute([$status, $id]);
redirect_with_flash('modules/attendance/index.php', 'success', "Leave request {$status}.");
