<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM membership_plans WHERE plan_id = ?');
$stmt->execute([$id]);
$plan = $stmt->fetch();
if (!$plan) redirect_with_flash('modules/plans/index.php', 'error', 'Plan not found.');

// BR-06: a plan referenced by subscriptions or payments is history - re-check server-side, never trust the button.
$countStmt = $pdo->prepare(
    'SELECT
        (SELECT COUNT(*) FROM subscriptions WHERE plan_id = :id1) +
        (SELECT COUNT(*) FROM payments WHERE plan_id = :id2) AS history_count'
);
$countStmt->execute(['id1' => $id, 'id2' => $id]);

if ((int) $countStmt->fetchColumn() > 0) {
    redirect_with_flash('modules/plans/index.php', 'error', 'Cannot permanently delete "' . $plan['plan_name'] . '" - members have subscriptions or payments on this plan. Use Deactivate instead (BR-06).');
}

$pdo->prepare('DELETE FROM membership_plans WHERE plan_id = ?')->execute([$id]);
redirect_with_flash('modules/plans/index.php', 'success', 'Plan "' . $plan['plan_name'] . '" permanently deleted.');
