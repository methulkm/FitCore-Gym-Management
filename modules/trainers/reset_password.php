<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM trainers WHERE trainer_id = ?');
$stmt->execute([$id]);
$trainer = $stmt->fetch();
if (!$trainer) redirect_with_flash('modules/trainers/index.php', 'error', 'Trainer not found.');

if (!$trainer['user_id']) {
    redirect_with_flash('modules/trainers/index.php', 'error', "{$trainer['trainer_code']} doesn't have a login account.");
}

// BR-21: admin resets a forgotten password - generate a fresh temp password, hash it, and
// OVERWRITE the old hash. Nobody ever needs to know or recover the old password.
$tempPassword = substr(bin2hex(random_bytes(4)), 0, 8);
$hash = password_hash($tempPassword, PASSWORD_DEFAULT);
$pdo->prepare('UPDATE users SET password_hash = ? WHERE user_id = ?')->execute([$hash, $trainer['user_id']]);

redirect_with_flash('modules/trainers/index.php', 'success', "Password reset for {$trainer['trainer_code']}. New temporary password: {$tempPassword} (share with the trainer).");
