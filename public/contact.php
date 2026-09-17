<?php
$pageTitle = 'Contact';
$activePublicNav = 'contact';
require_once __DIR__ . '/../includes/public_header.php';

$successMsg = flash('success');
$errorMsg = flash('error');
?>

<section class="hero-banner px-6 pt-16 pb-12">
  <div class="hero-watermark w-[420px] h-[420px]"><?= gym_icon('mail', 'w-full h-full') ?></div>
  <div class="max-w-7xl mx-auto">
    <span class="eyebrow"><?= gym_icon('mail', 'w-3.5 h-3.5') ?> Get In Touch</span>
    <h1 class="mt-6 text-4xl md:text-5xl font-black leading-tight max-w-2xl">Questions before you join? Ask away.</h1>
  </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-10 pb-24 grid md:grid-cols-5 gap-8">
  <div class="md:col-span-2 space-y-4">
    <div class="glass rounded-2xl p-6 flex items-center gap-4">
      <span class="w-11 h-11 rounded-xl bg-teal-500/15 text-teal-400 flex items-center justify-center shrink-0"><?= gym_icon('pin', 'w-5 h-5') ?></span>
      <div><p class="text-xs text-slate-500">Location</p><p class="font-semibold text-sm">Galle Road, Colombo 03</p></div>
    </div>
    <div class="glass rounded-2xl p-6 flex items-center gap-4">
      <span class="w-11 h-11 rounded-xl bg-teal-500/15 text-teal-400 flex items-center justify-center shrink-0"><?= gym_icon('phone', 'w-5 h-5') ?></span>
      <div><p class="text-xs text-slate-500">Phone</p><p class="font-semibold text-sm">+94 11 234 5678</p></div>
    </div>
    <div class="glass rounded-2xl p-6 flex items-center gap-4">
      <span class="w-11 h-11 rounded-xl bg-teal-500/15 text-teal-400 flex items-center justify-center shrink-0"><?= gym_icon('mail', 'w-5 h-5') ?></span>
      <div><p class="text-xs text-slate-500">Email</p><p class="font-semibold text-sm">hello@fitcore.lk</p></div>
    </div>
    <div class="glass rounded-2xl p-6 flex items-center gap-4">
      <span class="w-11 h-11 rounded-xl bg-teal-500/15 text-teal-400 flex items-center justify-center shrink-0"><?= gym_icon('clock', 'w-5 h-5') ?></span>
      <div><p class="text-xs text-slate-500">Hours</p><p class="font-semibold text-sm">Daily, 05:00 - 23:00</p></div>
    </div>
  </div>
  <div class="md:col-span-3 glass rounded-2xl p-8">
    <?php if ($successMsg): ?>
      <div class="rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm font-medium px-4 py-3 mb-5"><?= e($successMsg) ?></div>
    <?php endif; ?>
    <?php if ($errorMsg): ?>
      <div class="rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm font-medium px-4 py-3 mb-5"><?= e($errorMsg) ?></div>
    <?php endif; ?>
    <form method="post" action="<?= e(base_url('public/enquiry.php')) ?>" class="space-y-4">
      <div class="grid md:grid-cols-2 gap-4">
        <input name="name" required placeholder="Full Name" class="w-full bg-white/5 border border-white/10 rounded-[10px] px-4 py-3 text-sm placeholder-slate-500 focus:outline-none focus:border-teal-400">
        <input name="phone" placeholder="Phone" class="w-full bg-white/5 border border-white/10 rounded-[10px] px-4 py-3 text-sm placeholder-slate-500 focus:outline-none focus:border-teal-400">
      </div>
      <input type="email" name="email" placeholder="Email" class="w-full bg-white/5 border border-white/10 rounded-[10px] px-4 py-3 text-sm placeholder-slate-500 focus:outline-none focus:border-teal-400">
      <textarea name="message" required rows="4" placeholder="How can we help?" class="w-full bg-white/5 border border-white/10 rounded-[10px] px-4 py-3 text-sm placeholder-slate-500 focus:outline-none focus:border-teal-400"></textarea>
      <button class="inline-flex items-center gap-2 bg-teal-500 hover:bg-teal-400 text-[#07110F] font-extrabold px-7 py-3.5 rounded-[10px] btn-glow transition">Submit Gym Enquiry <?= gym_icon('arrow', 'w-4 h-4') ?></button>
    </form>
  </div>
</section>

<?php require __DIR__ . '/../includes/public_footer.php'; ?>
