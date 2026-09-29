<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM trainers WHERE trainer_id = ?');
$stmt->execute([$id]);
$trainer = $stmt->fetch();
if (!$trainer) redirect_with_flash('modules/trainers/index.php', 'error', 'Trainer not found.');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify('modules/trainers/index.php');
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $qualification = trim($_POST['qualification'] ?? '');
    $removePhoto = isset($_POST['remove_photo']);

    v_push($errors, v_required($fullName, 'Full name'));
    v_push($errors, v_phone($phone));

    $upload = handle_image_upload($_FILES['profile_image'] ?? null, 'trainers');
    if (isset($upload['error'])) $errors[] = $upload['error'];

    if (!$errors) {
        $newPhoto = $trainer['profile_image'];
        if ($upload['path']) {
            delete_uploaded_file($trainer['profile_image']);
            $newPhoto = $upload['path'];
        } elseif ($removePhoto) {
            delete_uploaded_file($trainer['profile_image']);
            $newPhoto = null;
        }

        $pdo->prepare('UPDATE trainers SET full_name=?, phone=?, specialization=?, qualification=?, profile_image=? WHERE trainer_id=?')
            ->execute([$fullName, $phone, $specialization, $qualification, $newPhoto, $id]);
        redirect_with_flash('modules/trainers/index.php', 'success', "Trainer {$trainer['trainer_code']} updated.");
    }
}

$pageTitle = 'Edit Trainer';
$activeNav = 'trainers';
$ownerTag = 'Senuka';
require __DIR__ . '/../../includes/layout_start.php';
?>
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 max-w-xl">
  <p class="text-teal-600 text-xs font-bold mb-4"><?= e($trainer['trainer_code']) ?></p>
  <?php foreach ($errors as $err): ?>
    <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium px-4 py-3 mb-4"><?= e($err) ?></div>
  <?php endforeach; ?>
  <form method="post" enctype="multipart/form-data" class="grid grid-cols-2 gap-4">
    <?= csrf_field() ?>
    <div class="col-span-2">
      <label class="text-xs font-bold text-slate-500">Full Name</label>
      <input name="full_name" required value="<?= e($_POST['full_name'] ?? $trainer['full_name']) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Phone</label>
      <input name="phone" placeholder="0771234567" value="<?= e($_POST['phone'] ?? $trainer['phone']) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Specialization</label>
      <input name="specialization" value="<?= e($_POST['specialization'] ?? $trainer['specialization']) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div class="col-span-2">
      <label class="text-xs font-bold text-slate-500">Qualification</label>
      <input name="qualification" value="<?= e($_POST['qualification'] ?? $trainer['qualification']) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div class="col-span-2">
      <label class="text-xs font-bold text-slate-500">Profile Photo</label>
      <?php if ($trainer['profile_image']): ?>
        <div class="mt-1 flex items-center gap-3 mb-2">
          <img src="<?= e(base_url($trainer['profile_image'])) ?>" class="w-14 h-14 rounded-full object-cover border border-slate-200">
          <label class="text-xs text-rose-500 font-semibold flex items-center gap-1.5">
            <input type="checkbox" name="remove_photo" value="1"> Remove current photo
          </label>
        </div>
      <?php endif; ?>
      <input type="file" name="profile_image" accept=".jpg,.jpeg,.png,.webp" class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm bg-white">
      <p class="text-xs text-slate-400 mt-1">Upload a new photo to replace the current one (JPG/PNG/WEBP, max 3MB).</p>
    </div>
    <div class="col-span-2 flex gap-3 pt-2">
      <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-[10px] px-5 py-2.5 text-sm">Save Changes</button>
      <?= btn('Cancel', base_url('modules/trainers/index.php'), 'ghost') ?>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
