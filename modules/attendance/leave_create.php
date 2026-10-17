<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$employees = $pdo->query("SELECT employee_id, employee_code, full_name FROM employees WHERE status != 'inactive' ORDER BY full_name")->fetchAll();
$errors = [];
// leave create
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify('modules/attendance/leave_create.php');
    $employeeId = (int) ($_POST['employee_id'] ?? 0);
    $leaveType = $_POST['leave_type'] ?? '';
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    $reason = trim($_POST['reason'] ?? '');

    $empIds = array_map('intval', array_column($employees, 'employee_id'));
    if (!in_array($employeeId, $empIds, true)) $errors[] = 'Please choose an active employee.';
    v_push($errors, v_in($leaveType, ['medical', 'casual'], 'leave type'));
    v_push($errors, v_required($startDate, 'Start date'));
    v_push($errors, v_required($endDate, 'End date'));
    v_push($errors, v_date($startDate, 'Start date'));
    v_push($errors, v_date($endDate, 'End date'));
    v_push($errors, v_free_text($reason, 'Reason', 255));

    if (!$errors) {
        $days = (int) ((strtotime($endDate) - strtotime($startDate)) / 86400) + 1;
        if ($endDate < $startDate) $errors[] = 'End date cannot be before the start date.';
        elseif ($days > 30) $errors[] = 'A single leave request cannot be longer than 30 days.';
        if ($startDate < date('Y-m-d', strtotime('-30 days'))) $errors[] = 'Start date cannot be more than 30 days in the past.';
        if ($startDate > date('Y-m-d', strtotime('+1 year'))) $errors[] = 'Start date cannot be more than a year ahead.';
    }

    if (!$errors) {
        $overlap = $pdo->prepare("SELECT COUNT(*) FROM employee_leave WHERE employee_id = ? AND status IN ('pending','approved')
                                  AND start_date <= ? AND end_date >= ?");
        $overlap->execute([$employeeId, $endDate, $startDate]);
        if ($overlap->fetchColumn() > 0) $errors[] = 'This employee already has a pending or approved leave request that overlaps these dates.';
    }

    if (!$errors) {
        $pdo->prepare('INSERT INTO employee_leave (employee_id, leave_type, start_date, end_date, reason, status) VALUES (?, ?, ?, ?, ?, "pending")')
            ->execute([$employeeId, $leaveType, $startDate, $endDate, $reason]);

        $empName = $pdo->prepare('SELECT full_name FROM employees WHERE employee_id = ?');
        $empName->execute([$employeeId]);
        notify_admin($pdo, 'leave', "{$empName->fetchColumn()} requested {$leaveType} leave ({$startDate} to {$endDate}).", 'modules/attendance/index.php');

        redirect_with_flash('modules/attendance/index.php', 'success', 'Leave request submitted.');
    }
}

$pageTitle = 'Request Leave';
$activeNav = 'attendance';
require __DIR__ . '/../../includes/layout_start.php';
?>
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 max-w-xl">
  <?php foreach ($errors as $err): ?>
    <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium px-4 py-3 mb-4"><?= e($err) ?></div>
  <?php endforeach; ?>
  <form method="post" class="space-y-4">
    <?= csrf_field() ?>
    <div>
      <label class="text-xs font-bold text-slate-500">Employee</label>
      <select name="employee_id" required class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
        <option value="">Select employee</option>
        <?php foreach ($employees as $emp): ?>
          <option value="<?= (int) $emp['employee_id'] ?>"<?= selected($emp['employee_id'], old('employee_id')) ?>><?= e($emp['employee_code'] . ' - ' . $emp['full_name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Leave Type</label>
      <select name="leave_type" required class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
        <option value="medical"<?= selected('medical', old('leave_type')) ?>>Medical</option>
        <option value="casual"<?= selected('casual', old('leave_type')) ?>>Casual</option>
      </select>
    </div>
    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="text-xs font-bold text-slate-500">Start Date</label>
        <input type="date" name="start_date" required value="<?= e(old('start_date')) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
      </div>
      <div>
        <label class="text-xs font-bold text-slate-500">End Date</label>
        <input type="date" name="end_date" required value="<?= e(old('end_date')) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
      </div>
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Reason (optional)</label>
      <input name="reason" maxlength="255" value="<?= e(old('reason')) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div class="flex gap-3 pt-2">
      <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-[10px] px-5 py-2.5 text-sm">Submit Request</button>
      <?= btn('Cancel', base_url('modules/attendance/index.php'), 'ghost') ?>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
