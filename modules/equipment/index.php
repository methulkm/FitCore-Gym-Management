<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$statusFilter = $_GET['status'] ?? 'all';
$locationFilter = trim($_GET['location'] ?? '');

// history_count: Delete is only offered when there is no maintenance history to lose (it cascades).
$sql = 'SELECT q.*, (SELECT COUNT(*) FROM equipment_maintenance WHERE equipment_id = q.equipment_id) AS history_count
     FROM equipment q WHERE 1=1';
$params = [];
if ($statusFilter !== 'all') { $sql .= ' AND q.status = ?'; $params[] = $statusFilter; }
if ($locationFilter !== '') { $sql .= ' AND q.location = ?'; $params[] = $locationFilter; }
$sql .= ' ORDER BY q.equipment_id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$equipment = $stmt->fetchAll();

$allEquipment = $pdo->query('SELECT status FROM equipment')->fetchAll();
$available = count(array_filter($allEquipment, fn($e) => $e['status'] === 'available'));
$maintenance = count(array_filter($allEquipment, fn($e) => $e['status'] === 'under_maintenance'));
$locations = $pdo->query("SELECT DISTINCT location FROM equipment WHERE location != '' AND location IS NOT NULL ORDER BY location")->fetchAll(PDO::FETCH_COLUMN);

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
  <div class="flex flex-wrap items-center gap-3 px-5 py-4 border-b border-slate-100">
    <div class="flex items-center gap-2">
      <?php foreach (['all' => 'All', 'available' => 'Available', 'in_use' => 'In Use', 'under_maintenance' => 'Under Maintenance', 'out_of_service' => 'Out of Service'] as $key => $label): ?>
        <a href="?status=<?= e($key) ?>&location=<?= e(urlencode($locationFilter)) ?>" class="text-xs font-bold px-3 py-1.5 rounded-full <?= $statusFilter === $key ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' ?>"><?= e($label) ?></a>
      <?php endforeach; ?>
    </div>
    <form method="get" class="ml-auto">
      <input type="hidden" name="status" value="<?= e($statusFilter) ?>">
      <select name="location" onchange="this.form.submit()" class="text-xs font-bold border border-slate-200 rounded-full px-3 py-1.5 text-slate-600">
        <option value="">All Locations</option>
        <?php foreach ($locations as $loc): ?>
          <option value="<?= e($loc) ?>" <?= $locationFilter === $loc ? 'selected' : '' ?>><?= e($loc) ?></option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>
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
            <?php if ((int) $eq['history_count'] === 0): ?>
              <?= delete_link(base_url('modules/equipment/hard_delete.php?id=' . $eq['equipment_id']), 'Permanently delete this equipment? This cannot be undone (allowed only because it has no maintenance history).', 'Delete') ?>
            <?php else: ?>
              <?= delete_disabled('Has ' . (int) $eq['history_count'] . ' maintenance record(s) - set status to Out of Service instead') ?>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$equipment): ?><tr><td colspan="5" class="px-5 py-8 text-center text-slate-400">No equipment recorded yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
