<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$sql = "SELECT c.*, t.full_name AS trainer_name,
        (SELECT COUNT(*) FROM bookings b WHERE b.class_id = c.class_id AND b.status = 'booked') AS booked_count
        FROM classes c JOIN trainers t ON t.trainer_id = c.trainer_id
        ORDER BY c.class_date, c.start_time";
$classes = $pdo->query($sql)->fetchAll();

$pageTitle = 'Class / PT Booking Management';
$pageSubtitle = 'Live capacity, 1-hour PT sessions, conflict prevention & 2-hour cancellation rule (FR-08, FR-11, FR-12, BR-10..BR-14)';
$activeNav = 'classes';
$ownerTag = 'Senuka';
$headerActions = btn('+ Add Class', base_url('modules/classes/create.php'));
require __DIR__ . '/../../includes/layout_start.php';
?>

<div class="grid md:grid-cols-2 xl:grid-cols-3 gap-5">
  <?php foreach ($classes as $c):
    $pct = $c['capacity'] > 0 ? min(100, round($c['booked_count'] / $c['capacity'] * 100)) : 0;
    $full = $c['booked_count'] >= $c['capacity'];
  ?>
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
      <div class="flex items-center justify-between mb-2">
        <h3 class="font-extrabold text-slate-900"><?= e($c['class_name']) ?></h3>
        <?= status_badge(ucfirst($c['status']), match ($c['status']) { 'scheduled' => 'emerald', 'completed' => 'indigo', default => 'rose' }) ?>
      </div>
      <p class="text-sm text-slate-500 mb-1">Trainer: <b class="text-slate-700"><?= e($c['trainer_name']) ?></b></p>
      <p class="text-sm text-slate-500 mb-3"><?= e(date('D, d M', strtotime($c['class_date']))) ?> &middot; <?= e(date('h:i A', strtotime($c['start_time']))) ?> &middot; <?= (int) $c['duration_minutes'] ?>min</p>
      <div class="mb-3">
        <div class="flex justify-between text-xs font-bold text-slate-500 mb-1">
          <span><?= (int) $c['booked_count'] ?>/<?= (int) $c['capacity'] ?> booked</span>
          <span><?= $full ? 'FULL' : $pct . '%' ?></span>
        </div>
        <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
          <div class="h-full <?= $full ? 'bg-rose-500' : 'bg-teal-500' ?>" style="width: <?= $pct ?>%"></div>
        </div>
      </div>
      <div class="flex items-center justify-between pt-3 border-t border-slate-100 text-xs font-bold">
        <a href="<?= e(base_url('modules/classes/bookings.php?class_id=' . $c['class_id'])) ?>" class="text-teal-600">Manage Bookings</a>
        <div class="space-x-3">
          <a href="<?= e(base_url('modules/classes/edit.php?id=' . $c['class_id'])) ?>" class="text-slate-500 hover:text-teal-600">Edit</a>
          <?php if ($c['status'] === 'scheduled'): ?>
            <?= delete_link(base_url('modules/classes/cancel.php?id=' . $c['class_id']), 'Cancel this class?', 'Cancel') ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$classes): ?><p class="text-slate-400">No classes scheduled yet.</p><?php endif; ?>
</div>

<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
