<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$errors = [];
$tempPassword = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $qualification = trim($_POST['qualification'] ?? '');

    v_push($errors, v_required($fullName, 'Full name'));
    v_push($errors, v_required($email, 'Email'));
    v_push($errors, v_email($email));
    v_push($errors, v_phone($phone));

    $upload = handle_image_upload($_FILES['profile_image'] ?? null, 'trainers');
    if (isset($upload['error'])) $errors[] = $upload['error'];

    if (!$errors) {
        $pdo->beginTransaction();
        try {
            // UC-05: system generates trainer code AND a login account (unlike general employees).
            $tempPassword = substr(bin2hex(random_bytes(4)), 0, 8);
            $hash = password_hash($tempPassword, PASSWORD_DEFAULT);
            $pdo->prepare('INSERT INTO users (email, password_hash, role, status) VALUES (?, ?, "trainer", "active")')->execute([$email, $hash]);
            $userId = $pdo->lastInsertId();

            $code = next_code($pdo, 'trainers', 'trainer_code', 'TRN');
            $pdo->prepare('INSERT INTO trainers (trainer_code, user_id, full_name, email, phone, specialization, qualification, profile_image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, "active")')
                ->execute([$code, $userId, $fullName, $email, $phone, $specialization, $qualification, $upload['path']]);
            $pdo->commit();

            redirect_with_flash('modules/trainers/index.php', 'success', "Trainer {$code} created. Temporary login password: {$tempPassword}.");
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Could not create trainer: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Add Trainer';
$activeNav = 'trainers';
$ownerTag = 'Senuka';
require __DIR__ . '/../../includes/layout_start.php';
?>
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 max-w-xl">
  <?php foreach ($errors as $err): ?>
    <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium px-4 py-3 mb-4"><?= e($err) ?></div>
  <?php endforeach; ?>
  <form method="post" enctype="multipart/form-data" class="grid grid-cols-2 gap-4">
    <div class="col-span-2">
      <label class="text-xs font-bold text-slate-500">Full Name</label>
      <input name="full_name" required value="<?= e($_POST['full_name'] ?? '') ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Email (login)</label>
      <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Phone</label>
      <input name="phone" placeholder="0771234567" value="<?= e($_POST['phone'] ?? '') ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Specialization</label>
      <input name="specialization" placeholder="CrossFit, Yoga, Bodybuilding..." value="<?= e($_POST['specialization'] ?? '') ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Qualification</label>
      <input name="qualification" value="<?= e($_POST['qualification'] ?? '') ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div class="col-span-2">
      <label class="text-xs font-bold text-slate-500">Profile Photo (optional, JPG/PNG/WEBP, max 3MB)</label>
      <input type="file" name="profile_image" accept=".jpg,.jpeg,.png,.webp" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm bg-white">
    </div>
    <div class="col-span-2 flex gap-3 pt-2">
      <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-[10px] px-5 py-2.5 text-sm">Save Trainer</button>
      <?= btn('Cancel', base_url('modules/trainers/index.php'), 'ghost') ?>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
