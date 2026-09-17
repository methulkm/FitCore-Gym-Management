<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM classes WHERE class_id = ?');
$stmt->execute([$id]);
$class = $stmt->fetch();
if (!$class) redirect_with_flash('modules/classes/index.php', 'error', 'Class not found.');

$trainers = $pdo->query("SELECT trainer_id, full_name FROM trainers WHERE status = 'active' ORDER BY full_name")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $className = trim($_POST['class_name'] ?? '');
    $trainerId = (int) ($_POST['trainer_id'] ?? 0);
    $classDate = $_POST['class_date'] ?? '';
    $startTime = $_POST['start_time'] ?? '';
    $duration = (int) ($_POST['duration_minutes'] ?? 60);
    $capacity = (int) ($_POST['capacity'] ?? 0);

    if ($className === '') $errors[] = 'Class name is required.';
    if ($capacity <= 0) $errors[] = 'Capacity must be greater than 0.';

    if (!$errors) {
        $pdo->prepare('UPDATE classes SET class_name=?, trainer_id=?, class_date=?, start_time=?, duration_minutes=?, capacity=? WHERE class_id=?')
            ->execute([$className, $trainerId, $classDate, $startTime, $duration, $capacity, $id]);
        redirect_with_flash('modules/classes/index.php', 'success', 'Class updated.');
    }
}

$pageTitle = 'Edit Class';
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
      <input name="class_name" required value="<?= e($_POST['class_name'] ?? $class['class_name']) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div class="col-span-2">
      <label class="text-xs font-bold text-slate-500">Trainer</label>
      <select name="trainer_id" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
        <?php foreach ($trainers as $t): ?>
          <option value="<?= (int) $t['trainer_id'] ?>" <?= $t['trainer_id'] == $class['trainer_id'] ? 'selected' : '' ?>><?= e($t['full_name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Date</label>
      <input type="date" name="class_date" value="<?= e($class['class_date']) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Start Time</label>
      <input type="time" name="start_time" value="<?= e($class['start_time']) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Duration (minutes)</label>
      <input type="number" name="duration_minutes" value="<?= (int) $class['duration_minutes'] ?>" min="15" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Capacity</label>
      <input type="number" name="capacity" value="<?= (int) $class['capacity'] ?>" min="1" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div class="col-span-2 flex gap-3 pt-2">
      <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-[10px] px-5 py-2.5 text-sm">Save Changes</button>
      <?= btn('Cancel', base_url('modules/classes/index.php'), 'ghost') ?>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
