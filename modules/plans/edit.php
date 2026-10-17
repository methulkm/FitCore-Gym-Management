<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM membership_plans WHERE plan_id = ?');
$stmt->execute([$id]);
$plan = $stmt->fetch();
if (!$plan) redirect_with_flash('modules/plans/index.php', 'error', 'Plan not found.');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify('modules/plans/index.php');
    $planName = trim($_POST['plan_name'] ?? '');
    $duration = trim($_POST['duration_months'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $perks = trim($_POST['perks'] ?? '');
    $featured = isset($_POST['is_featured']) ? 1 : 0;

    [$errors, $description] = validate_plan_form($pdo, $planName, $duration, $price, $perks, $id);

    if (!$errors) {
        $pdo->prepare('UPDATE membership_plans SET plan_name=?, duration_months=?, price=?, description=?, is_featured=? WHERE plan_id=?')
            ->execute([$planName, (int) $duration, (float) $price, $description, $featured, $id]);
        redirect_with_flash('modules/plans/index.php', 'success', "Plan \"{$planName}\" updated.");
    }
}

$perksText = implode("\n", explode('|', $plan['description']));
$pageTitle = 'Edit Membership Plan';
$activeNav = 'plans';
require __DIR__ . '/../../includes/layout_start.php';
?>
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 max-w-xl">
  <?php foreach ($errors as $err): ?>
    <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium px-4 py-3 mb-4"><?= e($err) ?></div>
  <?php endforeach; ?>
  <form method="post" class="space-y-4">
    <?= csrf_field() ?>
    <div>
      <label class="text-xs font-bold text-slate-500">Plan Name</label>
      <input name="plan_name" required maxlength="80" value="<?= e(old('plan_name', $plan['plan_name'])) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="text-xs font-bold text-slate-500">Duration (months)</label>
        <input type="number" min="1" max="36" step="1" name="duration_months" required value="<?= e(old('duration_months', $plan['duration_months'])) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
      </div>
      <div>
        <label class="text-xs font-bold text-slate-500">Price (LKR)</label>
        <input type="number" step="0.01" min="0.01" max="1000000" name="price" required value="<?= e(old('price', $plan['price'])) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
      </div>
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Perks (one per line)</label>
      <textarea name="perks" rows="4" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm"><?= e(old('perks', $perksText)) ?></textarea>
    </div>
    <label class="flex items-center gap-2 text-sm font-medium text-slate-600">
      <input type="checkbox" name="is_featured" <?= (!empty($_POST['is_featured']) || (!isset($_POST['plan_name']) && $plan['is_featured'])) ? 'checked' : '' ?>> Mark as "Most Popular" (featured)
    </label>
    <div class="flex gap-3 pt-2">
      <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-[10px] px-5 py-2.5 text-sm">Save Changes</button>
      <?= btn('Cancel', base_url('modules/plans/index.php'), 'ghost') ?>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
