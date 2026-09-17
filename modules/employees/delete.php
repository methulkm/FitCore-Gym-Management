<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$id = (int) ($_GET['id'] ?? 0);
$reactivate = isset($_GET['reactivate']);
$newStatus = $reactivate ? 'active' : 'inactive';
$pdo->prepare('UPDATE employees SET status = ? WHERE employee_id = ?')->execute([$newStatus, $id]);

redirect_with_flash('modules/employees/index.php', 'success', $reactivate ? 'Employee reactivated.' : 'Employee marked inactive.');
