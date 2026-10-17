<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$errors = [];
$tempPassword = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify('modules/members/create.php');
    $fullName = trim($_POST['full_name'] ?? '');
    $nic = strtoupper(trim($_POST['nic_passport'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $joinDate = trim($_POST['join_date'] ?? '') ?: date('Y-m-d');

    v_push($errors, v_required($fullName, 'Full name'));
    v_push($errors, v_person_name($fullName));
    v_push($errors, v_required($nic, 'NIC / Passport'));
    v_push($errors, v_nic_or_passport($nic));
    v_push($errors, v_required($phone, 'Phone number'));
    v_push($errors, v_phone($phone));
    v_push($errors, v_free_text($address, 'Address', 255));
    v_push($errors, v_email($email));
    v_push($errors, v_date($joinDate, 'Join date'));
    if (!v_date($joinDate, 'Join date') && $joinDate > date('Y-m-d')) $errors[] = 'Join date cannot be in the future.';

    if (!$errors) {
        $dupNic = $pdo->prepare('SELECT member_code FROM members WHERE nic_passport = ?');
        $dupNic->execute([$nic]);
        if ($code = $dupNic->fetchColumn()) $errors[] = "A member with this NIC already exists ({$code}).";

        if ($email !== '') {
            $dupEmail = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
            $dupEmail->execute([$email]);
            if ($dupEmail->fetchColumn() > 0) $errors[] = 'This email is already used by another login account.';
        }
    }

    if (!$errors) {
        $pdo->beginTransaction();
        try {
            $userId = null;
            if ($email !== '') {
                // UC-02 step 5: system generates a temporary password for the new member's login.
                $tempPassword = substr(bin2hex(random_bytes(4)), 0, 8);
                $hash = password_hash($tempPassword, PASSWORD_DEFAULT);
                $pdo->prepare('INSERT INTO users (email, password_hash, role, status) VALUES (?, ?, "member", "active")')
                    ->execute([$email, $hash]);
                $userId = $pdo->lastInsertId();
            }

            $code = next_code($pdo, 'members', 'member_code', 'MEM'); // BR-02
            $pdo->prepare('INSERT INTO members (member_code, user_id, full_name, nic_passport, phone, address, email, join_date, account_status)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, "active")')
                ->execute([$code, $userId, $fullName, $nic, $phone, $address, $email ?: null, $joinDate]);

            $pdo->commit();

            $msg = "Member {$code} created.";
            if ($tempPassword) $msg .= " Temporary login password: {$tempPassword} (share with the member; BR-21 admin-issued).";
            redirect_with_flash('modules/members/index.php', 'success', $msg);
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Could not create the member. Please try again.';
        }
    }
}

$pageTitle = 'Add Member';
$activeNav = 'members';
require __DIR__ . '/../../includes/layout_start.php';
?>
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 max-w-2xl">
  <?php foreach ($errors as $err): ?>
    <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium px-4 py-3 mb-4"><?= e($err) ?></div>
  <?php endforeach; ?>
  <form method="post" class="grid grid-cols-2 gap-4">
    <?= csrf_field() ?>
    <div class="col-span-2">
      <label class="text-xs font-bold text-slate-500">Full Name</label>
      <input name="full_name" required maxlength="120" value="<?= e(old('full_name')) ?>" placeholder="e.g. Kasun Perera" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">NIC / Passport</label>
      <input name="nic_passport" required maxlength="12" value="<?= e(old('nic_passport')) ?>" placeholder="200012345678 or 991234567V" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Phone</label>
      <input name="phone" required maxlength="12" value="<?= e(old('phone')) ?>" placeholder="0771234567" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div class="col-span-2">
      <label class="text-xs font-bold text-slate-500">Address</label>
      <input name="address" maxlength="255" value="<?= e(old('address')) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Email (optional &mdash; creates a portal login)</label>
      <input type="email" name="email" maxlength="150" value="<?= e(old('email')) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Join Date</label>
      <input type="date" name="join_date" max="<?= e(date('Y-m-d')) ?>" value="<?= e(old('join_date', date('Y-m-d'))) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div class="col-span-2 flex gap-3 pt-2">
      <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-[10px] px-5 py-2.5 text-sm">Save Member</button>
      <?= btn('Cancel', base_url('modules/members/index.php'), 'ghost') ?>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
