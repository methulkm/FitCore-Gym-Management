<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM members WHERE member_id = ?');
$stmt->execute([$id]);
$member = $stmt->fetch();
if (!$member) redirect_with_flash('modules/members/index.php', 'error', 'Member not found.');

if (!$member['user_id']) {
    redirect_with_flash('modules/members/index.php', 'error', "{$member['member_code']} doesn't have a login account yet (no email was set when they were created).");
}

// BR-21: admin resets a forgotten password - generate a fresh temp password, hash it, and
// OVERWRITE the old hash. Nobody ever needs to know or recover the old password.
$tempPassword = substr(bin2hex(random_bytes(4)), 0, 8);
$hash = password_hash($tempPassword, PASSWORD_DEFAULT);
$pdo->prepare('UPDATE users SET password_hash = ? WHERE user_id = ?')->execute([$hash, $member['user_id']]);

redirect_with_flash('modules/members/index.php', 'success', "Password reset for {$member['member_code']}. New temporary password: {$tempPassword} (share with the member).");
