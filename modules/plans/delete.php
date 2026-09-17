<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$id = (int) ($_GET['id'] ?? 0);
$reactivate = isset($_GET['reactivate']);

// BR-06: a plan referenced by historical subscriptions/payments is deactivated, never hard-deleted.
$newStatus = $reactivate ? 'active' : 'deactivated';
$pdo->prepare('UPDATE membership_plans SET status = ? WHERE plan_id = ?')->execute([$newStatus, $id]);

redirect_with_flash('modules/plans/index.php', 'success', $reactivate ? 'Plan reactivated.' : 'Plan deactivated.');
