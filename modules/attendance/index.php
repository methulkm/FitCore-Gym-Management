<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$employees = $pdo->query("SELECT employee_id, employee_code, full_name FROM employees WHERE status != 'inactive' ORDER BY full_name")->fetchAll();

$today = $pdo->query("SELECT a.*, e.full_name, e.employee_code FROM employee_attendance a
                       JOIN employees e ON e.employee_id = a.employee_id
                       WHERE a.attendance_date = CURDATE() ORDER BY a.check_in_time DESC")->fetchAll();

$leaves = $pdo->query("SELECT l.*, e.full_name, e.employee_code FROM employee_leave l
                        JOIN employees e ON e.employee_id = l.employee_id
                        ORDER BY l.leave_id DESC LIMIT 15")->fetchAll();

$pendingLeave = count(array_filter($leaves, fn($l) => $l['status'] === 'pending'));

$pageTitle = 'Staff Attendance & Leave';
$pageSubtitle = 'Daily check-ins & leave requests for cleaners, receptionists and maintenance staff (FR-07, BR-15, BR-17)';
$activeNav = 'attendance';
$ownerTag = 'Hasith';
require __DIR__ . '/../../includes/layout_start.php';
?>

<div class="grid grid-cols-3 gap-4">
  <?= kpi_card('Checked In Today', (string) count($today)) ?>
  <?= kpi_card('Pending Leave Requests', (string) $pendingLeave, '', 'text-amber-600') ?>
  <?= kpi_card('Total Employees', (string) count($employees)) ?>
</div>

<div class="grid lg:grid-cols-2 gap-6">
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
    <h3 class="font-extrabold mb-4">Record Today's Check-in</h3>
    <form method="post" action="<?= e(base_url('modules/attendance/check_in.php')) ?>" class="flex gap-2 mb-5">
      <select name="employee_id" required class="flex-1 border border-slate-200 rounded-[10px] px-3 py-2.5 text-sm">
        <option value="">Select employee</option>
        <?php foreach ($employees as $emp): ?>
          <option value="<?= (int) $emp['employee_id'] ?>"><?= e($emp['employee_code'] . ' - ' . $emp['full_name']) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold px-4 py-2.5 rounded-[10px] whitespace-nowrap">Check In</button>
    </form>
    <h4 class="text-xs font-bold text-slate-400 uppercase mb-2">Today's Check-ins</h4>
    <div class="space-y-2 max-h-72 overflow-y-auto">
      <?php foreach ($today as $t): ?>
        <div class="flex items-center justify-between text-sm border-b border-slate-100 pb-2">
          <span class="font-medium text-slate-700"><?= e($t['full_name']) ?> <span class="text-teal-600 text-xs font-bold"><?= e($t['employee_code']) ?></span></span>
          <span class="text-slate-500"><?= e(date('h:i A', strtotime($t['check_in_time']))) ?></span>
        </div>
      <?php endforeach; ?>
      <?php if (!$today): ?><p class="text-slate-400 text-sm">No check-ins recorded yet today.</p><?php endif; ?>
    </div>
  </div>

  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-extrabold">Leave Requests</h3>
      <?= btn('+ Request Leave', base_url('modules/attendance/leave_create.php'), 'ghost') ?>
    </div>
    <div class="space-y-3 max-h-96 overflow-y-auto">
      <?php foreach ($leaves as $l):
        $tone = match ($l['status']) { 'approved' => 'emerald', 'rejected' => 'rose', default => 'amber' };
      ?>
        <div class="border border-slate-100 rounded-xl p-3">
          <div class="flex items-center justify-between">
            <span class="font-semibold text-sm text-slate-900"><?= e($l['full_name']) ?></span>
            <?= status_badge(ucfirst($l['status']), $tone) ?>
          </div>
          <p class="text-xs text-slate-500 mt-1 capitalize"><?= e($l['leave_type']) ?> leave &middot; <?= e(date('d M', strtotime($l['start_date']))) ?> - <?= e(date('d M Y', strtotime($l['end_date']))) ?></p>
          <?php if ($l['status'] === 'pending'): ?>
            <div class="flex gap-3 mt-2">
              <a href="<?= e(base_url('modules/attendance/leave_action.php?id=' . $l['leave_id'] . '&action=approve')) ?>" class="text-emerald-600 text-xs font-bold">Approve</a>
              <a href="<?= e(base_url('modules/attendance/leave_action.php?id=' . $l['leave_id'] . '&action=reject')) ?>" class="text-rose-500 text-xs font-bold">Reject</a>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
      <?php if (!$leaves): ?><p class="text-slate-400 text-sm">No leave requests yet.</p><?php endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
