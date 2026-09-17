<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$employees = $pdo->query("SELECT employee_id, employee_code, full_name FROM employees WHERE status != 'inactive' ORDER BY full_name")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employeeId = (int) ($_POST['employee_id'] ?? 0);
    $leaveType = $_POST['leave_type'] ?? '';
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    $reason = trim($_POST['reason'] ?? '');

    if (!$employeeId) $errors[] = 'Please choose an employee.';
    if (!in_array($leaveType, ['medical', 'casual'], true)) $errors[] = 'Please choose a leave type.';
    if (!$startDate || !$endDate) $errors[] = 'Start and end dates are required.';
    elseif ($endDate < $startDate) $errors[] = 'End date cannot be before the start date.';

    if (!$errors) {
        $pdo->prepare('INSERT INTO employee_leave (employee_id, leave_type, start_date, end_date, reason, status) VALUES (?, ?, ?, ?, ?, "pending")')
            ->execute([$employeeId, $leaveType, $startDate, $endDate, $reason]);
        redirect_with_flash('modules/attendance/index.php', 'success', 'Leave request submitted.');
    }
}

$pageTitle = 'Request Leave';
$activeNav = 'attendance';
$ownerTag = 'Hasith';
require __DIR__ . '/../../includes/layout_start.php';
?>
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 max-w-xl">
  <?php foreach ($errors as $err): ?>
    <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium px-4 py-3 mb-4"><?= e($err) ?></div>
  <?php endforeach; ?>
  <form method="post" class="space-y-4">
    <div>
      <label class="text-xs font-bold text-slate-500">Employee</label>
      <select name="employee_id" required class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
        <option value="">Select employee</option>
        <?php foreach ($employees as $emp): ?>
          <option value="<?= (int) $emp['employee_id'] ?>"><?= e($emp['employee_code'] . ' - ' . $emp['full_name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Leave Type</label>
      <select name="leave_type" required class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
        <option value="medical">Medical</option>
        <option value="casual">Casual</option>
      </select>
    </div>
    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="text-xs font-bold text-slate-500">Start Date</label>
        <input type="date" name="start_date" required class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
      </div>
      <div>
        <label class="text-xs font-bold text-slate-500">End Date</label>
        <input type="date" name="end_date" required class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
      </div>
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Reason (optional)</label>
      <input name="reason" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div class="flex gap-3 pt-2">
      <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-[10px] px-5 py-2.5 text-sm">Submit Request</button>
      <?= btn('Cancel', base_url('modules/attendance/index.php'), 'ghost') ?>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
