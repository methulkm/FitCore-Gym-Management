<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login(); // any logged-in role (admin/member/trainer) can change their own password

$user = current_user();
$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE user_id = ?');
    $stmt->execute([$user['user_id']]);
    $hash = $stmt->fetchColumn();

    if (!password_verify($current, $hash)) $errors[] = 'Current password is incorrect.';
    if (strlen($new) < 8) $errors[] = 'New password must be at least 8 characters.';
    if ($new !== $confirm) $errors[] = 'New password and confirmation do not match.';

    if (!$errors) {
        $pdo->prepare('UPDATE users SET password_hash = ? WHERE user_id = ?')
            ->execute([password_hash($new, PASSWORD_DEFAULT), $user['user_id']]);
        $success = 'Password changed successfully.';
    }
}

$pageTitle = 'Change Password';
$activeNav = '';
require __DIR__ . '/../includes/layout_start.php';
?>
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 max-w-md">
  <?php foreach ($errors as $err): ?>
    <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium px-4 py-3 mb-4"><?= e($err) ?></div>
  <?php endforeach; ?>
  <?php if ($success): ?>
    <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-medium px-4 py-3 mb-4"><?= e($success) ?></div>
  <?php endif; ?>
  <form method="post" class="space-y-4">
    <div>
      <label class="text-xs font-bold text-slate-500">Current Password</label>
      <input type="password" name="current_password" required class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">New Password</label>
      <input type="password" name="new_password" required minlength="8" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
      <p class="text-xs text-slate-400 mt-1">At least 8 characters.</p>
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Confirm New Password</label>
      <input type="password" name="confirm_password" required minlength="8" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-[10px] px-5 py-2.5 text-sm">Update Password</button>
  </form>
</div>
<?php require __DIR__ . '/../includes/layout_end.php'; ?>
