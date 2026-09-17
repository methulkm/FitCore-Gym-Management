<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$trainerId = (int) ($_GET['trainer_id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM trainers WHERE trainer_id = ?');
$stmt->execute([$trainerId]);
$trainer = $stmt->fetch();
if (!$trainer) redirect_with_flash('modules/trainers/index.php', 'error', 'Trainer not found.');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dayDate = $_POST['day_date'] ?? '';
    $startTime = $_POST['start_time'] ?? '';
    $endTime = $_POST['end_time'] ?? '';

    if (!$dayDate || !$startTime || !$endTime) $errors[] = 'All fields are required.';
    elseif ($endTime <= $startTime) $errors[] = 'End time must be after start time.'; // UC-06 validation

    if (!$errors) {
        $pdo->prepare('INSERT INTO trainer_availability (trainer_id, day_date, start_time, end_time) VALUES (?, ?, ?, ?)')
            ->execute([$trainerId, $dayDate, $startTime, $endTime]);
        redirect_with_flash('modules/trainers/availability.php?trainer_id=' . $trainerId, 'success', 'Availability slot added.');
    }
}

$slots = $pdo->prepare('SELECT * FROM trainer_availability WHERE trainer_id = ? ORDER BY day_date, start_time');
$slots->execute([$trainerId]);
$slots = $slots->fetchAll();

$pageTitle = 'Availability - ' . $trainer['full_name'];
$activeNav = 'trainers';
$ownerTag = 'Senuka';
require __DIR__ . '/../../includes/layout_start.php';
?>
<div class="grid lg:grid-cols-2 gap-6">
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
    <h3 class="font-extrabold mb-4">Add Availability Slot</h3>
    <?php foreach ($errors as $err): ?>
      <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium px-4 py-3 mb-4"><?= e($err) ?></div>
    <?php endforeach; ?>
    <form method="post" class="space-y-4">
      <div>
        <label class="text-xs font-bold text-slate-500">Day / Date</label>
        <input type="date" name="day_date" required class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="text-xs font-bold text-slate-500">Start Time</label>
          <input type="time" name="start_time" required class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
        </div>
        <div>
          <label class="text-xs font-bold text-slate-500">End Time</label>
          <input type="time" name="end_time" required class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
        </div>
      </div>
      <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-[10px] px-5 py-2.5 text-sm">Add Slot</button>
    </form>
  </div>
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
    <h3 class="font-extrabold mb-4">Upcoming Availability</h3>
    <div class="space-y-2">
      <?php foreach ($slots as $s): ?>
        <div class="flex items-center justify-between text-sm border-b border-slate-100 pb-2">
          <span class="font-medium"><?= e(date('D, d M Y', strtotime($s['day_date']))) ?></span>
          <span class="text-slate-500"><?= e(date('h:i A', strtotime($s['start_time']))) ?> - <?= e(date('h:i A', strtotime($s['end_time']))) ?></span>
        </div>
      <?php endforeach; ?>
      <?php if (!$slots): ?><p class="text-slate-400 text-sm">No availability slots yet.</p><?php endif; ?>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
