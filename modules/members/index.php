<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$statusFilter = $_GET['status'] ?? 'all';

// Pull each member with their latest subscription so we can compute BR-04 "Expiring Soon" at read time.
$sql = "SELECT m.*, s.expiry_date, s.status AS sub_status
        FROM members m
        LEFT JOIN (
            SELECT s1.* FROM subscriptions s1
            INNER JOIN (SELECT member_id, MAX(subscription_id) AS max_id FROM subscriptions GROUP BY member_id) latest
                ON s1.subscription_id = latest.max_id
        ) s ON s.member_id = m.member_id
        ORDER BY m.member_id DESC";
$members = $pdo->query($sql)->fetchAll();

$total = count($members);
$active = 0; $expiringSoon = 0; $newThisMonth = 0;
$rows = [];
foreach ($members as $m) {
    [$dispKey, $dispLabel] = $m['expiry_date'] ? subscription_display_status($m['expiry_date'], $m['sub_status']) : ['none', 'No Plan'];
    if ($m['account_status'] === 'active') $active++;
    if ($dispKey === 'expiring_soon') $expiringSoon++;
    if (date('Y-m', strtotime($m['join_date'])) === date('Y-m')) $newThisMonth++;
    $m['disp_key'] = $dispKey;
    $m['disp_label'] = $dispLabel;
    $rows[] = $m;
}

if ($statusFilter !== 'all') {
    $rows = array_values(array_filter($rows, function ($m) use ($statusFilter) {
        if ($statusFilter === 'inactive') return $m['account_status'] === 'deactivated';
        return $m['disp_key'] === $statusFilter;
    }));
}

$pageTitle = 'Member Management';
$pageSubtitle = 'Auto-generated member IDs, CRUD, search & status filters (FR-05, BR-02, UC-02)';
$activeNav = 'members';
$ownerTag = 'Sajatha';
$headerActions = btn('+ Add Member', base_url('modules/members/create.php'));
require __DIR__ . '/../../includes/layout_start.php';
?>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
  <?= kpi_card('Total Members', (string)$total) ?>
  <?= kpi_card('Active', (string)$active, '', 'text-emerald-600') ?>
  <?= kpi_card('Expiring Soon', (string)$expiringSoon, '<= 7 days left', 'text-amber-600') ?>
  <?= kpi_card('New This Month', (string)$newThisMonth, '', 'text-teal-600') ?>
</div>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
  <div class="flex items-center gap-2 px-5 py-4 border-b border-slate-100">
    <?php foreach (['all' => 'All', 'active' => 'Active', 'expiring_soon' => 'Expiring Soon', 'inactive' => 'Inactive'] as $key => $label): ?>
      <a href="?status=<?= e($key) ?>" class="text-xs font-bold px-3 py-1.5 rounded-full <?= $statusFilter === $key ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
  </div>
  <table class="w-full text-sm">
    <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
      <tr>
        <th class="text-left font-bold px-5 py-3">Member</th>
        <th class="text-left font-bold px-5 py-3">Phone</th>
        <th class="text-left font-bold px-5 py-3">Join Date</th>
        <th class="text-left font-bold px-5 py-3">Status</th>
        <th class="text-right font-bold px-5 py-3">Actions</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
      <?php foreach ($rows as $m): ?>
        <tr>
          <td class="px-5 py-3">
            <div class="font-semibold text-slate-900"><?= e($m['full_name']) ?></div>
            <div class="text-teal-600 text-xs font-bold"><?= e($m['member_code']) ?></div>
          </td>
          <td class="px-5 py-3 text-slate-600"><?= e($m['phone']) ?></td>
          <td class="px-5 py-3 text-slate-600"><?= e(date('d M Y', strtotime($m['join_date']))) ?></td>
          <td class="px-5 py-3">
            <?php
              $tone = match ($m['disp_key']) { 'active' => 'emerald', 'expiring_soon' => 'amber', 'expired' => 'rose', default => 'slate' };
              if ($m['account_status'] === 'deactivated') { $tone = 'rose'; $label = 'Inactive'; } else { $label = $m['disp_label']; }
              echo status_badge($label, $tone);
            ?>
          </td>
          <td class="px-5 py-3 text-right space-x-3 whitespace-nowrap">
            <a href="<?= e(base_url('modules/members/edit.php?id=' . $m['member_id'])) ?>" class="text-slate-500 hover:text-teal-600 text-sm font-bold">Edit</a>
            <?php if ($m['account_status'] === 'active'): ?>
              <?= delete_link(base_url('modules/members/delete.php?id=' . $m['member_id']), 'Deactivate this member account?') ?>
            <?php else: ?>
              <a href="<?= e(base_url('modules/members/delete.php?id=' . $m['member_id'] . '&reactivate=1')) ?>" class="text-emerald-600 hover:text-emerald-700 text-sm font-bold">Reactivate</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?>
        <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400">No members match this filter.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
