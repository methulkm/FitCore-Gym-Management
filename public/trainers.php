<?php
$pageTitle = 'Trainers';
$activePublicNav = 'trainers';
require_once __DIR__ . '/../includes/public_header.php';

$trainers = $pdo->query("SELECT * FROM trainers WHERE status='active' ORDER BY trainer_id")->fetchAll();
?>

<section class="hero-banner px-6 pt-16 pb-12">
  <div class="hero-watermark w-[420px] h-[420px]"><?= gym_icon('users', 'w-full h-full') ?></div>
  <div class="max-w-7xl mx-auto">
    <span class="eyebrow"><?= gym_icon('users', 'w-3.5 h-3.5') ?> Meet the Team</span>
    <h1 class="mt-6 text-4xl md:text-5xl font-black leading-tight max-w-2xl">Coaches who train the way they coach.</h1>
    <p class="mt-5 text-slate-400 text-lg max-w-2xl">Every specialization, all certified, all bookable for 1-on-1 sessions after you log in.</p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-10 pb-24">
  <div class="grid md:grid-cols-3 gap-5">
    <?php foreach ($trainers as $t): ?>
      <div class="glass glass-hover rounded-2xl p-7 text-center">
        <span class="w-20 h-20 mx-auto rounded-full bg-gradient-to-br from-teal-400 to-teal-600 flex items-center justify-center font-extrabold text-2xl text-[#07110F] mb-4"><?= e(strtoupper(substr($t['full_name'],0,2))) ?></span>
        <p class="font-extrabold text-lg"><?= e($t['full_name']) ?></p>
        <span class="inline-block mt-2 mb-3 text-[11px] font-bold px-3 py-1 rounded-full bg-teal-500/15 text-teal-400"><?= e($t['specialization']) ?></span>
        <p class="text-slate-500 text-xs mb-5"><?= e($t['qualification']) ?></p>
        <a href="<?= e(base_url('auth/login.php')) ?>" class="inline-flex items-center gap-1.5 text-xs font-bold bg-teal-500 hover:bg-teal-400 text-[#07110F] px-4 py-2.5 rounded-[10px] btn-glow transition">Book 1-on-1 PT <?= gym_icon('arrow', 'w-3.5 h-3.5') ?></a>
      </div>
    <?php endforeach; ?>
    <?php if (!$trainers): ?><p class="text-slate-400 col-span-3">Trainer profiles coming soon.</p><?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/../includes/public_footer.php'; ?>
