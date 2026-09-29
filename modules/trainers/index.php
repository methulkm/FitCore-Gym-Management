<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

// history_count so the UI only offers a hard Delete when it's safe. Availability slots are just
// disposable schedule config (they cascade away harmlessly); classes/bookings are real assignments
// and history, so a trainer who has ever been assigned one must be Deactivated instead (SRS 3.1).
$statusFilter = $_GET['status'] ?? 'all';
$specFilter = trim($_GET['spec'] ?? '');

$sql = 'SELECT t.*,
        (SELECT COUNT(*) FROM classes WHERE trainer_id = t.trainer_id) +
        (SELECT COUNT(*) FROM bookings WHERE trainer_id = t.trainer_id) AS history_count
     FROM trainers t WHERE 1=1';
$params = [];
if ($statusFilter !== 'all') { $sql .= ' AND t.status = ?'; $params[] = $statusFilter; }
if ($specFilter !== '') { $sql .= ' AND t.specialization = ?'; $params[] = $specFilter; }
$sql .= ' ORDER BY t.trainer_id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$trainers = $stmt->fetchAll();

$specializations = $pdo->query("SELECT DISTINCT specialization FROM trainers WHERE specialization != '' ORDER BY specialization")->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'Trainers & Schedule Management';
$pageSubtitle = 'Credentials, specializations, working-hours availability and class assignments (FR-06, FR-08, FR-09, FR-10, UC-05, UC-06)';
$activeNav = 'trainers';
$ownerTag = 'Senuka';
$headerActions = btn('+ Add Trainer', base_url('modules/trainers/create.php'));
require __DIR__ . '/../../includes/layout_start.php';
?>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex flex-wrap items-center gap-3">
  <div class="flex items-center gap-2">
    <?php foreach (['all' => 'All', 'active' => 'Active', 'deactivated' => 'Deactivated'] as $key => $label): ?>
      <a href="?status=<?= e($key) ?>&spec=<?= e($specFilter) ?>" class="text-xs font-bold px-3 py-1.5 rounded-full <?= $statusFilter === $key ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
  </div>
  <form method="get" class="ml-auto">
    <select name="spec" onchange="this.form.submit()" class="text-xs font-bold border border-slate-200 rounded-full px-3 py-1.5 text-slate-600">
      <option value="">All specializations</option>
      <?php foreach ($specializations as $spec): ?>
        <option value="<?= e($spec) ?>" <?= $specFilter === $spec ? 'selected' : '' ?>><?= e($spec) ?></option>
      <?php endforeach; ?>
    </select>
    <input type="hidden" name="status" value="<?= e($statusFilter) ?>">
  </form>
</div>

<div class="grid md:grid-cols-3 gap-5">
  <?php foreach ($trainers as $t): ?>
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
      <div class="flex items-center gap-3 mb-3">
        <?php if ($t['profile_image']): ?>
          <img src="<?= e(base_url($t['profile_image'])) ?>" class="w-11 h-11 rounded-full object-cover border border-slate-200">
        <?php else: ?>
          <span class="w-11 h-11 rounded-full bg-teal-600 text-white flex items-center justify-center font-bold"><?= e(strtoupper(substr($t['full_name'], 0, 2))) ?></span>
        <?php endif; ?>
        <div>
          <p class="font-extrabold text-slate-900"><?= e($t['full_name']) ?></p>
          <p class="text-teal-600 text-xs font-bold"><?= e($t['trainer_code']) ?></p>
        </div>
      </div>
      <p class="text-sm text-slate-600 mb-1"><b>Specialization:</b> <?= e($t['specialization']) ?></p>
      <p class="text-sm text-slate-600 mb-3"><b>Qualification:</b> <?= e($t['qualification']) ?></p>
      <div class="flex items-center justify-between pt-3 border-t border-slate-100">
        <?= status_badge(ucfirst($t['status']), $t['status'] === 'active' ? 'emerald' : 'rose') ?>
        <div class="relative">
          <button type="button" data-menu-btn class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600" aria-label="Actions">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="12" cy="19" r="1.8"/></svg>
          </button>
          <div data-menu class="hidden absolute right-0 top-9 w-44 bg-white rounded-xl border border-slate-200 shadow-lg z-20 py-1.5 text-sm">
            <a href="<?= e(base_url('modules/trainers/availability.php?trainer_id=' . $t['trainer_id'])) ?>" class="block px-3.5 py-2 text-slate-600 hover:bg-slate-50 font-semibold">Availability</a>
            <a href="<?= e(base_url('modules/trainers/edit.php?id=' . $t['trainer_id'])) ?>" class="block px-3.5 py-2 text-slate-600 hover:bg-slate-50 font-semibold">Edit</a>
            <?php if ($t['user_id']): ?>
              <a href="<?= e(csrf_url(base_url('modules/trainers/reset_password.php?id=' . $t['trainer_id']))) ?>" onclick="return confirm('Reset this trainer\'s password? A new temporary password will be generated.')" class="block px-3.5 py-2 text-indigo-600 hover:bg-indigo-50 font-semibold">Reset Password</a>
            <?php endif; ?>
            <div class="my-1.5 border-t border-slate-100"></div>
            <?php if ($t['status'] === 'active'): ?>
              <a href="<?= e(csrf_url(base_url('modules/trainers/delete.php?id=' . $t['trainer_id']))) ?>" onclick="return confirm('Deactivate this trainer? (history preserved)')" class="block px-3.5 py-2 text-amber-600 hover:bg-amber-50 font-semibold">Deactivate</a>
            <?php else: ?>
              <a href="<?= e(csrf_url(base_url('modules/trainers/delete.php?id=' . $t['trainer_id'] . '&reactivate=1'))) ?>" class="block px-3.5 py-2 text-emerald-600 hover:bg-emerald-50 font-semibold">Reactivate</a>
            <?php endif; ?>
            <?php if ((int) $t['history_count'] === 0): ?>
              <a href="<?= e(csrf_url(base_url('modules/trainers/hard_delete.php?id=' . $t['trainer_id']))) ?>" onclick="return confirm('Permanently delete this trainer? This cannot be undone (only allowed because they have no classes or bookings yet).')" class="block px-3.5 py-2 text-rose-600 hover:bg-rose-50 font-semibold">Delete</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$trainers): ?><p class="text-slate-400">No trainers yet.</p><?php endif; ?>
</div>

<script>
if (!window.__actionMenuInit) {
  window.__actionMenuInit = true;
  (function () {
    var openMenu = null;
    function closeMenu() { if (openMenu) { openMenu.classList.add('hidden'); openMenu = null; } }
    document.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-menu-btn]');
      if (btn) {
        var menu = btn.nextElementSibling;
        var wasOpen = menu === openMenu;
        closeMenu();
        if (!wasOpen) { menu.classList.remove('hidden'); openMenu = menu; }
        return;
      }
      if (!e.target.closest('[data-menu]')) closeMenu();
    });
    window.addEventListener('app-nav-start', closeMenu);
  })();
}
</script>

<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
