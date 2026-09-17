<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM members WHERE member_id = ?');
$stmt->execute([$id]);
$member = $stmt->fetch();
if (!$member) redirect_with_flash('modules/members/index.php', 'error', 'Member not found.');

// Never trust the list page's flag alone - re-check server-side that this member truly has
// no historical records before allowing a real DELETE (3.1: don't destroy referenced history).
$countStmt = $pdo->prepare('SELECT
    (SELECT COUNT(*) FROM subscriptions WHERE member_id = :id1) +
    (SELECT COUNT(*) FROM payments WHERE member_id = :id2) +
    (SELECT COUNT(*) FROM bookings WHERE member_id = :id3) +
    (SELECT COUNT(*) FROM member_attendance WHERE member_id = :id4) AS history_count');
$countStmt->execute(['id1' => $id, 'id2' => $id, 'id3' => $id, 'id4' => $id]);
$historyCount = (int) $countStmt->fetchColumn();

if ($historyCount > 0) {
    redirect_with_flash('modules/members/index.php', 'error', 'Cannot permanently delete this member - they have subscription/payment/booking/attendance history. Use Deactivate instead.');
}

$pdo->beginTransaction();
try {
    // Remove their login account too, if they had one, so the email is free again.
    if ($member['user_id']) {
        $pdo->prepare('DELETE FROM users WHERE user_id = ?')->execute([$member['user_id']]);
    }
    $pdo->prepare('DELETE FROM members WHERE member_id = ?')->execute([$id]);
    $pdo->commit();
    redirect_with_flash('modules/members/index.php', 'success', "Member {$member['member_code']} permanently deleted.");
} catch (Exception $e) {
    $pdo->rollBack();
    redirect_with_flash('modules/members/index.php', 'error', 'Delete failed: ' . $e->getMessage());
}
