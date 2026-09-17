<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

// history_count so the UI only offers a hard Delete when it's safe. Availability slots are just
// disposable schedule config (they cascade away harmlessly); classes/bookings are real assignments
// and history, so a trainer who has ever been assigned one must be Deactivated instead (SRS 3.1).
$trainers = $pdo->query(
    'SELECT t.*,
        (SELECT COUNT(*) FROM classes WHERE trainer_id = t.trainer_id) +
        (SELECT COUNT(*) FROM bookings WHERE trainer_id = t.trainer_id) AS history_count
     FROM trainers t ORDER BY t.trainer_id DESC'
)->fetchAll();

$pageTitle = 'Trainers & Schedule Management';
$pageSubtitle = 'Credentials, specializations, working-hours availability and class assignments (FR-06, FR-08, FR-09, FR-10, UC-05, UC-06)';
$activeNav = 'trainers';
$ownerTag = 'Senuka';
$headerActions = btn('+ Add Trainer', base_url('modules/trainers/create.php'));
require __DIR__ . '/../../includes/layout_start.php';
?>



<div class="grid md:grid-cols-3 gap-5">
  <?php foreach ($trainers as $t): ?>
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
      <div class="flex items-center gap-3 mb-3">
        <span class="w-11 h-11 rounded-full bg-teal-600 text-white flex items-center justify-center font-bold"><?= e(strtoupper(substr($t['full_name'], 0, 2))) ?></span>
        <div>
          <p class="font-extrabold text-slate-900"><?= e($t['full_name']) ?></p>
          <p class="text-teal-600 text-xs font-bold"><?= e($t['trainer_code']) ?></p>
        </div>
      </div>
      <p class="text-sm text-slate-600 mb-1"><b>Specialization:</b> <?= e($t['specialization']) ?></p>
      <p class="text-sm text-slate-600 mb-3"><b>Qualification:</b> <?= e($t['qualification']) ?></p>
      <div class="flex items-center justify-between pt-3 border-t border-slate-100">
        <?= status_badge(ucfirst($t['status']), $t['status'] === 'active' ? 'emerald' : 'rose') ?>
        <div class="space-x-3">
          <a href="<?= e(base_url('modules/trainers/availability.php?trainer_id=' . $t['trainer_id'])) ?>" class="text-slate-500 hover:text-teal-600 text-xs font-bold">Availability</a>
          <a href="<?= e(base_url('modules/trainers/edit.php?id=' . $t['trainer_id'])) ?>" class="text-slate-500 hover:text-teal-600 text-xs font-bold">Edit</a>
          <?php if ($t['user_id']): ?>
            <a href="<?= e(base_url('modules/trainers/reset_password.php?id=' . $t['trainer_id'])) ?>" onclick="return confirm('Reset this trainer\'s password? A new temporary password will be generated.')" class="text-indigo-500 hover:text-indigo-700 text-xs font-bold">Reset Password</a>
          <?php endif; ?>
          <?php if ($t['status'] === 'active'): ?>
            <?= delete_link(base_url('modules/trainers/delete.php?id=' . $t['trainer_id']), 'Deactivate this trainer? (history preserved)', 'Deactivate') ?>
          <?php else: ?>
            <a href="<?= e(base_url('modules/trainers/delete.php?id=' . $t['trainer_id'] . '&reactivate=1')) ?>" class="text-emerald-600 hover:text-emerald-700 text-xs font-bold">Reactivate</a>
          <?php endif; ?>
          <?php if ((int) $t['history_count'] === 0): ?>
            <?= delete_link(base_url('modules/trainers/hard_delete.php?id=' . $t['trainer_id']), 'Permanently delete this trainer? This cannot be undone (only allowed because they have no classes or bookings yet).', 'Delete') ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$trainers): ?><p class="text-slate-400">No trainers yet.</p><?php endif; ?>
</div>

<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
