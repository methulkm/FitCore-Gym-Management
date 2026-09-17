<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM equipment WHERE equipment_id = ?');
$stmt->execute([$id]);
$eq = $stmt->fetch();
if (!$eq) redirect_with_flash('modules/equipment/index.php', 'error', 'Equipment not found.');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['equipment_name'] ?? '');
    $supplier = trim($_POST['supplier'] ?? '');
    $condition = trim($_POST['condition_status'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $status = $_POST['status'] ?? 'available'; // BR-18 enum

    if ($name === '') $errors[] = 'Equipment name is required.';

    if (!$errors) {
        $pdo->prepare('UPDATE equipment SET equipment_name=?, supplier=?, condition_status=?, location=?, status=? WHERE equipment_id=?')
            ->execute([$name, $supplier, $condition, $location, $status, $id]);
        redirect_with_flash('modules/equipment/index.php', 'success', "Equipment {$eq['equipment_code']} updated.");
    }
}

$pageTitle = 'Edit Equipment';
$activeNav = 'equipment';
$ownerTag = 'Janith';
require __DIR__ . '/../../includes/layout_start.php';
?>
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 max-w-xl">
  <p class="text-teal-600 text-xs font-bold mb-4"><?= e($eq['equipment_code']) ?></p>
  <?php foreach ($errors as $err): ?>
    <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium px-4 py-3 mb-4"><?= e($err) ?></div>
  <?php endforeach; ?>
  <form method="post" class="grid grid-cols-2 gap-4">
    <div class="col-span-2">
      <label class="text-xs font-bold text-slate-500">Equipment Name</label>
      <input name="equipment_name" required value="<?= e($_POST['equipment_name'] ?? $eq['equipment_name']) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Supplier</label>
      <input name="supplier" value="<?= e($_POST['supplier'] ?? $eq['supplier']) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Condition</label>
      <input name="condition_status" value="<?= e($_POST['condition_status'] ?? $eq['condition_status']) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Location</label>
      <input name="location" value="<?= e($_POST['location'] ?? $eq['location']) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Status</label>
      <select name="status" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
        <?php foreach (['available' => 'Available', 'in_use' => 'In Use', 'under_maintenance' => 'Under Maintenance', 'out_of_service' => 'Out of Service'] as $val => $label): ?>
          <option value="<?= $val ?>" <?= $eq['status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-span-2 flex gap-3 pt-2">
      <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-[10px] px-5 py-2.5 text-sm">Save Changes</button>
      <?= btn('Cancel', base_url('modules/equipment/index.php'), 'ghost') ?>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
