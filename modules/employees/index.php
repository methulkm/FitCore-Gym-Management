<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

// history_count so the UI only offers a hard Delete when it's safe - an employee with
// attendance/leave records is real HR history and must be Deactivated instead (SRS 3.1).
$employees = $pdo->query(
    'SELECT e.*,
        (SELECT COUNT(*) FROM employee_attendance WHERE employee_id = e.employee_id) +
        (SELECT COUNT(*) FROM employee_leave WHERE employee_id = e.employee_id) AS history_count
     FROM employees e ORDER BY e.employee_id DESC'

)->fetchAll();
$total = count($employees);
$active = count(array_filter($employees, fn($e) => $e['status'] === 'active'));
$onLeave = count(array_filter($employees, fn($e) => $e['status'] === 'on_leave'));

$pageTitle = 'Employee Directory';
$pageSubtitle = 'Cleaners, receptionists & maintenance staff - shifts, phone numbers, status (FR-04, FR-07, BR-03, BR-17, UC-04)';
$activeNav = 'employees';
$ownerTag = 'Hasith';
$headerActions = btn('+ Add Employee', base_url('modules/employees/create.php'));
require __DIR__ . '/../../includes/layout_start.php';
?>


<div class="rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-700 text-sm font-medium px-4 py-3">
  Rule BR-03 / BR-17: cleaners, receptionists and maintenance technicians do <b>not</b> receive system login accounts &mdash; only Admin manages their records here.
</div>

<div class="grid grid-cols-3 gap-4">
  <?= kpi_card('Total Employees', (string) $total) ?>
  <?= kpi_card('Active', (string) $active, '', 'text-emerald-600') ?>
  <?= kpi_card('On Leave', (string) $onLeave, '', 'text-amber-600') ?>
</div>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
      <tr>
        <th class="text-left font-bold px-5 py-3">Employee</th>
        <th class="text-left font-bold px-5 py-3">Role</th>
        <th class="text-left font-bold px-5 py-3">Shift</th>
        <th class="text-left font-bold px-5 py-3">Phone</th>
        <th class="text-left font-bold px-5 py-3">Status</th>
        <th class="text-right font-bold px-5 py-3">Actions</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
      <?php foreach ($employees as $emp): ?>
        <tr>
          <td class="px-5 py-3">
            <div class="font-semibold text-slate-900"><?= e($emp['full_name']) ?></div>
            <div class="text-teal-600 text-xs font-bold"><?= e($emp['employee_code']) ?></div>
          </td>
          <td class="px-5 py-3 text-slate-600 capitalize"><?= e($emp['job_role']) ?></td>
          <td class="px-5 py-3 text-slate-600 capitalize"><?= e(str_replace('_', ' ', $emp['shift'])) ?></td>
          <td class="px-5 py-3 text-slate-600"><?= e($emp['phone']) ?></td>
          <td class="px-5 py-3"><?= status_badge(ucfirst(str_replace('_', ' ', $emp['status'])), match ($emp['status']) { 'active' => 'emerald', 'on_leave' => 'amber', default => 'rose' }) ?></td>
          <td class="px-5 py-3 text-right space-x-3 whitespace-nowrap">
            <a href="<?= e(base_url('modules/employees/edit.php?id=' . $emp['employee_id'])) ?>" class="text-slate-500 hover:text-teal-600 text-sm font-bold">Edit</a>
            <?php if ($emp['status'] !== 'inactive'): ?>
              <?= delete_link(base_url('modules/employees/delete.php?id=' . $emp['employee_id']), 'Mark this employee inactive?', 'Deactivate') ?>
            <?php else: ?>
              <a href="<?= e(base_url('modules/employees/delete.php?id=' . $emp['employee_id'] . '&reactivate=1')) ?>" class="text-emerald-600 hover:text-emerald-700 text-sm font-bold">Reactivate</a>
            <?php endif; ?>
            <?php if ((int) $emp['history_count'] === 0): ?>
              <?= delete_link(base_url('modules/employees/hard_delete.php?id=' . $emp['employee_id']), 'Permanently delete this employee? This cannot be undone (only allowed because they have no attendance or leave history yet).', 'Delete') ?>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$employees): ?>
        <tr><td colspan="6" class="px-5 py-8 text-center text-slate-400">No employees yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
