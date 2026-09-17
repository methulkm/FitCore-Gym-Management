<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT s.*, p.duration_months FROM subscriptions s JOIN membership_plans p ON p.plan_id = s.plan_id WHERE subscription_id = ?');
$stmt->execute([$id]);
$sub = $stmt->fetch();
if (!$sub) redirect_with_flash('modules/subscriptions/index.php', 'error', 'Subscription not found.');

// BR-09: if still active, renewal extends from the CURRENT EXPIRY DATE.
// If expired, renewal RESTARTS from today (the approval/renewal date).
[$dispKey] = subscription_display_status($sub['expiry_date'], $sub['status']);
$months = (int) $sub['duration_months'];

if ($dispKey === 'active' || $dispKey === 'expiring_soon') {
    $newStart = $sub['start_date'];
    $newExpiry = date('Y-m-d', strtotime($sub['expiry_date'] . " +{$months} months"));
} else {
    $newStart = date('Y-m-d');
    $newExpiry = date('Y-m-d', strtotime("+{$months} months"));
}

$pdo->prepare('UPDATE subscriptions SET start_date = ?, expiry_date = ?, status = "active" WHERE subscription_id = ?')
    ->execute([$newStart, $newExpiry, $id]);

redirect_with_flash('modules/subscriptions/index.php', 'success', 'Subscription renewed - new expiry ' . date('d M Y', strtotime($newExpiry)) . '.');
