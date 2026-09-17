<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$equipment = $pdo->query('SELECT * FROM equipment ORDER BY equipment_id DESC')->fetchAll();
$available = count(array_filter($equipment, fn($e) => $e['status'] === 'available'));
$maintenance = count(array_filter($equipment, fn($e) => $e['status'] === 'under_maintenance'));

$pageTitle = 'Equipment & Maintenance';
$pageSubtitle = 'Machine inventory, condition, location and servicing schedule (FR-15, FR-19, BR-18, BR-19, UC-15)';
$activeNav = 'equipment';
$ownerTag = 'Janith';
$headerActions = btn('+ Add Equipment', base_url('modules/equipment/create.php'));
require __DIR__ . '/../../includes/layout_start.php';
?>



<div class="grid grid-cols-3 gap-4">
  <?= kpi_card('Total Equipment', (string) count($equipment)) ?>
  <?= kpi_card('Available', (string) $available, '', 'text-emerald-600') ?>
  <?= kpi_card('Under Maintenance', (string) $maintenance, '', 'text-amber-600') ?>
</div>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
      <tr>
        <th class="text-left font-bold px-5 py-3">Equipment</th>
        <th class="text-left font-bold px-5 py-3">Location</th>
        <th class="text-left font-bold px-5 py-3">Condition</th>
        <th class="text-left font-bold px-5 py-3">Status</th>
        <th class="text-right font-bold px-5 py-3">Actions</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
      <?php foreach ($equipment as $eq): ?>
        <tr>
          <td class="px-5 py-3">
            <div class="font-semibold text-slate-900"><?= e($eq['equipment_name']) ?></div>
            <div class="text-teal-600 text-xs font-bold"><?= e($eq['equipment_code']) ?></div>
          </td>
          <td class="px-5 py-3 text-slate-600"><?= e($eq['location']) ?></td>
          <td class="px-5 py-3 text-slate-600"><?= e($eq['condition_status']) ?></td>
          <td class="px-5 py-3"><?= status_badge(ucfirst(str_replace('_', ' ', $eq['status'])), match ($eq['status']) { 'available' => 'emerald', 'in_use' => 'indigo', 'under_maintenance' => 'amber', default => 'rose' }) ?></td>
          <td class="px-5 py-3 text-right space-x-3 whitespace-nowrap">
            <a href="<?= e(base_url('modules/equipment/maintenance.php?equipment_id=' . $eq['equipment_id'])) ?>" class="text-slate-500 hover:text-teal-600 text-sm font-bold">Maintenance</a>
            <a href="<?= e(base_url('modules/equipment/edit.php?id=' . $eq['equipment_id'])) ?>" class="text-slate-500 hover:text-teal-600 text-sm font-bold">Edit</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$equipment): ?><tr><td colspan="5" class="px-5 py-8 text-center text-slate-400">No equipment recorded yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
