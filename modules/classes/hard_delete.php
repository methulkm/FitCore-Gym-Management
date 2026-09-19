<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM classes WHERE class_id = ?');
$stmt->execute([$id]);
$class = $stmt->fetch();
if (!$class) redirect_with_flash('modules/classes/index.php', 'error', 'Class not found.');

// bookings.class_id is ON DELETE CASCADE, so a blind DELETE would silently erase members' booking history.
$countStmt = $pdo->prepare('SELECT COUNT(*) FROM bookings WHERE class_id = ?');
$countStmt->execute([$id]);

if ((int) $countStmt->fetchColumn() > 0) {
    redirect_with_flash('modules/classes/index.php', 'error', 'Cannot permanently delete "' . $class['class_name'] . '" - it has member bookings. Use Cancel instead.');
}

$pdo->prepare('DELETE FROM classes WHERE class_id = ?')->execute([$id]);
redirect_with_flash('modules/classes/index.php', 'success', 'Class "' . $class['class_name'] . '" permanently deleted.');
