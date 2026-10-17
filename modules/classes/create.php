<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$trainers = $pdo->query("SELECT trainer_id, full_name FROM trainers WHERE status = 'active' ORDER BY full_name")->fetchAll();
$activeTrainerIds = array_map('intval', array_column($trainers, 'trainer_id'));
$errors = [];



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify('modules/classes/create.php');
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



    $errors = validate_class_form($pdo, $activeTrainerIds, $className, $trainerId, $classDate, $startTime, $duration, $capacity);

    
    if (!$errors) {
        $pdo->prepare('INSERT INTO classes (class_name, trainer_id, class_date, start_time, duration_minutes, capacity, status) VALUES (?, ?, ?, ?, ?, ?, "scheduled")')
            ->execute([$className, $trainerId, $classDate, $startTime, (int) $duration, (int) $capacity]);
        redirect_with_flash('modules/classes/index.php', 'success', "Class \"{$className}\" scheduled.");
    }
}

$pageTitle = 'Add Class';
$activeNav = 'classes';
require __DIR__ . '/../../includes/layout_start.php';
?>
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 max-w-xl">
  <?php foreach ($errors as $err): ?>
    <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium px-4 py-3 mb-4"><?= e($err) ?></div>
  <?php endforeach; ?>
  <form method="post" class="grid grid-cols-2 gap-4">
    <?= csrf_field() ?>
    <div class="col-span-2">
      <label class="text-xs font-bold text-slate-500">Class Name</label>
      <input name="class_name" required maxlength="120" value="<?= e(old('class_name')) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div class="col-span-2">
      <label class="text-xs font-bold text-slate-500">Trainer</label>
      <select name="trainer_id" required class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
        <option value="">Select a trainer</option>
        <?php foreach ($trainers as $t): ?>
          <option value="<?= (int) $t['trainer_id'] ?>"<?= selected($t['trainer_id'], old('trainer_id')) ?>><?= e($t['full_name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Date</label>
      <input type="date" name="class_date" required min="<?= e(date('Y-m-d')) ?>" value="<?= e(old('class_date')) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Start Time</label>
      <input type="time" name="start_time" required min="05:00" max="22:45" value="<?= e(old('start_time')) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Duration (minutes, 15-180)</label>
      <input type="number" name="duration_minutes" required min="15" max="180" step="1" value="<?= e(old('duration_minutes', '60')) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Capacity (1-100)</label>
      <input type="number" name="capacity" required min="1" max="100" step="1" value="<?= e(old('capacity', '20')) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div class="col-span-2 flex gap-3 pt-2">
      <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-[10px] px-5 py-2.5 text-sm">Save Class</button>
      <?= btn('Cancel', base_url('modules/classes/index.php'), 'ghost') ?>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
