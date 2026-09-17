<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$trainers = $pdo->query("SELECT * FROM trainers WHERE status='active' ORDER BY trainer_id LIMIT 3")->fetchAll();
$classes = $pdo->query("SELECT c.*, t.full_name AS trainer_name,
                        (SELECT COUNT(*) FROM bookings b WHERE b.class_id=c.class_id AND b.status='booked') AS booked_count
                        FROM classes c JOIN trainers t ON t.trainer_id=c.trainer_id
                        WHERE c.status='scheduled' AND c.class_date >= CURDATE() ORDER BY c.class_date LIMIT 4")->fetchAll();
$plans = $pdo->query("SELECT * FROM membership_plans WHERE status='active' ORDER BY duration_months")->fetchAll();
$successMsg = flash('success');
$errorMsg = flash('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FitCore Gym - Premium Fitness Club</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<script>
  tailwind.config = { theme: { extend: {
    colors: { teal: { 50:'#F0FDFA',500:'#14B8A6',600:'#0D9488',700:'#0F766E' } },
    fontFamily: { sans: ["'Plus Jakarta Sans'","Inter","sans-serif"] }
  } } };
</script>
<style> body { font-family: 'Plus Jakarta Sans', Inter, sans-serif; } </style>
</head>
<body class="bg-[#0A0F1D] text-white">

<header class="sticky top-0 z-30 backdrop-blur bg-[#0A0F1D]/80 border-b border-white/10">
  <div class="max-w-7xl mx-auto flex items-center justify-between px-6 py-4">
    <div class="flex items-center gap-3">
      <span class="w-10 h-10 rounded-[10px] bg-gradient-to-br from-teal-600 to-teal-400 flex items-center justify-center font-extrabold">FC</span>
      <span><span class="block font-extrabold leading-none">Fit<span class="text-teal-400">Core</span></span><span class="block text-[9px] font-bold tracking-widest text-slate-400">PREMIUM FITNESS CLUB</span></span>
    </div>
    <nav class="hidden lg:flex items-center gap-8 text-sm font-medium text-slate-300">
      <a href="#about" class="hover:text-white">About</a>
      <a href="#facilities" class="hover:text-white">Facilities</a>
      <a href="#trainers" class="hover:text-white">Trainers</a>
      <a href="#classes" class="hover:text-white">Classes</a>
      <a href="#plans" class="hover:text-white">Membership Plans</a>
      <a href="#gallery" class="hover:text-white">Gallery</a>
      <a href="#contact" class="hover:text-white">Contact</a>
    </nav>
    <a href="<?= e(base_url('auth/login.php')) ?>" class="bg-teal-600 hover:bg-teal-700 text-white text-sm font-bold px-5 py-2.5 rounded-[10px]">Portal Login</a>
  </div>
</header>

<section class="relative py-24 px-6 text-center overflow-hidden">
  <div class="absolute inset-0 bg-gradient-to-b from-teal-600/10 to-transparent"></div>
  <div class="relative max-w-4xl mx-auto">
    <span class="inline-block text-xs font-bold tracking-widest text-teal-400 bg-teal-500/10 border border-teal-500/30 px-4 py-2 rounded-full mb-6">COLOMBO'S ELITE HIGH-PERFORMANCE GYM &amp; WELLNESS CENTER</span>
    <h1 class="text-5xl md:text-7xl font-black leading-tight mb-6">Build Strength.<br><span class="text-teal-400">Build Confidence.</span><br>Build Your Best.</h1>
    <p class="text-slate-300 max-w-2xl mx-auto mb-8">Welcome to FitCore. Experience world-class strength equipment, certified personal coaches, dynamic group HIIT sessions, and seamless digital membership management.</p>
    <div class="flex items-center justify-center gap-4">
      <a href="#plans" class="bg-teal-600 hover:bg-teal-700 font-bold px-6 py-3.5 rounded-[10px]">View Membership Plans →</a>
      <a href="<?= e(base_url('auth/login.php')) ?>" class="bg-white/5 border border-white/20 hover:bg-white/10 font-bold px-6 py-3.5 rounded-[10px]">Member / Staff Login</a>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-2xl mx-auto mt-14">
      <?php foreach ([['450+','Active Members'],['12+','Certified Trainers'],['68+','Modern Machines'],['24/7','Secure Facility']] as [$n,$l]): ?>
        <div class="bg-white/5 border border-white/10 rounded-xl p-4"><p class="text-2xl font-extrabold text-teal-400"><?= e($n) ?></p><p class="text-xs text-slate-400"><?= e($l) ?></p></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section id="about" class="py-20 px-6 max-w-6xl mx-auto">
  <h2 class="text-3xl font-extrabold mb-4">About FitCore</h2>
  <p class="text-slate-300 max-w-3xl mb-8">FitCore is owned and operated by Mr. D. Dasanayaka, offering modern equipment, professional certified coaches, and flexible membership payments with slip verification &mdash; all in the heart of Colombo 03.</p>
  <div class="grid md:grid-cols-3 gap-4 text-sm text-slate-300">
    <div class="bg-white/5 border border-white/10 rounded-xl p-5">Modern imported equipment across two full floors</div>
    <div class="bg-white/5 border border-white/10 rounded-xl p-5">Certified professional trainers for every discipline</div>
    <div class="bg-white/5 border border-white/10 rounded-xl p-5">Flexible bank-transfer payments with slip verification</div>
  </div>
</section>

<section id="facilities" class="py-20 px-6 max-w-6xl mx-auto">
  <h2 class="text-3xl font-extrabold mb-8">Facilities &amp; Zones</h2>
  <div class="grid md:grid-cols-3 gap-5">
    <?php foreach (['Strength Training Arena','High-Tech Cardio Zone','Free Weights & Dumbbells','Group Fitness Studio','Personal Training Enclave','Changing & Locker Suites'] as $f): ?>
      <div class="bg-white/5 border border-white/10 rounded-2xl p-6 hover:border-teal-500/40 transition">
        <div class="w-10 h-10 rounded-lg bg-teal-500/20 mb-4"></div>
        <p class="font-bold"><?= e($f) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section id="trainers" class="py-20 px-6 max-w-6xl mx-auto">
  <h2 class="text-3xl font-extrabold mb-8">Master Trainers</h2>
  <div class="grid md:grid-cols-3 gap-5">
    <?php foreach ($trainers as $t): ?>
      <div class="bg-white/5 border border-white/10 rounded-2xl p-6 text-center">
        <span class="w-16 h-16 mx-auto rounded-full bg-teal-600 flex items-center justify-center font-extrabold text-lg mb-3"><?= e(strtoupper(substr($t['full_name'],0,2))) ?></span>
        <p class="font-bold"><?= e($t['full_name']) ?></p>
        <p class="text-teal-400 text-xs font-bold mb-3"><?= e($t['specialization']) ?></p>
        <a href="<?= e(base_url('auth/login.php')) ?>" class="text-xs font-bold bg-teal-600 hover:bg-teal-700 px-4 py-2 rounded-[10px] inline-block">Book 1-on-1 PT</a>
      </div>
    <?php endforeach; ?>
    <?php if (!$trainers): ?><p class="text-slate-400 col-span-3">Trainer profiles coming soon.</p><?php endif; ?>
  </div>
</section>

<section id="classes" class="py-20 px-6 max-w-6xl mx-auto">
  <h2 class="text-3xl font-extrabold mb-8">Classes &amp; Timetable</h2>
  <div class="grid md:grid-cols-2 gap-5">
    <?php foreach ($classes as $c):
      $pct = $c['capacity'] > 0 ? min(100, round($c['booked_count']/$c['capacity']*100)) : 0; ?>
      <div class="bg-white/5 border border-white/10 rounded-2xl p-5">
        <div class="flex justify-between mb-2"><p class="font-bold"><?= e($c['class_name']) ?></p><span class="text-teal-400 text-sm font-bold"><?= e(date('h:i A', strtotime($c['start_time']))) ?></span></div>
        <p class="text-xs text-slate-400 mb-3">with <?= e($c['trainer_name']) ?> &middot; <?= e(date('D, d M', strtotime($c['class_date']))) ?></p>
        <div class="w-full h-1.5 bg-white/10 rounded-full overflow-hidden"><div class="h-full bg-teal-500" style="width:<?= $pct ?>%"></div></div>
        <p class="text-xs text-slate-500 mt-1"><?= (int) $c['booked_count'] ?>/<?= (int) $c['capacity'] ?> spots &middot; Login to Book Spot</p>
      </div>
    <?php endforeach; ?>
    <?php if (!$classes): ?><p class="text-slate-400 col-span-2">No upcoming classes scheduled.</p><?php endif; ?>
  </div>
</section>

<section id="plans" class="py-20 px-6 max-w-6xl mx-auto">
  <h2 class="text-3xl font-extrabold mb-8">Membership Plans</h2>
  <div class="grid md:grid-cols-4 gap-5">
    <?php foreach ($plans as $p):
      $perks = array_filter(explode('|', $p['description']));
      $featured = (bool) $p['is_featured']; ?>
      <div class="rounded-2xl p-6 border <?= $featured ? 'bg-gradient-to-br from-teal-600 to-teal-500 border-transparent' : 'bg-white/5 border-white/10' ?>">
        <p class="text-xs font-bold text-white/60 mb-2"><?= (int) $p['duration_months'] ?> Month<?= $p['duration_months']>1?'s':'' ?></p>
        <p class="font-extrabold mb-1"><?= e($p['plan_name']) ?></p>
        <p class="text-2xl font-extrabold mb-3">LKR <?= number_format($p['price']) ?></p>
        <ul class="text-xs text-white/80 space-y-1 mb-4">
          <?php foreach ($perks as $perk): ?><li>✓ <?= e(trim($perk)) ?></li><?php endforeach; ?>
        </ul>
        <a href="<?= e(base_url('auth/login.php')) ?>" class="block text-center text-xs font-bold bg-white/10 hover:bg-white/20 py-2.5 rounded-[10px]">Login to Subscribe</a>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section id="gallery" class="py-20 px-6 max-w-6xl mx-auto">
  <h2 class="text-3xl font-extrabold mb-8">Photo Gallery</h2>
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <?php foreach (['from-teal-600 to-slate-800','from-indigo-600 to-slate-800','from-slate-600 to-slate-800','from-teal-500 to-indigo-700'] as $grad): ?>
      <div class="aspect-square rounded-xl bg-gradient-to-br <?= $grad ?>"></div>
    <?php endforeach; ?>
  </div>
</section>

<section id="contact" class="py-20 px-6 max-w-6xl mx-auto grid md:grid-cols-2 gap-10">
  <div>
    <h2 class="text-3xl font-extrabold mb-4">Contact &amp; Visitor Enquiry</h2>
    <p class="text-slate-300 text-sm mb-1">Galle Road, Colombo 03, Sri Lanka</p>
    <p class="text-slate-300 text-sm mb-1">+94 11 234 5678</p>
    <p class="text-slate-300 text-sm mb-1">hello@fitcore.lk</p>
    <p class="text-slate-300 text-sm">Open Daily: 05:00 AM - 11:00 PM</p>
  </div>
  <div class="bg-white/5 border border-white/10 rounded-2xl p-6">
    <?php if ($successMsg): ?>
      <div class="rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm font-medium px-4 py-3 mb-4"><?= e($successMsg) ?></div>
    <?php endif; ?>
    <?php if ($errorMsg): ?>
      <div class="rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm font-medium px-4 py-3 mb-4"><?= e($errorMsg) ?></div>
    <?php endif; ?>
    <form method="post" action="<?= e(base_url('public/enquiry.php')) ?>" class="space-y-3">
      <input name="name" required placeholder="Full Name" class="w-full bg-white/10 border border-white/10 rounded-[10px] px-4 py-2.5 text-sm placeholder-slate-400">
      <input name="phone" placeholder="Phone" class="w-full bg-white/10 border border-white/10 rounded-[10px] px-4 py-2.5 text-sm placeholder-slate-400">
      <input type="email" name="email" placeholder="Email" class="w-full bg-white/10 border border-white/10 rounded-[10px] px-4 py-2.5 text-sm placeholder-slate-400">
      <textarea name="message" required rows="3" placeholder="Message" class="w-full bg-white/10 border border-white/10 rounded-[10px] px-4 py-2.5 text-sm placeholder-slate-400"></textarea>
      <button class="w-full bg-teal-600 hover:bg-teal-700 font-bold py-3 rounded-[10px] text-sm">Submit Gym Enquiry</button>
    </form>
  </div>
</section>

<footer class="border-t border-white/10 py-8 px-6 text-center text-slate-500 text-xs">
  © 2026 FitCore Gym &middot; Apex Alliance (PPA Project)
</footer>
</body>
</html>
