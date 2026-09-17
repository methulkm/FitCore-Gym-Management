<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM employees WHERE employee_id = ?');
$stmt->execute([$id]);
$emp = $stmt->fetch();
if (!$emp) redirect_with_flash('modules/employees/index.php', 'error', 'Employee not found.');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $jobRole = $_POST['job_role'] ?? '';
    $shift = $_POST['shift'] ?? 'general';

    if ($fullName === '') $errors[] = 'Full name is required.';
    if ($phone === '') $errors[] = 'Phone number is required.';

    if (!$errors) {
        $pdo->prepare('UPDATE employees SET full_name=?, phone=?, job_role=?, shift=? WHERE employee_id=?')
            ->execute([$fullName, $phone, $jobRole, $shift, $id]);
        redirect_with_flash('modules/employees/index.php', 'success', "Employee {$emp['employee_code']} updated.");
    }
}

$pageTitle = 'Edit Employee';
$activeNav = 'employees';
$ownerTag = 'Hasith';
require __DIR__ . '/../../includes/layout_start.php';
?>
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 max-w-xl">
  <p class="text-teal-600 text-xs font-bold mb-4"><?= e($emp['employee_code']) ?></p>
  <?php foreach ($errors as $err): ?>
    <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium px-4 py-3 mb-4"><?= e($err) ?></div>
  <?php endforeach; ?>
  <form method="post" class="space-y-4">
    <div>
      <label class="text-xs font-bold text-slate-500">Full Name</label>
      <input name="full_name" required value="<?= e($_POST['full_name'] ?? $emp['full_name']) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="text-xs font-bold text-slate-500">Phone</label>
        <input name="phone" required value="<?= e($_POST['phone'] ?? $emp['phone']) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
      </div>
      <div>
        <label class="text-xs font-bold text-slate-500">Job Role</label>
        <select name="job_role" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
          <?php foreach (['cleaner', 'receptionist', 'maintenance'] as $r): ?>
            <option value="<?= $r ?>" <?= $emp['job_role'] === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Shift</label>
      <select name="shift" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
        <?php foreach (['morning' => 'Morning (06:00-14:00)', 'evening' => 'Evening (14:00-22:00)', 'general' => 'General'] as $val => $label): ?>
          <option value="<?= $val ?>" <?= $emp['shift'] === $val ? 'selected' : '' ?>><?= $label ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="flex gap-3 pt-2">
      <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-[10px] px-5 py-2.5 text-sm">Save Changes</button>
      <?= btn('Cancel', base_url('modules/employees/index.php'), 'ghost') ?>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
