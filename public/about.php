<?php
$pageTitle = 'About';
$activePublicNav = 'about';
require_once __DIR__ . '/../includes/public_header.php';
?>

<section class="hero-banner px-6 pt-16 pb-12">
  <div class="hero-watermark w-[420px] h-[420px]"><?= gym_icon('shield', 'w-full h-full') ?></div>
  <div class="max-w-7xl mx-auto">
    <span class="eyebrow"><?= gym_icon('shield', 'w-3.5 h-3.5') ?> Our Story</span>
    <h1 class="mt-6 text-4xl md:text-5xl font-black leading-tight max-w-2xl">Built by a gym owner who trains here too.</h1>
    <p class="mt-5 text-slate-400 text-lg max-w-2xl">FitCore is owned and operated by Mr. D. Dasanayaka, offering modern equipment, professional certified coaches, and flexible membership payments with slip verification &mdash; all in the heart of Colombo 03.</p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-10 grid md:grid-cols-3 gap-5">
  <?php foreach ([
      ['dumbbell', 'Modern Equipment', 'Imported strength and cardio machines across two full floors, serviced on a regular maintenance schedule.'],
      ['users', 'Certified Coaches', 'Every trainer on our floor holds a recognized certification in their discipline.'],
      ['shield', 'Verified Payments', 'Bank-transfer slips are checked and verified by admin before any membership activates.'],
  ] as [$icon, $title, $desc]): ?>
    <div class="glass rounded-2xl p-7">
      <div class="w-12 h-12 rounded-xl bg-teal-500/15 text-teal-400 flex items-center justify-center mb-5"><?= gym_icon($icon, 'w-6 h-6') ?></div>
      <p class="font-extrabold text-lg mb-2"><?= e($title) ?></p>
      <p class="text-slate-400 text-sm"><?= e($desc) ?></p>
    </div>
  <?php endforeach; ?>
</section>

<section class="max-w-7xl mx-auto px-6 py-10 pb-24 grid md:grid-cols-2 gap-5">
  <div class="glass rounded-2xl p-8">
    <div class="flex items-center gap-3 mb-4 text-teal-400"><?= gym_icon('clock', 'w-6 h-6') ?><p class="font-extrabold text-lg text-white">Operational Hours</p></div>
    <div class="flex justify-between text-sm text-slate-300 border-b border-white/10 py-3"><span>Monday - Sunday</span><span class="font-bold text-white">05:00 AM - 11:00 PM</span></div>
    <p class="text-slate-500 text-xs mt-3">Open every day of the year, including public holidays.</p>
  </div>
  <div class="glass rounded-2xl p-8">
    <div class="flex items-center gap-3 mb-4 text-teal-400"><?= gym_icon('pin', 'w-6 h-6') ?><p class="font-extrabold text-lg text-white">Find Us</p></div>
    <p class="text-slate-300 text-sm mb-1">Galle Road, Colombo 03, Sri Lanka</p>
    <p class="text-slate-300 text-sm mb-1">+94 11 234 5678</p>
    <p class="text-slate-300 text-sm">hello@fitcore.lk</p>
  </div>
</section>

<?php require __DIR__ . '/../includes/public_footer.php'; ?>
