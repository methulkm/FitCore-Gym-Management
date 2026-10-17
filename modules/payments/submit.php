<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']); // admin can log a submission on a member's behalf (e.g. counter/manual slip handoff)

$members = $pdo->query("SELECT member_id, member_code, full_name FROM members WHERE account_status = 'active' ORDER BY full_name")->fetchAll();
$plans = $pdo->query("SELECT plan_id, plan_name, price FROM membership_plans WHERE status = 'active' ORDER BY duration_months")->fetchAll();
$errors = [];

const MAX_SLIP_BYTES = 5 * 1024 * 1024; // NFR-P04: 5MB max
const ALLOWED_SLIP_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'application/pdf' => 'pdf'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify('modules/payments/submit.php');
    $memberId = (int) ($_POST['member_id'] ?? 0);
    $planId = (int) ($_POST['plan_id'] ?? 0);
    $amountRaw = trim($_POST['amount'] ?? '');
    $amount = (float) $amountRaw;
    $transferDate = trim($_POST['transfer_date'] ?? '');
    $refNumber = strtoupper(trim($_POST['reference_number'] ?? ''));

    $planPrices = array_column($plans, 'price', 'plan_id');
    if (!in_array($memberId, array_map('intval', array_column($members, 'member_id')), true)) $errors[] = 'Please choose an active member.';
    if (!isset($planPrices[$planId])) $errors[] = 'Please choose an active plan.';
    v_push($errors, v_required($amountRaw, 'Amount'));
    $amountErr = v_money($amountRaw, 'Amount');
    if ($amountErr) {
        $errors[] = $amountErr;
    } elseif ($amountRaw !== '' && isset($planPrices[$planId]) && round($amount, 2) !== round((float) $planPrices[$planId], 2)) {
        $errors[] = 'Amount must match the selected plan price (LKR ' . number_format((float) $planPrices[$planId], 2) . ').';
    }

    v_push($errors, v_required($transferDate, 'Transfer date'));
    $dateErr = v_date($transferDate, 'Transfer date');
    if ($dateErr) {
        $errors[] = $dateErr;
    } elseif ($transferDate > date('Y-m-d')) {
        $errors[] = 'Transfer date cannot be in the future.';
    } elseif ($transferDate !== '' && $transferDate < date('Y-m-d', strtotime('-90 days'))) {
        $errors[] = 'Transfer date cannot be more than 90 days ago.';
    }

    v_push($errors, v_required($refNumber, 'Reference number'));
    $refErr = v_reference($refNumber);
    if ($refErr) {
        $errors[] = $refErr;
    } elseif ($refNumber !== '') {
        $dupRef = $pdo->prepare("SELECT payment_code FROM payments WHERE reference_number = ? AND status <> 'rejected'");
        $dupRef->execute([$refNumber]);
        if ($code = $dupRef->fetchColumn()) $errors[] = "This reference number was already submitted ({$code}).";
    }

    $slipPath = null;
    $noSlip = empty($_FILES['slip']) || $_FILES['slip']['error'] === UPLOAD_ERR_NO_FILE;
    if ($errors) {
        // Don't store the uploaded slip at all while other fields are still wrong - just say if it's missing.
        if ($noSlip) $errors[] = 'Please attach the bank transfer slip.';
    } elseif ($noSlip) {
        $errors[] = 'Please attach the bank transfer slip.';
    } elseif ($_FILES['slip']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Slip upload failed. Please try again.';
    } elseif ($_FILES['slip']['size'] > MAX_SLIP_BYTES) {
        $errors[] = 'Slip file must be 5MB or smaller.'; // BR/NFR-P04
    } else {
        // NFR-S04: validate real MIME type (not just extension), generate a safe random filename.
        $mime = mime_content_type($_FILES['slip']['tmp_name']);
        if (!isset(ALLOWED_SLIP_TYPES[$mime])) {
            $errors[] = 'Only JPG, PNG or PDF slips are accepted.';
        } else {
            $ext = ALLOWED_SLIP_TYPES[$mime];
            $safeName = bin2hex(random_bytes(16)) . '.' . $ext;
            $dest = __DIR__ . '/../../assets/uploads/slips/' . $safeName;
            if (!move_uploaded_file($_FILES['slip']['tmp_name'], $dest)) {
                $errors[] = 'Could not save the uploaded slip.';
            } else {
                $slipPath = 'assets/uploads/slips/' . $safeName;
            }
        }
    }

    if (!$errors) {
        $code = next_code($pdo, 'payments', 'payment_code', 'PAY', 4, 1001);
        $pdo->prepare('INSERT INTO payments (payment_code, member_id, plan_id, amount, transfer_date, reference_number, slip_path, status)
                        VALUES (?, ?, ?, ?, ?, ?, ?, "pending")')
            ->execute([$code, $memberId, $planId, $amount, $transferDate, $refNumber, $slipPath]);

        $memberName = $pdo->prepare('SELECT full_name FROM members WHERE member_id = ?');
        $memberName->execute([$memberId]);
        notify_admin($pdo, 'payment', "New payment {$code} submitted by {$memberName->fetchColumn()} - needs verification.", 'modules/payments/index.php');

        redirect_with_flash('modules/payments/submit.php', 'success', "Payment {$code} submitted and is now pending admin verification.");
    }
}

$recent = $pdo->query("SELECT p.*, m.full_name, m.member_code FROM payments p JOIN members m ON m.member_id = p.member_id ORDER BY p.payment_id DESC LIMIT 8")->fetchAll();

$pageTitle = 'Payment Submission';
$pageSubtitle = 'Bank transfer slip upload, reference number, commercial bank details';
$activeNav = 'payment-submission';
require __DIR__ . '/../../includes/layout_start.php';
?>

<div class="grid lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
    <h3 class="font-extrabold mb-4">Submit a Bank Transfer Payment</h3>
    <?php foreach ($errors as $err): ?>
      <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium px-4 py-3 mb-4"><?= e($err) ?></div>
    <?php endforeach; ?>
    <form method="post" enctype="multipart/form-data" class="grid grid-cols-2 gap-4">
      <?= csrf_field() ?>
      <div>
        <label class="text-xs font-bold text-slate-500">Member</label>
        <select name="member_id" required class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
          <option value="">Select member</option>
          <?php foreach ($members as $m): ?><option value="<?= (int) $m['member_id'] ?>"<?= selected($m['member_id'], old('member_id')) ?>><?= e($m['member_code'] . ' - ' . $m['full_name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="text-xs font-bold text-slate-500">Plan</label>
        <select name="plan_id" id="plan_id" required class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
          <option value="">Select plan</option>
          <?php foreach ($plans as $p): ?><option value="<?= (int) $p['plan_id'] ?>"<?= selected($p['plan_id'], old('plan_id')) ?> data-price="<?= (float) $p['price'] ?>"><?= e($p['plan_name']) ?> (LKR <?= number_format($p['price']) ?>)</option><?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="text-xs font-bold text-slate-500">Amount (LKR)</label>
        <input type="number" step="0.01" min="0.01" max="1000000" name="amount" id="amount" required value="<?= e(old('amount')) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
      </div>
      <div>
        <label class="text-xs font-bold text-slate-500">Transfer Date</label>
        <input type="date" name="transfer_date" required max="<?= e(date('Y-m-d')) ?>" value="<?= e(old('transfer_date', date('Y-m-d'))) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
      </div>
      <div class="col-span-2">
        <label class="text-xs font-bold text-slate-500">Reference Number</label>
        <input name="reference_number" required maxlength="40" value="<?= e(old('reference_number')) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
      </div>
      <div class="col-span-2">
        <label class="text-xs font-bold text-slate-500">Slip (JPG, PNG or PDF, max 5MB)</label>
        <input type="file" name="slip" required accept=".jpg,.jpeg,.png,.pdf" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm bg-white">
      </div>
      <div class="col-span-2">
        <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-[10px] px-5 py-2.5 text-sm">Submit Payment</button>
      </div>
    </form>
  </div>

  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
    <h3 class="font-extrabold mb-2">FitCore Bank Details</h3>
    <p class="text-sm text-slate-600">Bank: <b>Commercial Bank of Ceylon</b></p>
    <p class="text-sm text-slate-600">Account Name: <b>FitCore Gym (Pvt) Ltd</b></p>
    <p class="text-sm text-slate-600">Account No: <b>8001234567</b></p>
    <p class="text-sm text-slate-600 mb-4">Branch: <b>Colombo 03</b></p>
    <h4 class="text-xs font-bold text-slate-400 uppercase mb-2">Recent Submissions</h4>
    <div class="space-y-2">
      <?php foreach ($recent as $r): ?>
        <div class="flex items-center justify-between text-xs border-b border-slate-100 pb-2">
          <span><?= e($r['payment_code']) ?> &middot; <?= e($r['full_name']) ?></span>
          <?= status_badge(ucfirst($r['status']), match ($r['status']) { 'approved' => 'emerald', 'rejected' => 'rose', default => 'amber' }) ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<script>
  document.getElementById('plan_id').addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    document.getElementById('amount').value = opt.dataset.price || '';
  });
</script>
<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
