<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role(['admin']);

$q = trim($_GET['q'] ?? '');
$results = []; // [group label => [ [title, subtitle, link], ... ] ]

if ($q !== '' && strlen($q) >= 2) {
    $like = '%' . $q . '%';

    $stmt = $pdo->prepare("SELECT member_id, member_code, full_name, phone, email FROM members
        WHERE full_name LIKE ? OR member_code LIKE ? OR phone LIKE ? OR email LIKE ? LIMIT 8");
    $stmt->execute([$like, $like, $like, $like]);
    foreach ($stmt->fetchAll() as $r) {
        $results['Members'][] = [$r['full_name'], $r['member_code'] . ' - ' . $r['phone'], base_url('modules/members/edit.php?id=' . $r['member_id'])];
    }

    $stmt = $pdo->prepare("SELECT employee_id, employee_code, full_name, job_role FROM employees
        WHERE full_name LIKE ? OR employee_code LIKE ? LIMIT 8");
    $stmt->execute([$like, $like]);
    foreach ($stmt->fetchAll() as $r) {
        $results['Employees'][] = [$r['full_name'], $r['employee_code'] . ' - ' . ucfirst($r['job_role']), base_url('modules/employees/edit.php?id=' . $r['employee_id'])];
    }

    $stmt = $pdo->prepare("SELECT trainer_id, trainer_code, full_name, specialization FROM trainers
        WHERE full_name LIKE ? OR trainer_code LIKE ? OR specialization LIKE ? LIMIT 8");
    $stmt->execute([$like, $like, $like]);
    foreach ($stmt->fetchAll() as $r) {
        $results['Trainers'][] = [$r['full_name'], $r['trainer_code'] . ' - ' . $r['specialization'], base_url('modules/trainers/edit.php?id=' . $r['trainer_id'])];
    }

    
    $stmt = $pdo->prepare("SELECT equipment_id, equipment_code, equipment_name, location FROM equipment
        WHERE equipment_name LIKE ? OR equipment_code LIKE ? OR location LIKE ? LIMIT 8");
    $stmt->execute([$like, $like, $like]);
    foreach ($stmt->fetchAll() as $r) {
        $results['Equipment'][] = [$r['equipment_name'], $r['equipment_code'] . ' - ' . $r['location'], base_url('modules/equipment/edit.php?id=' . $r['equipment_id'])];
    }

    $stmt = $pdo->prepare("SELECT p.payment_id, p.payment_code, p.reference_number, m.full_name FROM payments p
        JOIN members m ON m.member_id = p.member_id
        WHERE p.payment_code LIKE ? OR p.reference_number LIKE ? OR m.full_name LIKE ? LIMIT 8");
    $stmt->execute([$like, $like, $like]);
    foreach ($stmt->fetchAll() as $r) {
        $results['Payments'][] = [$r['payment_code'], 'Ref ' . $r['reference_number'] . ' - ' . $r['full_name'], base_url('modules/payments/index.php')];
    }

    $stmt = $pdo->prepare("SELECT class_id, class_name, class_date FROM classes WHERE class_name LIKE ? LIMIT 8");
    $stmt->execute([$like]);
    foreach ($stmt->fetchAll() as $r) {
        $results['Classes'][] = [$r['class_name'], date('d M Y', strtotime($r['class_date'])), base_url('modules/classes/bookings.php?class_id=' . $r['class_id'])];
    }

    $stmt = $pdo->prepare("SELECT plan_id, plan_name, price FROM membership_plans WHERE plan_name LIKE ? LIMIT 8");
    $stmt->execute([$like]);
    foreach ($stmt->fetchAll() as $r) {
        $results['Membership Plans'][] = [$r['plan_name'], 'LKR ' . number_format($r['price']), base_url('modules/plans/edit.php?id=' . $r['plan_id'])];
    }
}

$totalCount = array_sum(array_map('count', $results));

$pageTitle = 'Search Results';
$pageSubtitle = $q !== '' ? "{$totalCount} result(s) for \"{$q}\"" : 'Type at least 2 characters to search';
$activeNav = '';
require __DIR__ . '/../includes/layout_start.php';
?>

<?php if ($q === ''): ?>
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-10 text-center text-slate-400 text-sm">Use the search bar above to find a member, employee, trainer, piece of equipment, payment, class or plan.</div>
<?php elseif ($totalCount === 0): ?>
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-10 text-center text-slate-400 text-sm">No results for "<?= e($q) ?>".</div>
<?php else: ?>
  <div class="grid md:grid-cols-2 gap-5">
    <?php foreach ($results as $group => $items): ?>
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 text-xs font-bold text-slate-500 uppercase tracking-wide"><?= e($group) ?></div>
        <div class="divide-y divide-slate-100">
          <?php foreach ($items as [$title, $subtitle, $link]): ?>
            <a href="<?= e($link) ?>" class="block px-5 py-3 hover:bg-slate-50 transition">
              <p class="text-sm font-semibold text-slate-900"><?= e($title) ?></p>
              <p class="text-xs text-slate-400"><?= e($subtitle) ?></p>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/layout_end.php'; ?>
