<?php
$pageTitle = 'Facilities';
$activePublicNav = 'facilities';
require_once __DIR__ . '/../includes/public_header.php';
?>

<section class="hero-banner px-6 pt-16 pb-12">
  <div class="hero-watermark w-[460px] h-[460px]"><?= gym_icon('dumbbell', 'w-full h-full') ?></div>
  <div class="max-w-7xl mx-auto">
    <span class="eyebrow"><?= gym_icon('dumbbell', 'w-3.5 h-3.5') ?> Two Floors, Zero Compromise</span>
    <h1 class="mt-6 text-4xl md:text-5xl font-black leading-tight max-w-2xl">Facilities built for every kind of training.</h1>
    <p class="mt-5 text-slate-400 text-lg max-w-2xl">From heavy strength work to recovery, every zone is purpose-built and fully equipped.</p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-6 py-10 pb-24">
  <div class="grid md:grid-cols-3 gap-5">
    <?php foreach ([
        ['dumbbell', 'Strength Training Arena', 'Full range of power racks, barbells and plate-loaded machines for serious lifters.'],
        ['heartbeat', 'High-Tech Cardio Zone', 'Treadmills, rowers and bikes with performance tracking built in.'],
        ['target', 'Free Weights & Dumbbells', 'Dumbbells up to 50kg and a dedicated Olympic lifting platform.'],
        ['users', 'Group Fitness Studio', 'Mirrored studio built for yoga, HIIT and dance-based classes.'],
        ['trophy', 'Personal Training Enclave', 'A quieter, dedicated space reserved for 1-on-1 coaching sessions.'],
        ['shield', 'Changing & Locker Suites', 'Secure lockers, showers and a relaxation lounge for before and after.'],
    ] as [$icon, $title, $desc]): ?>
      <div class="glass glass-hover rounded-2xl p-7">
        <div class="w-12 h-12 rounded-xl bg-teal-500/15 text-teal-400 flex items-center justify-center mb-5"><?= gym_icon($icon, 'w-6 h-6') ?></div>
        <p class="font-extrabold text-lg mb-2"><?= e($title) ?></p>
        <p class="text-slate-400 text-sm"><?= e($desc) ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<?php require __DIR__ . '/../includes/public_footer.php'; ?>
