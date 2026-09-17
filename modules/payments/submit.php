<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']); // admin can log a submission on a member's behalf (e.g. counter/manual slip handoff)

$members = $pdo->query("SELECT member_id, member_code, full_name FROM members WHERE account_status = 'active' ORDER BY full_name")->fetchAll();
$plans = $pdo->query("SELECT plan_id, plan_name, price FROM membership_plans WHERE status = 'active' ORDER BY duration_months")->fetchAll();
$errors = [];

const MAX_SLIP_BYTES = 5 * 1024 * 1024; // NFR-P04: 5MB max
const ALLOWED_SLIP_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'application/pdf' => 'pdf'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $memberId = (int) ($_POST['member_id'] ?? 0);
    $planId = (int) ($_POST['plan_id'] ?? 0);
    $amount = (float) ($_POST['amount'] ?? 0);
    $transferDate = $_POST['transfer_date'] ?? '';
    $refNumber = trim($_POST['reference_number'] ?? '');

    if (!$memberId) $errors[] = 'Please choose a member.';
    if (!$planId) $errors[] = 'Please choose a plan.';
    if ($amount <= 0) $errors[] = 'Amount must be greater than 0.';
    if (!$transferDate) $errors[] = 'Transfer date is required.';
    if ($refNumber === '') $errors[] = 'Reference number is required.';

    $slipPath = null;
    if (empty($_FILES['slip']) || $_FILES['slip']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Please attach the bank transfer slip.';
    } elseif ($_FILES['slip']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Slip upload failed. Please try again.';
    } elseif ($_FILES['slip']['size'] > MAX_SLIP_BYTES) {
        $errors[] = 'Slip file must be 5MB or smaller (NFR-P04).'; // BR/NFR-P04
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
        redirect_with_flash('modules/payments/submit.php', 'success', "Payment {$code} submitted and is now pending admin verification.");
    }
}

$recent = $pdo->query("SELECT p.*, m.full_name, m.member_code FROM payments p JOIN members m ON m.member_id = p.member_id ORDER BY p.payment_id DESC LIMIT 8")->fetchAll();

$pageTitle = 'Payment Submission';
$pageSubtitle = 'Bank transfer slip upload, reference number, commercial bank details (FR-12, FR-16, FR-18, NFR-P04, UC-12)';
$activeNav = 'payment-submission';
$ownerTag = 'Asiri';
require __DIR__ . '/../../includes/layout_start.php';
?>

<div class="grid lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
    <h3 class="font-extrabold mb-4">Submit a Bank Transfer Payment</h3>
    <?php foreach ($errors as $err): ?>
      <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium px-4 py-3 mb-4"><?= e($err) ?></div>
    <?php endforeach; ?>
    <form method="post" enctype="multipart/form-data" class="grid grid-cols-2 gap-4">
      <div>
        <label class="text-xs font-bold text-slate-500">Member</label>
        <select name="member_id" required class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
          <option value="">Select member</option>
          <?php foreach ($members as $m): ?><option value="<?= (int) $m['member_id'] ?>"><?= e($m['member_code'] . ' - ' . $m['full_name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="text-xs font-bold text-slate-500">Plan</label>
        <select name="plan_id" id="plan_id" required class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
          <option value="">Select plan</option>
          <?php foreach ($plans as $p): ?><option value="<?= (int) $p['plan_id'] ?>" data-price="<?= (float) $p['price'] ?>"><?= e($p['plan_name']) ?> (LKR <?= number_format($p['price']) ?>)</option><?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="text-xs font-bold text-slate-500">Amount (LKR)</label>
        <input type="number" step="0.01" name="amount" id="amount" required class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
      </div>
      <div>
        <label class="text-xs font-bold text-slate-500">Transfer Date</label>
        <input type="date" name="transfer_date" required value="<?= e(date('Y-m-d')) ?>" class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
      </div>
      <div class="col-span-2">
        <label class="text-xs font-bold text-slate-500">Reference Number</label>
        <input name="reference_number" required class="mt-1 w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm">
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
