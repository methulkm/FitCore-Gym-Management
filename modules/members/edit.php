<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM members WHERE member_id = ?');
$stmt->execute([$id]);
$member = $stmt->fetch();
if (!$member) redirect_with_flash('modules/members/index.php', 'error', 'Member not found.');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify('modules/members/index.php');
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));

    v_push($errors, v_required($fullName, 'Full name'));
    v_push($errors, v_person_name($fullName));
    v_push($errors, v_required($phone, 'Phone number'));
    v_push($errors, v_phone($phone));
    v_push($errors, v_free_text($address, 'Address', 255));
    v_push($errors, v_email($email));

    // The email doubles as the portal login for members who have one, so it must stay present and unique.
    if (!$errors && $member['user_id']) {
        if ($email === '') {
            $errors[] = 'Email cannot be removed because this member has a portal login.';
        } else {
            $dup = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ? AND user_id <> ?');
            $dup->execute([$email, $member['user_id']]);
            if ($dup->fetchColumn() > 0) $errors[] = 'This email is already used by another login account.';
        }
    } elseif (!$errors && $email !== '') {
        $dup = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
        $dup->execute([$email]);
        if ($dup->fetchColumn() > 0) $errors[] = 'This email is already used by another login account.';
    }

    if (!$errors) {
        $pdo->beginTransaction();
        $pdo->prepare('UPDATE members SET full_name = ?, phone = ?, address = ?, email = ? WHERE member_id = ?')
            ->execute([$fullName, $phone, $address, $email ?: null, $id]);
        if ($member['user_id']) {
            $pdo->prepare('UPDATE users SET email = ? WHERE user_id = ?')->execute([$email, $member['user_id']]);
        }
        $pdo->commit();
        redirect_with_flash('modules/members/index.php', 'success', "Member {$member['member_code']} updated.");
    }
}

$pageTitle = 'Edit Member';
$activeNav = 'members';
require __DIR__ . '/../../includes/layout_start.php';
?>
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 max-w-2xl">
  <p class="text-teal-600 text-xs font-bold mb-4"><?= e($member['member_code']) ?></p>
  <?php foreach ($errors as $err): ?>
    <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium px-4 py-3 mb-4"><?= e($err) ?></div>
  <?php endforeach; ?>
  <form method="post" class="grid grid-cols-2 gap-4">
    <?= csrf_field() ?>
    <div class="col-span-2">
      <label class="text-xs font-bold text-slate-500">Full Name</label>
      <input name="full_name" required maxlength="120" value="<?= e(old('full_name', $member['full_name'])) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">NIC / Passport <span class="text-slate-300">(locked)</span></label>
      <input value="<?= e($member['nic_passport']) ?>" disabled class="mt-1 w-full border border-slate-200 bg-slate-50 rounded-[10px] px-3.5 py-2.5 text-sm text-slate-400">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Phone</label>
      <input name="phone" required maxlength="12" value="<?= e(old('phone', $member['phone'])) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div class="col-span-2">
      <label class="text-xs font-bold text-slate-500">Address</label>
      <input name="address" maxlength="255" value="<?= e(old('address', $member['address'])) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div class="col-span-2">
      <label class="text-xs font-bold text-slate-500">Email</label>
      <input type="email" name="email" maxlength="150" value="<?= e(old('email', $member['email'])) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div class="col-span-2 flex gap-3 pt-2">
      <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-[10px] px-5 py-2.5 text-sm">Save Changes</button>
      <?= btn('Cancel', base_url('modules/members/index.php'), 'ghost') ?>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
