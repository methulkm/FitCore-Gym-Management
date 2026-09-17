<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$id = (int) ($_GET['id'] ?? 0);
$reactivate = isset($_GET['reactivate']);
// UC-05 alternate path: deactivate trainer while preserving history.
$newStatus = $reactivate ? 'active' : 'deactivated';
$pdo->prepare('UPDATE trainers SET status = ? WHERE trainer_id = ?')->execute([$newStatus, $id]);

redirect_with_flash('modules/trainers/index.php', 'success', $reactivate ? 'Trainer reactivated.' : 'Trainer deactivated.');
