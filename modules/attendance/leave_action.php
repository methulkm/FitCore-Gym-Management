<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$id = (int) ($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';
$status = $action === 'approve' ? 'approved' : ($action === 'reject' ? 'rejected' : null);

if (!$status) redirect_with_flash('modules/attendance/index.php', 'error', 'Invalid action.');

$pdo->prepare('UPDATE employee_leave SET status = ? WHERE leave_id = ?')->execute([$status, $id]);
redirect_with_flash('modules/attendance/index.php', 'success', "Leave request {$status}.");
