<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM payments WHERE payment_id = ?');
$stmt->execute([$id]);
$payment = $stmt->fetch();
if (!$payment) redirect_with_flash('modules/payments/index.php', 'error', 'Payment not found.');
if ($payment['status'] !== 'pending') redirect_with_flash('modules/payments/index.php', 'error', 'This payment has already been processed.');

$planStmt = $pdo->prepare('SELECT duration_months FROM membership_plans WHERE plan_id = ?');
$planStmt->execute([$payment['plan_id']]);
$months = (int) $planStmt->fetchColumn();

// BR-08/BR-09, handled transactionally (4.2): approving a payment activates/extends the membership.
// If a still-active subscription exists, extend from its expiry; otherwise (none, or expired) start fresh today.
$pdo->beginTransaction();
try {
    $latest = $pdo->prepare('SELECT * FROM subscriptions WHERE member_id = ? ORDER BY subscription_id DESC LIMIT 1');
    $latest->execute([$payment['member_id']]);
    $sub = $latest->fetch();

    $isStillActive = $sub && $sub['status'] === 'active' && strtotime($sub['expiry_date']) >= strtotime(date('Y-m-d'));

    if ($isStillActive) {
        $newExpiry = date('Y-m-d', strtotime($sub['expiry_date'] . " +{$months} months"));
        $pdo->prepare('UPDATE subscriptions SET expiry_date = ?, status = "active" WHERE subscription_id = ?')
            ->execute([$newExpiry, $sub['subscription_id']]);
        $subscriptionId = $sub['subscription_id'];
    } else {
        $startDate = date('Y-m-d');
        $newExpiry = date('Y-m-d', strtotime("+{$months} months"));
        $pdo->prepare('INSERT INTO subscriptions (member_id, plan_id, start_date, expiry_date, status) VALUES (?, ?, ?, ?, "active")')
            ->execute([$payment['member_id'], $payment['plan_id'], $startDate, $newExpiry]);
        $subscriptionId = $pdo->lastInsertId();
    }

    $pdo->prepare('UPDATE payments SET status = "approved", subscription_id = ?, verified_by = ?, verified_at = NOW() WHERE payment_id = ?')
        ->execute([$subscriptionId, current_user()['user_id'], $id]);

    $pdo->commit();
    redirect_with_flash('modules/payments/index.php', 'success', "Payment {$payment['payment_code']} approved - membership updated to expire {$newExpiry}.");
} catch (Exception $e) {
    $pdo->rollBack();
    redirect_with_flash('modules/payments/index.php', 'error', 'Approval failed, no changes were made: ' . $e->getMessage());
}
