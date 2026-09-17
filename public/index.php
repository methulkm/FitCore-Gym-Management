<?php
$pageTitle = 'Home';
$activePublicNav = 'home';
require_once __DIR__ . '/../includes/public_header.php';
?>

<section class="hero-banner px-6 pt-16 pb-28 md:pt-24">
  <div class="hero-watermark w-[520px] h-[520px]"><?= gym_icon('dumbbell', 'w-full h-full') ?></div>
  <div class="max-w-7xl mx-auto grid lg:grid-cols-[1.1fr_.9fr] gap-16 items-center">
    <div>
      <span class="eyebrow"><?= gym_icon('bolt', 'w-3.5 h-3.5') ?> Colombo's Elite High-Performance Gym</span>
      <h1 class="mt-6 text-5xl md:text-6xl xl:text-7xl font-black leading-[1.05] tracking-tight">
        Build Strength.<br>
        <span class="bg-gradient-to-r from-teal-300 to-teal-500 bg-clip-text text-transparent">Build Confidence.</span><br>
        Build Your Best.
      </h1>
      <p class="mt-6 text-slate-400 text-lg max-w-lg">World-class equipment, certified coaches, and dynamic group training &mdash; run on a membership system built for people serious about showing up.</p>
      <div class="mt-9 flex flex-wrap items-center gap-4">
        <a href="<?= e(base_url('public/plans.php')) ?>" class="inline-flex items-center gap-2 bg-teal-500 hover:bg-teal-400 text-[#07110F] font-extrabold px-7 py-4 rounded-[12px] btn-glow transition">View Membership Plans <?= gym_icon('arrow', 'w-4 h-4') ?></a>
        <a href="<?= e(base_url('auth/login.php')) ?>" class="inline-flex items-center gap-2 glass hover:bg-white/10 font-bold px-7 py-4 rounded-[12px] transition">Member / Staff Login</a>
      </div>

      <div class="mt-14 grid grid-cols-2 sm:grid-cols-4 gap-x-6 gap-y-8 max-w-xl">
        <?php foreach ([
            ['dumbbell', '450+', 'Active Members'],
            ['trophy', '12+', 'Certified Trainers'],
            ['target', '68+', 'Modern Machines'],
            ['shield', '24/7', 'Secure Facility'],
        ] as [$icon, $n, $l]): ?>
          <div>
            <div class="text-teal-400 mb-1.5"><?= gym_icon($icon, 'w-5 h-5') ?></div>
            <p class="text-2xl font-extrabold"><?= e($n) ?></p>
            <p class="text-xs text-slate-500 font-medium"><?= e($l) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="relative h-[420px] hidden lg:block">
      <div class="absolute inset-8 rounded-[28px] bg-gradient-to-br from-teal-500/20 via-transparent to-indigo-500/10 border border-white/10"></div>

      <div class="absolute top-2 right-4 w-56 glass rounded-2xl p-5 shadow-2xl -rotate-3">
        <div class="flex items-center gap-2 text-teal-400 mb-2"><?= gym_icon('heartbeat', 'w-5 h-5') ?><span class="text-xs font-bold uppercase tracking-wide">Live Floor</span></div>
        <p class="text-3xl font-extrabold">142</p>
        <p class="text-xs text-slate-400">Check-ins today</p>
      </div>

      <div class="absolute top-40 left-2 w-60 glass rounded-2xl p-5 shadow-2xl rotate-2">
        <div class="flex items-center gap-2 text-amber-400 mb-3"><?= gym_icon('flame', 'w-5 h-5') ?><span class="text-xs font-bold uppercase tracking-wide">This Week</span></div>
        <div class="flex items-end gap-1.5 h-16">
          <?php foreach ([40, 65, 50, 80, 60, 95, 70] as $h): ?>
            <div class="flex-1 bg-gradient-to-t from-teal-500 to-teal-300 rounded-t" style="height:<?= $h ?>%"></div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="absolute bottom-4 right-10 w-52 glass rounded-2xl p-5 shadow-2xl -rotate-2">
        <div class="flex text-amber-400 gap-0.5 mb-2"><?= str_repeat(gym_icon('star', 'w-4 h-4 fill-current'), 5) ?></div>
        <p class="text-sm font-bold">4.9 out of 5</p>
        <p class="text-xs text-slate-400">from 200+ member reviews</p>
      </div>
    </div>
  </div>
</section>

<section class="relative bg-[#0B1120] border-y border-white/10 diagonal-top -mt-10 pt-16">
  <div class="max-w-7xl mx-auto px-6 py-16">
    <div class="max-w-xl mb-12">
      <span class="eyebrow">Why FitCore</span>
      <h2 class="mt-4 text-3xl md:text-4xl font-extrabold">Everything a serious gym needs, none of the clutter.</h2>
    </div>
    <div class="grid md:grid-cols-3 gap-5">
      <a href="<?= e(base_url('public/facilities.php')) ?>" class="glass glass-hover rounded-2xl p-7 md:row-span-2 flex flex-col justify-between">
        <div>
          <div class="w-12 h-12 rounded-xl bg-teal-500/15 text-teal-400 flex items-center justify-center mb-5"><?= gym_icon('dumbbell', 'w-6 h-6') ?></div>
          <p class="font-extrabold text-lg mb-2">World-Class Facilities</p>
          <p class="text-slate-400 text-sm">Two full floors covering strength, cardio, free weights, and a dedicated group studio &mdash; all with modern imported equipment.</p>
        </div>
        <span class="inline-flex items-center gap-1.5 mt-6 text-teal-400 text-sm font-bold">Explore Facilities <?= gym_icon('arrow', 'w-4 h-4') ?></span>
      </a>
      <a href="<?= e(base_url('public/trainers.php')) ?>" class="glass glass-hover rounded-2xl p-7">
        <div class="w-12 h-12 rounded-xl bg-indigo-500/15 text-indigo-300 flex items-center justify-center mb-5"><?= gym_icon('users', 'w-6 h-6') ?></div>
        <p class="font-extrabold text-lg mb-2">Certified Coaches</p>
        <p class="text-slate-400 text-sm">CrossFit, Yoga, Bodybuilding and more &mdash; book 1-on-1 time with a specialist.</p>
      </a>
      <a href="<?= e(base_url('public/classes.php')) ?>" class="glass glass-hover rounded-2xl p-7">
        <div class="w-12 h-12 rounded-xl bg-amber-500/15 text-amber-300 flex items-center justify-center mb-5"><?= gym_icon('calendar', 'w-6 h-6') ?></div>
        <p class="font-extrabold text-lg mb-2">Live Class Schedule</p>
        <p class="text-slate-400 text-sm">Real-time capacity on every group class &mdash; log in and reserve your spot in seconds.</p>
      </a>
      <a href="<?= e(base_url('public/plans.php')) ?>" class="glass glass-hover rounded-2xl p-7 md:col-span-2">
        <div class="flex items-center gap-5">
          <div class="w-12 h-12 rounded-xl bg-teal-500/15 text-teal-400 flex items-center justify-center shrink-0"><?= gym_icon('shield', 'w-6 h-6') ?></div>
          <div>
            <p class="font-extrabold text-lg mb-1">Flexible Membership Plans</p>
            <p class="text-slate-400 text-sm">1, 3, 6, or 12-month tiers with transparent LKR pricing and bank-slip payment verification.</p>
          </div>
        </div>
      </a>
    </div>
  </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-20">
  <div class="text-center max-w-xl mx-auto mb-12">
    <span class="eyebrow">Member Voices</span>
    <h2 class="mt-4 text-3xl md:text-4xl font-extrabold">Trusted by people who train hard.</h2>
  </div>
  <div class="grid md:grid-cols-3 gap-5">
    <?php foreach ([
        ['Sanduni Perera', 'Member since 2026', 'The Gold Pro Pass paid for itself in the first month. Booking classes online is genuinely effortless.'],
        ['Kasun Nimalka', 'CrossFit Regular', 'Best-equipped strength floor in Colombo 03, hands down. Ruwan\'s coaching pushed my numbers up fast.'],
        ['Nadeesha Silva', 'Yoga & Wellness', 'Clean facility, friendly front desk, and the app makes tracking my check-ins actually satisfying.'],
    ] as [$name, $role, $quote]): ?>
      <div class="glass rounded-2xl p-7">
        <div class="text-teal-400 mb-4"><?= gym_icon('quote', 'w-7 h-7') ?></div>
        <p class="text-slate-300 text-sm leading-relaxed mb-6">&ldquo;<?= e($quote) ?>&rdquo;</p>
        <div class="flex items-center gap-3">
          <span class="w-10 h-10 rounded-full bg-gradient-to-br from-teal-400 to-teal-600 flex items-center justify-center font-extrabold text-[#07110F] text-xs"><?= e(strtoupper(substr($name,0,2))) ?></span>
          <div>
            <p class="text-sm font-bold"><?= e($name) ?></p>
            <p class="text-xs text-slate-500"><?= e($role) ?></p>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="max-w-7xl mx-auto px-6 pb-24">
  <div class="relative rounded-[28px] overflow-hidden bg-gradient-to-br from-teal-600 to-teal-500 px-8 py-14 md:p-16 text-center">
    <div class="absolute -top-20 -right-20 w-72 h-72 rounded-full bg-white/10 blur-3xl"></div>
    <div class="absolute -bottom-24 -left-10 w-72 h-72 rounded-full bg-black/10 blur-3xl"></div>
    <div class="relative">
      <h2 class="text-3xl md:text-5xl font-black text-[#07110F] mb-4">Ready to start training?</h2>
      <p class="text-[#07110F]/80 max-w-xl mx-auto mb-8">Pick a plan, submit your payment slip, and your membership is verified within hours &mdash; not days.</p>
      <a href="<?= e(base_url('public/plans.php')) ?>" class="inline-flex items-center gap-2 bg-[#07110F] text-white font-extrabold px-8 py-4 rounded-[12px] hover:bg-black transition">See Membership Plans <?= gym_icon('arrow', 'w-4 h-4') ?></a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/public_footer.php'; ?>
