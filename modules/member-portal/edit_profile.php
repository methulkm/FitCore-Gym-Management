<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['member']);

$userId = current_user()['user_id'];
$stmt = $pdo->prepare('SELECT * FROM members WHERE user_id = ?');
$stmt->execute([$userId]);
$member = $stmt->fetch();
if (!$member) redirect_with_flash('auth/login.php', 'error', 'No member profile linked to this account.');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify('modules/member-portal/edit_profile.php');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // FR-05: member may only edit phone/address - name, NIC, email stay admin-managed.
    v_push($errors, v_required($phone, 'Phone number'));
    v_push($errors, v_phone($phone));

    if (!$errors) {
        $pdo->prepare('UPDATE members SET phone = ?, address = ? WHERE member_id = ?')
            ->execute([$phone, $address, $member['member_id']]);
        redirect_with_flash('modules/member-portal/index.php', 'success', 'Profile updated.');
    }
}

$pageTitle = 'Edit My Profile';
$activeNav = 'member-portal';
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
      <label class="text-xs font-bold text-slate-500">Full Name <span class="text-slate-300">(admin managed)</span></label>
      <input value="<?= e($member['full_name']) ?>" disabled class="mt-1 w-full border border-slate-200 bg-slate-50 rounded-[10px] px-3.5 py-2.5 text-sm text-slate-400">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Phone</label>
      <input name="phone" required value="<?= e($_POST['phone'] ?? $member['phone']) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Email <span class="text-slate-300">(admin managed)</span></label>
      <input value="<?= e($member['email']) ?>" disabled class="mt-1 w-full border border-slate-200 bg-slate-50 rounded-[10px] px-3.5 py-2.5 text-sm text-slate-400">
    </div>
    <div class="col-span-2">
      <label class="text-xs font-bold text-slate-500">Address</label>
      <input name="address" value="<?= e($_POST['address'] ?? $member['address']) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div class="col-span-2 flex gap-3 pt-2">
      <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-[10px] px-5 py-2.5 text-sm">Save Changes</button>
      <?= btn('Cancel', base_url('modules/member-portal/index.php'), 'ghost') ?>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
