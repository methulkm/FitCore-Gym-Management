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
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($fullName === '') $errors[] = 'Full name is required.';
    if ($phone === '') $errors[] = 'Phone number is required.';

    if (!$errors) {
        $pdo->prepare('UPDATE members SET full_name = ?, phone = ?, address = ?, email = ? WHERE member_id = ?')
            ->execute([$fullName, $phone, $address, $email, $id]);
        redirect_with_flash('modules/members/index.php', 'success', "Member {$member['member_code']} updated.");
    }
}

$pageTitle = 'Edit Member';
$activeNav = 'members';
$ownerTag = 'Sajatha';
require __DIR__ . '/../../includes/layout_start.php';
?>
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 max-w-2xl">
  <p class="text-teal-600 text-xs font-bold mb-4"><?= e($member['member_code']) ?></p>
  <?php foreach ($errors as $err): ?>
    <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium px-4 py-3 mb-4"><?= e($err) ?></div>
  <?php endforeach; ?>
  <form method="post" class="grid grid-cols-2 gap-4">
    <div class="col-span-2">
      <label class="text-xs font-bold text-slate-500">Full Name</label>
      <input name="full_name" required value="<?= e($_POST['full_name'] ?? $member['full_name']) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">NIC / Passport <span class="text-slate-300">(locked &mdash; UC-02)</span></label>
      <input value="<?= e($member['nic_passport']) ?>" disabled class="mt-1 w-full border border-slate-200 bg-slate-50 rounded-[10px] px-3.5 py-2.5 text-sm text-slate-400">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Phone</label>
      <input name="phone" required value="<?= e($_POST['phone'] ?? $member['phone']) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div class="col-span-2">
      <label class="text-xs font-bold text-slate-500">Address</label>
      <input name="address" value="<?= e($_POST['address'] ?? $member['address']) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div class="col-span-2">
      <label class="text-xs font-bold text-slate-500">Email</label>
      <input type="email" name="email" value="<?= e($_POST['email'] ?? $member['email']) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div class="col-span-2 flex gap-3 pt-2">
      <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-[10px] px-5 py-2.5 text-sm">Save Changes</button>
      <?= btn('Cancel', base_url('modules/members/index.php'), 'ghost') ?>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
