<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['trainer']);

$userId = current_user()['user_id'];
$trainer = $pdo->prepare('SELECT * FROM trainers WHERE user_id = ?');
$trainer->execute([$userId]);
$trainer = $trainer->fetch();
if (!$trainer) redirect_with_flash('auth/login.php', 'error', 'No trainer profile linked to this account.');

$classes = $pdo->prepare("SELECT c.*, (SELECT COUNT(*) FROM bookings b WHERE b.class_id = c.class_id AND b.status='booked') AS booked_count
                           FROM classes c WHERE c.trainer_id = ? AND c.class_date >= CURDATE() AND c.status = 'scheduled'
                           ORDER BY c.class_date, c.start_time");
$classes->execute([$trainer['trainer_id']]);
$classes = $classes->fetchAll();

$availability = $pdo->prepare('SELECT * FROM trainer_availability WHERE trainer_id = ? AND day_date >= CURDATE() ORDER BY day_date, start_time LIMIT 8');
$availability->execute([$trainer['trainer_id']]);
$availability = $availability->fetchAll();

$pageTitle = 'My Trainer Dashboard';
$activeNav = 'trainer-portal';
require __DIR__ . '/../../includes/layout_start.php';
?>

<div class="grid grid-cols-2 gap-4">
  <?= kpi_card('Upcoming Classes', (string) count($classes)) ?>
  <?= kpi_card('Specialization', $trainer['specialization'] ?: '-') ?>
</div>

<div class="grid md:grid-cols-2 gap-6">
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
    <h3 class="font-extrabold mb-3">My Upcoming Classes</h3>
    <div class="space-y-2">
      <?php foreach ($classes as $c): ?>
        <div class="border-b border-slate-100 pb-2">
          <div class="flex justify-between text-sm font-semibold">
            <span><?= e($c['class_name']) ?></span>
            <span class="text-slate-500"><?= e(date('D, d M', strtotime($c['class_date']))) ?></span>
          </div>
          <p class="text-xs text-slate-500"><?= e(date('h:i A', strtotime($c['start_time']))) ?> &middot; <?= (int) $c['booked_count'] ?>/<?= (int) $c['capacity'] ?> booked</p>
        </div>
      <?php endforeach; ?>
      <?php if (!$classes): ?><p class="text-slate-400 text-sm">No upcoming classes assigned.</p><?php endif; ?>
    </div>
  </div>
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
    <h3 class="font-extrabold mb-3">My Availability</h3>
    <div class="space-y-2">
      <?php foreach ($availability as $a): ?>
        <div class="flex justify-between text-sm border-b border-slate-100 pb-2">
          <span><?= e(date('D, d M Y', strtotime($a['day_date']))) ?></span>
          <span class="text-slate-500"><?= e(date('h:i A', strtotime($a['start_time']))) ?> - <?= e(date('h:i A', strtotime($a['end_time']))) ?></span>
        </div>
      <?php endforeach; ?>
      <?php if (!$availability): ?><p class="text-slate-400 text-sm">No availability slots set yet.</p><?php endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
