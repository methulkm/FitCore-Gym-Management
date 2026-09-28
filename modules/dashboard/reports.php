<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$reports = [
    ['Members',       'modules/members/export.php',       'members',       'Sajatha'],
    ['Employees',     'modules/employees/export.php',     'employees',     'Hasith'],
    ['Trainers',      'modules/trainers/export.php',       'trainers',      'Senuka'],
    ['Classes',       'modules/classes/export.php',       'classes',       'Senuka'],
    ['Membership Plans & Subscriptions', 'modules/subscriptions/export.php', 'plans-renewals', 'Methul'],
    ['Payments',      'modules/payments/export.php',       'payments',      'Asiri'],
    ['Equipment',     'modules/equipment/export.php',      'equipment',     'Janith'],
];

$counts = [
    'members'       => $pdo->query('SELECT COUNT(*) FROM members')->fetchColumn(),
    'employees'     => $pdo->query('SELECT COUNT(*) FROM employees')->fetchColumn(),
    'trainers'      => $pdo->query('SELECT COUNT(*) FROM trainers')->fetchColumn(),
    'classes'       => $pdo->query('SELECT COUNT(*) FROM classes')->fetchColumn(),
    'plans-renewals'=> $pdo->query('SELECT COUNT(*) FROM subscriptions')->fetchColumn(),
    'payments'      => $pdo->query('SELECT COUNT(*) FROM payments')->fetchColumn(),
    'equipment'     => $pdo->query('SELECT COUNT(*) FROM equipment')->fetchColumn(),
];

$pageTitle = 'Reports & Export';
$pageSubtitle = 'Simple operational exports per module, CSV format (BR-20: no forecasting/analytics)';
$activeNav = 'reports';
require __DIR__ . '/../../includes/layout_start.php';
?>

<div class="grid md:grid-cols-2 xl:grid-cols-3 gap-5">
  <?php foreach ($reports as [$label, $path, $key, $owner]): ?>
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
      <div class="flex items-start justify-between mb-4">
        <span class="w-11 h-11 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center"><?= sidebar_icon('doc') ?></span>
        <span class="text-[10px] font-bold px-2 py-1 rounded-full bg-indigo-50 text-indigo-500"><?= e($owner) ?></span>
      </div>
      <p class="font-extrabold text-slate-900 mb-1"><?= e($label) ?></p>
      <p class="text-sm text-slate-500 mb-5"><?= (int) $counts[$key] ?> record(s)</p>
      <a href="<?= e(base_url($path)) ?>" data-no-ajax class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold px-4 py-2.5 rounded-[10px]">
        Download CSV
      </a>
    </div>
  <?php endforeach; ?>
</div>

<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
