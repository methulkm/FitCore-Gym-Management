<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM equipment WHERE equipment_id = ?');
$stmt->execute([$id]);
$eq = $stmt->fetch();
if (!$eq) redirect_with_flash('modules/equipment/index.php', 'error', 'Equipment not found.');

// equipment_maintenance is ON DELETE CASCADE, so deleting would wipe the service history with it.
$countStmt = $pdo->prepare('SELECT COUNT(*) FROM equipment_maintenance WHERE equipment_id = ?');
$countStmt->execute([$id]);

if ((int) $countStmt->fetchColumn() > 0) {
    redirect_with_flash('modules/equipment/index.php', 'error', 'Cannot permanently delete ' . $eq['equipment_name'] . ' - it has maintenance history. Set its status to Out of Service instead (BR-18).');
}

$pdo->prepare('DELETE FROM equipment WHERE equipment_id = ?')->execute([$id]);
redirect_with_flash('modules/equipment/index.php', 'success', 'Equipment ' . $eq['equipment_code'] . ' permanently deleted.');
