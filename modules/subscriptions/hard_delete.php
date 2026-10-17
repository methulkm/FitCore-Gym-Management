<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);
csrf_verify('modules/subscriptions/index.php');

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT s.*, m.full_name FROM subscriptions s JOIN members m ON m.member_id = s.member_id WHERE s.subscription_id = ?');
$stmt->execute([$id]);
$sub = $stmt->fetch();
if (!$sub) redirect_with_flash('modules/subscriptions/index.php', 'error', 'Subscription not found.');

// A subscription created by an approved payment is the financial record of that payment (BR-08/BR-09).
// Only manually-created subscriptions with no linked payment may be removed.
$countStmt = $pdo->prepare('SELECT COUNT(*) FROM payments WHERE subscription_id = ?');
$countStmt->execute([$id]);

if ((int) $countStmt->fetchColumn() > 0) {
    redirect_with_flash('modules/subscriptions/index.php', 'error', 'Cannot delete this subscription - it is linked to a verified payment.');
}

$pdo->prepare('DELETE FROM subscriptions WHERE subscription_id = ?')->execute([$id]);
redirect_with_flash('modules/subscriptions/index.php', 'success', 'Subscription for ' . $sub['full_name'] . ' deleted.');
