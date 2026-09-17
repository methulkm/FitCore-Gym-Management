<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['member']);

$userId = current_user()['user_id'];
$member = $pdo->prepare('SELECT * FROM members WHERE user_id = ?');
$member->execute([$userId]);
$member = $member->fetch();
if (!$member) redirect_with_flash('auth/login.php', 'error', 'No member profile linked to this account.');

$sub = $pdo->prepare('SELECT s.*, p.plan_name FROM subscriptions s JOIN membership_plans p ON p.plan_id = s.plan_id WHERE member_id = ? ORDER BY subscription_id DESC LIMIT 1');
$sub->execute([$member['member_id']]);
$sub = $sub->fetch();

$attendance = $pdo->prepare('SELECT * FROM member_attendance WHERE member_id = ? ORDER BY attendance_date DESC LIMIT 8');
$attendance->execute([$member['member_id']]);
$attendance = $attendance->fetchAll();
$checkinCount = $pdo->prepare('SELECT COUNT(*) FROM member_attendance WHERE member_id = ?');
$checkinCount->execute([$member['member_id']]);
$checkinCount = (int) $checkinCount->fetchColumn();

$bookings = $pdo->prepare("SELECT b.*, c.class_name FROM bookings b LEFT JOIN classes c ON c.class_id = b.class_id
                            WHERE b.member_id = ? AND b.status = 'booked' ORDER BY b.slot_date, b.start_time");
$bookings->execute([$member['member_id']]);
$bookings = $bookings->fetchAll();

$pageTitle = 'My Membership';
$activeNav = 'member-portal';
require __DIR__ . '/../../includes/layout_start.php';
?>

<?php if ($sub):
  [$dispKey, $dispLabel] = subscription_display_status($sub['expiry_date'], $sub['status']);
  $tone = match ($dispKey) { 'active' => 'emerald', 'expiring_soon' => 'amber', 'expired' => 'rose', default => 'slate' };
?>
<div class="bg-gradient-to-br from-teal-600 to-teal-500 text-white rounded-2xl p-6">
  <p class="text-xs font-bold uppercase tracking-widest text-white/70 mb-1">Current Plan</p>
  <h2 class="text-2xl font-extrabold mb-3"><?= e($sub['plan_name']) ?></h2>
  <div class="flex items-center gap-6 text-sm">
    <span>Expiry: <b><?= e(date('d M Y', strtotime($sub['expiry_date']))) ?></b></span>
    <?= status_badge($dispLabel, $tone === 'emerald' ? 'slate' : $tone) ?>
  </div>
</div>
<?php else: ?>
<div class="rounded-xl bg-amber-50 border border-amber-200 text-amber-700 text-sm font-medium px-4 py-3">You don't have an active plan yet. Visit the front desk or submit a payment to subscribe.</div>
<?php endif; ?>

<div class="grid md:grid-cols-2 gap-6">
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
    <div class="flex items-center justify-between mb-3">
      <h3 class="font-extrabold">Attendance History</h3>
      <span class="text-xs font-bold text-teal-600"><?= $checkinCount ?> total check-ins</span>
    </div>
    <div class="space-y-2">
      <?php foreach ($attendance as $a): ?>
        <div class="flex justify-between text-sm border-b border-slate-100 pb-2">
          <span><?= e(date('D, d M Y', strtotime($a['attendance_date']))) ?></span>
          <span class="text-slate-500"><?= e(date('h:i A', strtotime($a['check_in_time']))) ?></span>
        </div>
      <?php endforeach; ?>
      <?php if (!$attendance): ?><p class="text-slate-400 text-sm">No check-ins recorded yet.</p><?php endif; ?>
    </div>
  </div>
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
    <h3 class="font-extrabold mb-3">Upcoming Bookings</h3>
    <div class="space-y-2">
      <?php foreach ($bookings as $b): ?>
        <div class="flex justify-between text-sm border-b border-slate-100 pb-2">
          <span><?= e($b['class_name'] ?? 'Personal Training') ?></span>
          <span class="text-slate-500"><?= e(date('d M', strtotime($b['slot_date']))) ?> &middot; <?= e(date('h:i A', strtotime($b['start_time']))) ?></span>
        </div>
      <?php endforeach; ?>
      <?php if (!$bookings): ?><p class="text-slate-400 text-sm">No upcoming bookings.</p><?php endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
