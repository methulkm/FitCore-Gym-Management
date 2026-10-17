<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM classes WHERE class_id = ?');
$stmt->execute([$id]);
$class = $stmt->fetch();
if (!$class) redirect_with_flash('modules/classes/index.php', 'error', 'Class not found.');
if ($class['status'] !== 'scheduled') redirect_with_flash('modules/classes/index.php', 'error', 'Only scheduled classes can be edited (this one is ' . $class['status'] . ').');

$trainers = $pdo->query("SELECT trainer_id, full_name FROM trainers WHERE status = 'active' ORDER BY full_name")->fetchAll();
$activeTrainerIds = array_map('intval', array_column($trainers, 'trainer_id'));

$bookedStmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE class_id = ? AND status = 'booked'");
$bookedStmt->execute([$id]);
$bookedCount = (int) $bookedStmt->fetchColumn();

$errors = [];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify('modules/classes/index.php');
    $className = trim($_POST['class_name'] ?? '');
    $trainerId = (int) ($_POST['trainer_id'] ?? 0);
    $classDate = trim($_POST['class_date'] ?? '');
    $startTime = trim($_POST['start_time'] ?? '');
    $duration = trim($_POST['duration_minutes'] ?? '');
    $capacity = trim($_POST['capacity'] ?? '');

    $timeChanged = $classDate !== $class['class_date'] || substr($startTime, 0, 5) !== substr($class['start_time'], 0, 5);
    $errors = validate_class_form($pdo, $activeTrainerIds, $className, $trainerId, $classDate, $startTime, $duration, $capacity, $id, $timeChanged);

    if (!$errors && (int) $capacity < $bookedCount) {
        $errors[] = "Capacity cannot be lower than the {$bookedCount} member(s) already booked into this class.";
    }

    if (!$errors) {
        $pdo->prepare('UPDATE classes SET class_name=?, trainer_id=?, class_date=?, start_time=?, duration_minutes=?, capacity=? WHERE class_id=?')
            ->execute([$className, $trainerId, $classDate, $startTime, (int) $duration, (int) $capacity, $id]);
        redirect_with_flash('modules/classes/index.php', 'success', 'Class updated.');
    }
}

$pageTitle = 'Edit Class';
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
      <input name="class_name" required maxlength="120" value="<?= e(old('class_name', $class['class_name'])) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div class="col-span-2">
      <label class="text-xs font-bold text-slate-500">Trainer</label>
      <select name="trainer_id" required class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
        <option value="">Select a trainer</option>
        <?php foreach ($trainers as $t): ?>
          <option value="<?= (int) $t['trainer_id'] ?>"<?= selected($t['trainer_id'], old('trainer_id', $class['trainer_id'])) ?>><?= e($t['full_name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Date</label>
      <input type="date" name="class_date" required value="<?= e(old('class_date', $class['class_date'])) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Start Time</label>
      <input type="time" name="start_time" required min="05:00" max="22:45" value="<?= e(substr(old('start_time', $class['start_time']), 0, 5)) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Duration (minutes, 15-180)</label>
      <input type="number" name="duration_minutes" required min="15" max="180" step="1" value="<?= e(old('duration_minutes', $class['duration_minutes'])) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Capacity (<?= $bookedCount ?> already booked)</label>
      <input type="number" name="capacity" required min="<?= max(1, $bookedCount) ?>" max="100" step="1" value="<?= e(old('capacity', $class['capacity'])) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div class="col-span-2 flex gap-3 pt-2">
      <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-[10px] px-5 py-2.5 text-sm">Save Changes</button>
      <?= btn('Cancel', base_url('modules/classes/index.php'), 'ghost') ?>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
