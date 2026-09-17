<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = $pdo->prepare('SELECT p.*, m.full_name FROM payments p JOIN members m ON m.member_id = p.member_id WHERE payment_id = ?');
$stmt->execute([$id]);
$payment = $stmt->fetch();
if (!$payment) redirect_with_flash('modules/payments/index.php', 'error', 'Payment not found.');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = trim($_POST['reason'] ?? '') ?: 'Not specified';
    $pdo->prepare('UPDATE payments SET status = "rejected", rejection_reason = ?, verified_by = ?, verified_at = NOW() WHERE payment_id = ?')
        ->execute([$reason, current_user()['user_id'], $id]);
    redirect_with_flash('modules/payments/index.php', 'success', "Payment {$payment['payment_code']} rejected.");
}

$pageTitle = 'Reject Payment';
$activeNav = 'payment-verification';
$ownerTag = 'Asiri';
require __DIR__ . '/../../includes/layout_start.php';
?>
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 max-w-lg">
  <p class="text-sm text-slate-600 mb-4">Rejecting payment <b><?= e($payment['payment_code']) ?></b> from <b><?= e($payment['full_name']) ?></b>.</p>
  <form method="post">
    <label class="text-xs font-bold text-slate-500">Reason (e.g. unclear slip, incorrect amount, payment not found)</label>
    <textarea name="reason" rows="3" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm" required></textarea>
    <div class="flex gap-3 pt-4">
      <button type="submit" class="bg-rose-500 hover:bg-rose-600 text-white font-bold rounded-[10px] px-5 py-2.5 text-sm">Confirm Reject</button>
      <?= btn('Cancel', base_url('modules/payments/index.php'), 'ghost') ?>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
