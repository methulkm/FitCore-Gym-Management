<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']); // UC-13 special requirement: only admin can approve/reject payments

$statusFilter = $_GET['status'] ?? 'pending';
$sql = "SELECT p.*, m.full_name, m.member_code, pl.plan_name, pl.duration_months
        FROM payments p
        JOIN members m ON m.member_id = p.member_id
        JOIN membership_plans pl ON pl.plan_id = p.plan_id";
$params = [];
if ($statusFilter !== 'all') {
    $sql .= ' WHERE p.status = ?';
    $params[] = $statusFilter;
}
$sql .= ' ORDER BY p.payment_id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$payments = $stmt->fetchAll();

$pendingCount = $pdo->query("SELECT COUNT(*) FROM payments WHERE status='pending'")->fetchColumn();

$pageTitle = 'Admin Payment Verification';
$pageSubtitle = 'Pending verification queue, slip preview, approve/reject (FR-13, FR-17, FR-18, BR-07, BR-08, UC-13)';
$activeNav = 'payment-verification';
$ownerTag = 'Asiri';
require __DIR__ . '/../../includes/layout_start.php';
?>

<?= kpi_card('Pending Verification', (string) $pendingCount, 'Needs action', 'text-amber-600') ?>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
  <div class="flex items-center gap-2 px-5 py-4 border-b border-slate-100">
    <?php foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All'] as $key => $label): ?>
      <a href="?status=<?= e($key) ?>" class="text-xs font-bold px-3 py-1.5 rounded-full <?= $statusFilter === $key ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
  </div>
  <table class="w-full text-sm">
    <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
      <tr>
        <th class="text-left font-bold px-5 py-3">Payment</th>
        <th class="text-left font-bold px-5 py-3">Member</th>
        <th class="text-left font-bold px-5 py-3">Plan</th>
        <th class="text-left font-bold px-5 py-3">Amount</th>
        <th class="text-left font-bold px-5 py-3">Slip</th>
        <th class="text-left font-bold px-5 py-3">Status</th>
        <th class="text-right font-bold px-5 py-3">Actions</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
      <?php foreach ($payments as $p): ?>
        <tr>
          <td class="px-5 py-3">
            <div class="font-bold text-teal-600 text-xs"><?= e($p['payment_code']) ?></div>
            <div class="text-slate-400 text-xs"><?= e(date('d M Y', strtotime($p['transfer_date']))) ?> &middot; Ref <?= e($p['reference_number']) ?></div>
          </td>
          <td class="px-5 py-3">
            <div class="font-semibold text-slate-900"><?= e($p['full_name']) ?></div>
            <div class="text-slate-400 text-xs"><?= e($p['member_code']) ?></div>
          </td>
          <td class="px-5 py-3 text-slate-600"><?= e($p['plan_name']) ?></td>
          <td class="px-5 py-3 font-bold text-slate-900">LKR <?= number_format($p['amount']) ?></td>
          <td class="px-5 py-3">
            <?php if ($p['slip_path']): ?>
              <a href="<?= e(base_url($p['slip_path'])) ?>" target="_blank" class="text-teal-600 text-xs font-bold underline">View Slip</a>
            <?php else: ?>
              <span class="text-slate-300 text-xs">-</span>
            <?php endif; ?>
          </td>
          <td class="px-5 py-3">
            <?= status_badge(ucfirst($p['status']), match ($p['status']) { 'approved' => 'emerald', 'rejected' => 'rose', default => 'amber' }) ?>
            <?php if ($p['status'] === 'rejected' && $p['rejection_reason']): ?>
              <div class="text-xs text-slate-400 mt-1"><?= e($p['rejection_reason']) ?></div>
            <?php endif; ?>
          </td>
          <td class="px-5 py-3 text-right space-x-2 whitespace-nowrap">
            <?php if ($p['status'] === 'pending'): ?>
              <a href="<?= e(base_url('modules/payments/approve.php?id=' . $p['payment_id'])) ?>" onclick="return confirm('Approve this payment? This activates/extends the membership (BR-08/BR-09).')" class="bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold px-3 py-2 rounded-[10px]">Approve</a>
              <a href="<?= e(base_url('modules/payments/reject.php?id=' . $p['payment_id'])) ?>" class="bg-white border border-slate-200 hover:bg-slate-50 text-rose-500 text-xs font-bold px-3 py-2 rounded-[10px]">Reject</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$payments): ?>
        <tr><td colspan="7" class="px-5 py-8 text-center text-slate-400">No payments in this view.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
