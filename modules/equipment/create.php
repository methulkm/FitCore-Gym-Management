<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['equipment_name'] ?? '');
    $supplier = trim($_POST['supplier'] ?? '');
    $purchaseDate = $_POST['purchase_date'] ?? null;
    $condition = trim($_POST['condition_status'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $status = $_POST['status'] ?? 'available';

    if ($name === '') $errors[] = 'Equipment name is required.';

    if (!$errors) {
        $code = next_code($pdo, 'equipment', 'equipment_code', 'EQP');
        $pdo->prepare('INSERT INTO equipment (equipment_code, equipment_name, supplier, purchase_date, condition_status, location, status) VALUES (?, ?, ?, ?, ?, ?, ?)')
            ->execute([$code, $name, $supplier, $purchaseDate ?: null, $condition, $location, $status]);
        redirect_with_flash('modules/equipment/index.php', 'success', "Equipment {$code} added.");
    }
}

$pageTitle = 'Add Equipment';
$activeNav = 'equipment';
$ownerTag = 'Janith';
require __DIR__ . '/../../includes/layout_start.php';
?>
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 max-w-xl">
  <?php foreach ($errors as $err): ?>
    <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium px-4 py-3 mb-4"><?= e($err) ?></div>
  <?php endforeach; ?>
  <form method="post" class="grid grid-cols-2 gap-4">
    <div class="col-span-2">
      <label class="text-xs font-bold text-slate-500">Equipment Name</label>
      <input name="equipment_name" required value="<?= e($_POST['equipment_name'] ?? '') ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Supplier</label>
      <input name="supplier" value="<?= e($_POST['supplier'] ?? '') ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Purchase Date</label>
      <input type="date" name="purchase_date" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Condition</label>
      <input name="condition_status" placeholder="Good, Needs Service..." value="<?= e($_POST['condition_status'] ?? '') ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Location</label>
      <input name="location" placeholder="Floor 1, Floor 2..." value="<?= e($_POST['location'] ?? '') ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div class="col-span-2">
      <label class="text-xs font-bold text-slate-500">Status</label>
      <select name="status" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
        <?php foreach (['available' => 'Available', 'in_use' => 'In Use', 'under_maintenance' => 'Under Maintenance', 'out_of_service' => 'Out of Service'] as $val => $label): ?>
          <option value="<?= $val ?>"><?= $label ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-span-2 flex gap-3 pt-2">
      <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-[10px] px-5 py-2.5 text-sm">Save Equipment</button>
      <?= btn('Cancel', base_url('modules/equipment/index.php'), 'ghost') ?>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
