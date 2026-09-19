<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$sql = "SELECT s.*, m.full_name, m.member_code, p.plan_name, p.duration_months
        FROM subscriptions s
        JOIN members m ON m.member_id = s.member_id
        JOIN membership_plans p ON p.plan_id = s.plan_id
        ORDER BY s.subscription_id DESC";
$subs = $pdo->query($sql)->fetchAll();

$pageTitle = 'Subscription & Renewal Management';
$pageSubtitle = 'Track active subscriptions, expiry dates, 7-day expiring-soon alerts & renewals (FR-14, FR-15, BR-04, BR-09)';
$activeNav = 'subscriptions';
$ownerTag = 'Methul';
$headerActions = btn('+ New Subscription', base_url('modules/subscriptions/create.php'));
require __DIR__ . '/../../includes/layout_start.php'; //combine the layout
?>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
      <tr>
        <th class="text-left font-bold px-5 py-3">Member</th>
        <th class="text-left font-bold px-5 py-3">Plan</th>
        <th class="text-left font-bold px-5 py-3">Start Date</th>
        <th class="text-left font-bold px-5 py-3">Expiry Date</th>
        <th class="text-left font-bold px-5 py-3">Status</th>
        <th class="text-right font-bold px-5 py-3">Renewal Action</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
      <?php foreach ($subs as $s):
        [$dispKey, $dispLabel] = subscription_display_status($s['expiry_date'], $s['status']);
        $tone = match ($dispKey) { 'active' => 'emerald', 'expiring_soon' => 'amber', 'expired' => 'rose', default => 'slate' };
      ?>
        <tr>
          <td class="px-5 py-3">
            <div class="font-semibold text-slate-900"><?= e($s['full_name']) ?></div>
            <div class="text-teal-600 text-xs font-bold"><?= e($s['member_code']) ?></div>
          </td>
          <td class="px-5 py-3 text-slate-600"><?= e($s['plan_name']) ?> (<?= (int) $s['duration_months'] ?>M)</td>
          <td class="px-5 py-3 text-slate-600 font-medium"><?= e(date('d M Y', strtotime($s['start_date']))) ?></td>
          <td class="px-5 py-3 text-slate-900 font-bold"><?= e(date('d M Y', strtotime($s['expiry_date']))) ?></td>
          <td class="px-5 py-3"><?= status_badge($dispLabel, $tone) ?></td>
          <td class="px-5 py-3 text-right">
            <?php if ($dispKey === 'expiring_soon' || $dispKey === 'active'): ?>
              <a href="<?= e(base_url('modules/subscriptions/renew.php?id=' . $s['subscription_id'])) ?>"
                 onclick="return confirm('Renew: extends from current expiry date since membership is still active (BR-09).')"
                 class="bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold px-3.5 py-2 rounded-[10px]">Renew</a>
            <?php else: ?>
              <a href="<?= e(base_url('modules/subscriptions/renew.php?id=' . $s['subscription_id'])) ?>"
                 onclick="return confirm('Renew: since membership has expired, new duration starts today (BR-09).')"
                 class="bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold px-3.5 py-2 rounded-[10px]">Restart</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$subs): ?>
        <tr><td colspan="6" class="px-5 py-8 text-center text-slate-400">No subscriptions yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
