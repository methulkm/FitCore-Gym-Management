<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$id = (int) ($_GET['id'] ?? 0);
$reactivate = isset($_GET['reactivate']);

// Members are referenced by subscriptions/payments, so we deactivate (soft-delete) rather than
// hard DELETE, matching the SRS's general "deactivate, don't delete historical records" guidance (3.1).
$newStatus = $reactivate ? 'active' : 'deactivated';
$stmt = $pdo->prepare('UPDATE members SET account_status = ? WHERE member_id = ?');
$stmt->execute([$newStatus, $id]);

redirect_with_flash('modules/members/index.php', 'success', $reactivate ? 'Member reactivated.' : 'Member deactivated.');
