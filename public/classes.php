<?php
$pageTitle = 'Classes';
$activePublicNav = 'classes';
require_once __DIR__ . '/../includes/public_header.php';

$classes = $pdo->query("SELECT c.*, t.full_name AS trainer_name,
                        (SELECT COUNT(*) FROM bookings b WHERE b.class_id=c.class_id AND b.status='booked') AS booked_count
                        FROM classes c JOIN trainers t ON t.trainer_id=c.trainer_id
                        WHERE c.status='scheduled' AND c.class_date >= CURDATE() ORDER BY c.class_date")->fetchAll();
?>

<section class="hero-banner px-6 pt-16 pb-12">
  <div class="hero-watermark w-[420px] h-[420px]"><?= gym_icon('calendar', 'w-full h-full') ?></div>
  <div class="max-w-7xl mx-auto">
    <span class="eyebrow"><?= gym_icon('calendar', 'w-3.5 h-3.5') ?> This Week</span>
    <h1 class="mt-6 text-4xl md:text-5xl font-black leading-tight max-w-2xl">Live class schedule, real-time capacity.</h1>
    <p class="mt-5 text-slate-400 text-lg max-w-2xl">Spots fill fast &mdash; log in to your portal to lock yours in before it's full.</p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-10 pb-24">
  <div class="grid md:grid-cols-2 gap-5">
    <?php foreach ($classes as $c):
      $pct = $c['capacity'] > 0 ? min(100, round($c['booked_count']/$c['capacity']*100)) : 0;
      $full = $c['booked_count'] >= $c['capacity']; ?>
      <div class="glass rounded-2xl p-6">
        <div class="flex justify-between items-start mb-1">
          <p class="font-extrabold text-lg"><?= e($c['class_name']) ?></p>
          <span class="text-teal-400 text-sm font-bold whitespace-nowrap"><?= e(date('h:i A', strtotime($c['start_time']))) ?></span>
        </div>
        <p class="text-xs text-slate-400 mb-4">with <?= e($c['trainer_name']) ?> &middot; <?= e(date('D, d M', strtotime($c['class_date']))) ?></p>
        <div class="w-full h-2 bg-white/10 rounded-full overflow-hidden mb-2">
          <div class="h-full <?= $full ? 'bg-rose-400' : 'bg-gradient-to-r from-teal-500 to-teal-300' ?>" style="width:<?= $pct ?>%"></div>
        </div>
        <p class="text-xs text-slate-500"><?= (int) $c['booked_count'] ?>/<?= (int) $c['capacity'] ?> spots &middot; <?= $full ? 'Class full' : 'Login to Book Spot' ?></p>
      </div>
    <?php endforeach; ?>
    <?php if (!$classes): ?><p class="text-slate-400 col-span-2">No upcoming classes scheduled.</p><?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/../includes/public_footer.php'; ?>
