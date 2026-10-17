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
    csrf_verify('modules/equipment/maintenance.php?equipment_id=' . $equipmentId);
    $maintDate = trim($_POST['maintenance_date'] ?? '');
    $nextDue = trim($_POST['next_due_date'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    v_push($errors, v_required($maintDate, 'Maintenance date'));
    $mErr = v_date($maintDate, 'Maintenance date');
    if ($mErr) $errors[] = $mErr;
    elseif ($maintDate !== '' && $maintDate > date('Y-m-d')) $errors[] = 'Maintenance date cannot be in the future - log the work after it is done.';
    elseif ($maintDate !== '' && $eq['purchase_date'] && $maintDate < $eq['purchase_date']) $errors[] = 'Maintenance date cannot be before the purchase date.';
    $nErr = v_date($nextDue, 'Next due date');
    if ($nErr) $errors[] = $nErr;
    elseif ($nextDue !== '' && !$mErr && $nextDue <= $maintDate) $errors[] = 'Next due date must be after the maintenance date.';
    elseif ($nextDue !== '' && $nextDue > date('Y-m-d', strtotime('+2 years'))) $errors[] = 'Next due date cannot be more than 2 years ahead.';
    v_push($errors, v_free_text($notes, 'Notes', 255));

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
require __DIR__ . '/../../includes/layout_start.php';
?>
<div class="grid lg:grid-cols-2 gap-6">
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
    <h3 class="font-extrabold mb-4">Log Maintenance</h3>
    <?php foreach ($errors as $err): ?>
      <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium px-4 py-3 mb-4"><?= e($err) ?></div>
    <?php endforeach; ?>
    <form method="post" class="space-y-4">
      <?= csrf_field() ?>
      <div>
        <label class="text-xs font-bold text-slate-500">Maintenance Date</label>
        <input type="date" name="maintenance_date" required max="<?= e(date('Y-m-d')) ?>" value="<?= e(old('maintenance_date', date('Y-m-d'))) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
      </div>
      <div>
        <label class="text-xs font-bold text-slate-500">Next Due Date</label>
        <input type="date" name="next_due_date" value="<?= e(old('next_due_date')) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
      </div>
      <div>
        <label class="text-xs font-bold text-slate-500">Notes</label>
        <textarea name="notes" rows="3" maxlength="255" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm"><?= e(old('notes')) ?></textarea>
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
