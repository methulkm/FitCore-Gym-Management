<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);
csrf_verify('modules/trainers/index.php');

$id = (int) ($_GET['id'] ?? 0);
$reactivate = isset($_GET['reactivate']);
// UC-05 alternate path: deactivate trainer while preserving history.
$newStatus = $reactivate ? 'active' : 'deactivated';
$pdo->prepare('UPDATE trainers SET status = ? WHERE trainer_id = ?')->execute([$newStatus, $id]);
// Keep the portal login in step: a deactivated trainer must not be able to sign in.
$pdo->prepare('UPDATE users u JOIN trainers x ON x.user_id = u.user_id SET u.status = ? WHERE x.trainer_id = ?')
    ->execute([$reactivate ? 'active' : 'inactive', $id]);

redirect_with_flash('modules/trainers/index.php', 'success', $reactivate ? 'Trainer reactivated.' : 'Trainer deactivated.');
