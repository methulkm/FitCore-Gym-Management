<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$members = $pdo->query("SELECT member_id, member_code, full_name FROM members WHERE account_status = 'active' ORDER BY full_name")->fetchAll();
$plans = $pdo->query("SELECT plan_id, plan_name, duration_months, price FROM membership_plans WHERE status = 'active' ORDER BY duration_months")->fetchAll();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $memberId = (int) ($_POST['member_id'] ?? 0);
    $planId = (int) ($_POST['plan_id'] ?? 0);
    $startDate = $_POST['start_date'] ?: date('Y-m-d');

    if (!$memberId) $errors[] = 'Please choose a member.';
    if (!$planId) $errors[] = 'Please choose a plan.';

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT duration_months FROM membership_plans WHERE plan_id = ?');
        $stmt->execute([$planId]);
        $months = (int) $stmt->fetchColumn();
        $expiryDate = date('Y-m-d', strtotime("{$startDate} +{$months} months"));

        $pdo->prepare('INSERT INTO subscriptions (member_id, plan_id, start_date, expiry_date, status) VALUES (?, ?, ?, ?, "active")')
            ->execute([$memberId, $planId, $startDate, $expiryDate]);

        redirect_with_flash('modules/subscriptions/index.php', 'success', 'Subscription created.');
    }
}

$pageTitle = 'New Subscription';
$activeNav = 'subscriptions';
$ownerTag = 'Methul';
require __DIR__ . '/../../includes/layout_start.php';
?>
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 max-w-xl">
  <?php foreach ($errors as $err): ?>
    <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium px-4 py-3 mb-4"><?= e($err) ?></div>
  <?php endforeach; ?>
  <form method="post" class="space-y-4">
    <div>
      <label class="text-xs font-bold text-slate-500">Member</label>
      <select name="member_id" required class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
        <option value="">Select a member</option>
        <?php foreach ($members as $m): ?>
          <option value="<?= (int) $m['member_id'] ?>"><?= e($m['member_code'] . ' - ' . $m['full_name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Membership Plan</label>
      <select name="plan_id" required class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
        <option value="">Select a plan</option>
        <?php foreach ($plans as $p): ?>
          <option value="<?= (int) $p['plan_id'] ?>"><?= e($p['plan_name'] . ' (' . $p['duration_months'] . 'M - LKR ' . number_format($p['price']) . ')') ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="text-xs font-bold text-slate-500">Start Date</label>
      <input type="date" name="start_date" value="<?= e(date('Y-m-d')) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
    </div>
    <div class="flex gap-3 pt-2">
      <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-[10px] px-5 py-2.5 text-sm">Create Subscription</button>
      <?= btn('Cancel', base_url('modules/subscriptions/index.php'), 'ghost') ?>
    </div>
  </form>
</div>
<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
