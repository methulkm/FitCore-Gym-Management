<?php
$pageTitle = 'Membership Plans';
$activePublicNav = 'plans';
require_once __DIR__ . '/../includes/public_header.php';

$plans = $pdo->query("SELECT * FROM membership_plans WHERE status='active' ORDER BY duration_months")->fetchAll();
?>

<section class="hero-banner px-6 pt-16 pb-12">
  <div class="hero-watermark w-[420px] h-[420px]"><?= gym_icon('shield', 'w-full h-full') ?></div>
  <div class="max-w-7xl mx-auto text-center">
    <span class="eyebrow"><?= gym_icon('shield', 'w-3.5 h-3.5') ?> Simple, Transparent Pricing</span>
    <h1 class="mt-6 text-4xl md:text-5xl font-black leading-tight">Pick a plan. Start training today.</h1>
    <p class="mt-5 text-slate-400 text-lg max-w-xl mx-auto">All plans include full gym floor access. Pay by bank transfer &mdash; your membership activates once your slip is verified.</p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-10 pb-24">
  <div class="grid md:grid-cols-4 gap-5">
    <?php foreach ($plans as $p):
      $perks = array_filter(explode('|', $p['description']));
      $featured = (bool) $p['is_featured']; ?>
      <div class="relative rounded-2xl p-7 border <?= $featured ? 'bg-gradient-to-br from-teal-500 to-teal-600 border-transparent md:-translate-y-3 shadow-2xl shadow-teal-500/20' : 'glass' ?>">
        <?php if ($featured): ?><span class="absolute -top-3 left-1/2 -translate-x-1/2 text-[10px] font-extrabold px-3 py-1 rounded-full bg-[#07110F] text-teal-300 flex items-center gap-1"><?= gym_icon('star', 'w-3 h-3 fill-current') ?> MOST POPULAR</span><?php endif; ?>
        <p class="text-xs font-bold <?= $featured ? 'text-white/70' : 'text-slate-500' ?> mb-2"><?= (int) $p['duration_months'] ?> Month<?= $p['duration_months']>1?'s':'' ?></p>
        <p class="font-extrabold text-lg mb-1"><?= e($p['plan_name']) ?></p>
        <p class="text-3xl font-black mb-5">LKR <?= number_format($p['price']) ?></p>
        <ul class="text-sm space-y-2.5 mb-7 <?= $featured ? 'text-white/90' : 'text-slate-300' ?>">
          <?php foreach ($perks as $perk): ?>
            <li class="flex items-center gap-2"><?= gym_icon('check', 'w-4 h-4 ' . ($featured ? 'text-white' : 'text-teal-400')) ?> <?= e(trim($perk)) ?></li>
          <?php endforeach; ?>
        </ul>
        <a href="<?= e(base_url('auth/login.php')) ?>" class="block text-center text-sm font-extrabold py-3 rounded-[10px] transition <?= $featured ? 'bg-[#07110F] text-white hover:bg-black' : 'bg-white/5 hover:bg-white/10 border border-white/10' ?>">Login to Subscribe</a>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<?php require __DIR__ . '/../includes/public_footer.php'; ?>
