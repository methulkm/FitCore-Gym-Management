<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$trainers = $pdo->query("SELECT trainer_id, full_name FROM trainers WHERE status = 'active' ORDER BY full_name")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $className = trim($_POST['class_name'] ?? '');
    $trainerId = (int) ($_POST['trainer_id'] ?? 0);
    $classDate = $_POST['class_date'] ?? '';
    $startTime = $_POST['start_time'] ?? '';
    $duration = (int) ($_POST['duration_minutes'] ?? 60);
    $capacity = (int) ($_POST['capacity'] ?? 0); // BR-11: admin sets capacity per class

    if ($className === '') $errors[] = 'Class name is required.';
    if (!$trainerId) $errors[] = 'Please choose a trainer.';
    if (!$classDate || !$startTime) $errors[] = 'Date and start time are required.';
    if ($capacity <= 0) $errors[] = 'Capacity must be greater than 0.';

    if (!$errors) {
        // BR-13: prevent booking the same trainer for an overlapping session.
        $check = $pdo->prepare("SELECT COUNT(*) FROM classes WHERE trainer_id = ? AND class_date = ? AND start_time = ? AND status = 'scheduled'");
        $check->execute([$trainerId, $classDate, $startTime]);
        if ($check->fetchColumn() > 0) {
            $errors[] = 'This trainer already has a class scheduled at that exact date/time.';
        }
    }

    if (!$errors) {
        $pdo->prepare('INSERT INTO classes (class_name, trainer_id, class_date, start_time, duration_minutes, capacity, status) VALUES (?, ?, ?, ?, ?, ?, "scheduled")')
            ->execute([$className, $trainerId, $classDate, $startTime, $duration, $capacity]);
        redirect_with_flash('modules/classes/index.php', 'success', "Class \"{$className}\" scheduled.");
    }
}

$pageTitle = 'Add Class';
$activeNav = 'classes';
$ownerTag = 'Senuka';
require __DIR__ . '/../../includes/layout_start.php';
?>
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 max-w-xl">
  <?php foreach ($errors as $err): ?>
    <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium px-4 py-3 mb-4"><?= e($err) ?></div>
  <?php endforeach; ?>
  <form method="post" class="grid grid-cols-2 gap-4">
    <div class="col-span-2">
      <label class="text-xs font-bold text-slate-500">Class Name</label>
      <input name="class_name" required value="<?= e($_POST['class_name'] ?? '') ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div class="col-span-2">
      <label class="text-xs font-bold text-slate-500">Trainer</label>
      <select name="trainer_id" required class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
        <option value="">Select a trainer</option>
        <?php foreach ($trainers as $t): ?>
          <option value="<?= (int) $t['trainer_id'] ?>"><?= e($t['full_name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Date</label>
      <input type="date" name="class_date" required class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Start Time</label>
      <input type="time" name="start_time" required class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Duration (minutes)</label>
      <input type="number" name="duration_minutes" value="60" min="15" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Capacity</label>
      <input type="number" name="capacity" required min="1" value="<?= e($_POST['capacity'] ?? '20') ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div class="col-span-2 flex gap-3 pt-2">
      <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-[10px] px-5 py-2.5 text-sm">Save Class</button>
      <?= btn('Cancel', base_url('modules/classes/index.php'), 'ghost') ?>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
