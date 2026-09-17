<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM trainers WHERE trainer_id = ?');
$stmt->execute([$id]);
$trainer = $stmt->fetch();
if (!$trainer) redirect_with_flash('modules/trainers/index.php', 'error', 'Trainer not found.');

// Never trust the list page's flag alone - re-check server-side before allowing a real DELETE.
// (classes.trainer_id and bookings.trainer_id have no ON DELETE CASCADE, so MySQL itself would
// also refuse this - but we check first to give a clear message instead of a raw SQL error.)
$countStmt = $pdo->prepare(
    'SELECT
        (SELECT COUNT(*) FROM classes WHERE trainer_id = :id1) +
        (SELECT COUNT(*) FROM bookings WHERE trainer_id = :id2) AS history_count'
);
$countStmt->execute(['id1' => $id, 'id2' => $id]);
$historyCount = (int) $countStmt->fetchColumn();

if ($historyCount > 0) {
    redirect_with_flash('modules/trainers/index.php', 'error', 'Cannot permanently delete this trainer - they have classes or bookings on record. Use Deactivate instead.');
}

$pdo->beginTransaction();
try {
    // trainer_availability rows cascade automatically; the login account does not, so remove it here.
    if ($trainer['user_id']) {
        $pdo->prepare('DELETE FROM users WHERE user_id = ?')->execute([$trainer['user_id']]);
    }
    $pdo->prepare('DELETE FROM trainers WHERE trainer_id = ?')->execute([$id]);
    $pdo->commit();
    redirect_with_flash('modules/trainers/index.php', 'success', "Trainer {$trainer['trainer_code']} permanently deleted.");
} catch (Exception $e) {
    $pdo->rollBack();
    redirect_with_flash('modules/trainers/index.php', 'error', 'Delete failed: ' . $e->getMessage());
}
