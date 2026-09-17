<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$equipmentId = (int) ($_GET['equipment_id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM equipment WHERE equipment_id = ?');
$stmt->execute([$equipmentId]);
$eq = $stmt->fetch();
if (!$eq) redirect_with_flash('modules/equipment/index.php', 'error', 'Equipment not found.');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maintDate = $_POST['maintenance_date'] ?? '';
    $nextDue = $_POST['next_due_date'] ?? null;
    $notes = trim($_POST['notes'] ?? '');

    if (!$maintDate) $errors[] = 'Maintenance date is required.';

    if (!$errors) {
        $pdo->prepare('INSERT INTO equipment_maintenance (equipment_id, maintenance_date, next_due_date, notes) VALUES (?, ?, ?, ?)')
            ->execute([$equipmentId, $maintDate, $nextDue ?: null, $notes]);
        $pdo->prepare("UPDATE equipment SET status = 'available', condition_status = 'Good' WHERE equipment_id = ?")->execute([$equipmentId]);
        redirect_with_flash('modules/equipment/maintenance.php?equipment_id=' . $equipmentId, 'success', 'Maintenance record logged; equipment marked available.');
    }
}

$history = $pdo->prepare('SELECT * FROM equipment_maintenance WHERE equipment_id = ? ORDER BY maintenance_date DESC');
$history->execute([$equipmentId]);
$history = $history->fetchAll();

$pageTitle = 'Maintenance - ' . $eq['equipment_name'];
$activeNav = 'equipment';
$ownerTag = 'Janith';
require __DIR__ . '/../../includes/layout_start.php';
?>
<div class="grid lg:grid-cols-2 gap-6">
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
    <h3 class="font-extrabold mb-4">Log Maintenance</h3>
    <?php foreach ($errors as $err): ?>
      <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium px-4 py-3 mb-4"><?= e($err) ?></div>
    <?php endforeach; ?>
    <form method="post" class="space-y-4">
      <div>
        <label class="text-xs font-bold text-slate-500">Maintenance Date</label>
        <input type="date" name="maintenance_date" required value="<?= e(date('Y-m-d')) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
      </div>
      <div>
        <label class="text-xs font-bold text-slate-500">Next Due Date</label>
        <input type="date" name="next_due_date" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
      </div>
      <div>
        <label class="text-xs font-bold text-slate-500">Notes</label>
        <textarea name="notes" rows="3" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm"></textarea>
      </div>
      <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-[10px] px-5 py-2.5 text-sm">Save Record</button>
    </form>
  </div>
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
    <h3 class="font-extrabold mb-4">Maintenance History</h3>
    <div class="space-y-3">
      <?php foreach ($history as $h): ?>
        <div class="border-b border-slate-100 pb-2">
          <p class="text-sm font-semibold"><?= e(date('d M Y', strtotime($h['maintenance_date']))) ?><?= $h['next_due_date'] ? ' - next due ' . e(date('d M Y', strtotime($h['next_due_date']))) : '' ?></p>
          <?php if ($h['notes']): ?><p class="text-xs text-slate-500"><?= e($h['notes']) ?></p><?php endif; ?>
        </div>
      <?php endforeach; ?>
      <?php if (!$history): ?><p class="text-slate-400 text-sm">No maintenance history yet.</p><?php endif; ?>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
