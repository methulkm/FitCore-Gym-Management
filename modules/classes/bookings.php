<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$classId = (int) ($_GET['class_id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM classes WHERE class_id = ?');
$stmt->execute([$classId]);
$class = $stmt->fetch();
if (!$class) redirect_with_flash('modules/classes/index.php', 'error', 'Class not found.');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $memberId = (int) ($_POST['member_id'] ?? 0);
    if (!$memberId) {
        $errors[] = 'Please choose a member.';
    } else {
        // BR-13: prevent booking a full class, and prevent the same member double-booking this class.
        $bookedCount = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE class_id = ? AND status = 'booked'");
        $bookedCount->execute([$classId]);
        $dup = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE class_id = ? AND member_id = ? AND status = 'booked'");
        $dup->execute([$classId, $memberId]);

        if ($bookedCount->fetchColumn() >= $class['capacity']) {
            $errors[] = 'This class is already full (BR-13).';
        } elseif ($dup->fetchColumn() > 0) {
            $errors[] = 'This member already has a booking for this class.';
        } else {
            $pdo->prepare("INSERT INTO bookings (member_id, class_id, booking_type, slot_date, start_time, status) VALUES (?, ?, 'class', ?, ?, 'booked')")
                ->execute([$memberId, $classId, $class['class_date'], $class['start_time']]);
            redirect_with_flash('modules/classes/bookings.php?class_id=' . $classId, 'success', 'Member booked into class.');
        }
    }
}

$members = $pdo->query("SELECT member_id, member_code, full_name FROM members WHERE account_status = 'active' ORDER BY full_name")->fetchAll();
$bookings = $pdo->prepare("SELECT b.*, m.full_name, m.member_code FROM bookings b JOIN members m ON m.member_id = b.member_id WHERE b.class_id = ? ORDER BY b.booking_id DESC");
$bookings->execute([$classId]);
$bookings = $bookings->fetchAll();

$pageTitle = 'Bookings - ' . $class['class_name'];
$activeNav = 'classes';
$ownerTag = 'Senuka';
require __DIR__ . '/../../includes/layout_start.php';
?>
<div class="grid lg:grid-cols-2 gap-6">
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
    <h3 class="font-extrabold mb-1">Book a Member</h3>
    <p class="text-xs text-slate-500 mb-4"><?= (int) $class['capacity'] ?> capacity &middot; <?= e(date('D, d M', strtotime($class['class_date']))) ?> at <?= e(date('h:i A', strtotime($class['start_time']))) ?></p>
    <?php foreach ($errors as $err): ?>
      <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium px-4 py-3 mb-4"><?= e($err) ?></div>
    <?php endforeach; ?>
    <form method="post" class="flex gap-2">
      <select name="member_id" required class="flex-1 border border-slate-200 rounded-[10px] px-3 py-2.5 text-sm">
        <option value="">Select member</option>
        <?php foreach ($members as $m): ?>
          <option value="<?= (int) $m['member_id'] ?>"><?= e($m['member_code'] . ' - ' . $m['full_name']) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold px-4 py-2.5 rounded-[10px] whitespace-nowrap">Book</button>
    </form>
  </div>
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
    <h3 class="font-extrabold mb-4">Current Bookings (<?= count(array_filter($bookings, fn($b) => $b['status'] === 'booked')) ?>)</h3>
    <div class="space-y-2 max-h-96 overflow-y-auto">
      <?php foreach ($bookings as $b): ?>
        <div class="flex items-center justify-between text-sm border-b border-slate-100 pb-2">
          <span><?= e($b['full_name']) ?> <span class="text-teal-600 text-xs font-bold"><?= e($b['member_code']) ?></span></span>
          <div class="flex items-center gap-2">
            <?= status_badge(ucfirst($b['status']), match ($b['status']) { 'booked' => 'emerald', 'completed' => 'indigo', default => 'rose' }) ?>
            <?php if ($b['status'] === 'booked'): ?>
              <a href="<?= e(base_url('modules/classes/booking_cancel.php?id=' . $b['booking_id'] . '&class_id=' . $classId)) ?>" class="text-rose-500 text-xs font-bold">Cancel</a>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (!$bookings): ?><p class="text-slate-400 text-sm">No bookings yet.</p><?php endif; ?>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
