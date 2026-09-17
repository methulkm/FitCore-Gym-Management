<?php
/**
 * Shared header for the public marketing site — real separate pages, shared visual language.
 * Usage: set $pageTitle and $activePublicNav, then require this file.
 */
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/public_icons.php';

$publicNav = [
    'home'       => ['label' => 'Home',             'href' => 'public/index.php'],
    'about'      => ['label' => 'About',             'href' => 'public/about.php'],
    'facilities' => ['label' => 'Facilities',        'href' => 'public/facilities.php'],
    'trainers'   => ['label' => 'Trainers',          'href' => 'public/trainers.php'],
    'classes'    => ['label' => 'Classes',           'href' => 'public/classes.php'],
    'plans'      => ['label' => 'Membership Plans',  'href' => 'public/plans.php'],
    'contact'    => ['label' => 'Contact',           'href' => 'public/contact.php'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? 'FitCore Gym') ?> - FitCore Gym</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<script>
  tailwind.config = { theme: { extend: {
    colors: { teal: { 50:'#F0FDFA',400:'#2DD4BF',500:'#14B8A6',600:'#0D9488',700:'#0F766E' } },
    fontFamily: { sans: ["'Plus Jakarta Sans'","Inter","sans-serif"] }
  } } };
</script>
<style>
  body { font-family: 'Plus Jakarta Sans', Inter, sans-serif; background:#070B15; }

  .bg-atmosphere{position:fixed;inset:0;z-index:0;pointer-events:none;
    background:
      radial-gradient(700px 500px at 88% -8%, rgba(20,184,166,.16), transparent 65%),
      radial-gradient(600px 460px at -8% 105%, rgba(99,102,241,.12), transparent 65%);}
  .bg-grid{position:fixed;inset:0;z-index:0;background-image:linear-gradient(rgba(255,255,255,.035) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.035) 1px,transparent 1px);background-size:44px 44px;-webkit-mask-image:radial-gradient(ellipse 80% 60% at 50% 0%,#000 40%,transparent 100%);mask-image:radial-gradient(ellipse 80% 60% at 50% 0%,#000 40%,transparent 100%);pointer-events:none}
  main, header, footer { position:relative; z-index:1; }

  .eyebrow{display:inline-flex;align-items:center;gap:.5rem;font-size:.68rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:#5EEAD4;background:rgba(20,184,166,.1);border:1px solid rgba(45,212,191,.35);padding:.45rem 1rem;border-radius:9999px}
  .glass{background:rgba(20,25,38,.55);border:1px solid rgba(255,255,255,.08)}
  .glass-hover{transition:transform .25s ease,border-color .25s ease,background .25s ease}
  .glass-hover:hover{transform:translateY(-4px);border-color:rgba(45,212,191,.4);background:rgba(255,255,255,.06)}
  .btn-glow{box-shadow:0 8px 30px -8px rgba(13,148,136,.65)}
  .btn-glow:hover{box-shadow:0 10px 36px -6px rgba(13,148,136,.85)}
  .diagonal-top{clip-path:polygon(0 3%,100% 0,100% 100%,0 100%)}
  .num-outline{-webkit-text-stroke:1.5px rgba(255,255,255,.14);color:transparent}

  .chrome{background:#0D1526}
  .hero-banner{position:relative;overflow:hidden;background:#0A1122}
  .hero-banner::before{content:'';position:absolute;inset:0;z-index:0;pointer-events:none;
    background:
      repeating-linear-gradient(115deg, rgba(45,212,191,.055) 0px, rgba(45,212,191,.055) 2px, transparent 2px, transparent 42px),
      radial-gradient(900px 520px at 85% -10%, rgba(20,184,166,.30), transparent 60%),
      radial-gradient(700px 420px at -10% 110%, rgba(99,102,241,.16), transparent 60%);}
  .hero-banner::after{content:'';position:absolute;left:0;right:0;bottom:0;height:120px;z-index:0;pointer-events:none;
    background:linear-gradient(to bottom, transparent, #070B15);}
  .hero-banner > *{position:relative;z-index:1}
  .hero-watermark{position:absolute;top:50%;right:-60px;transform:translateY(-50%) rotate(-18deg);opacity:.05;z-index:0;pointer-events:none}
</style>
</head>
<body class="text-white antialiased">
<div class="bg-grid"></div>
<div class="bg-atmosphere"></div>

<header class="chrome sticky top-0 z-30 border-b border-white/10 shadow-[0_4px_24px_-8px_rgba(0,0,0,.5)]">
  <div class="max-w-7xl mx-auto flex items-center justify-between px-6 py-3.5">
    <a href="<?= e(base_url('public/index.php')) ?>" class="flex items-center gap-3">
      <span class="w-10 h-10 rounded-[10px] bg-gradient-to-br from-teal-400 to-teal-600 flex items-center justify-center font-extrabold text-[#07110F] shadow-[0_0_20px_-4px_rgba(45,212,191,.8)]">FC</span>
      <span><span class="block font-extrabold leading-none text-[15px]">Fit<span class="text-teal-400">Core</span></span><span class="block text-[9px] font-bold tracking-widest text-slate-400">PREMIUM FITNESS CLUB</span></span>
    </a>
    <nav class="hidden lg:flex items-center gap-7 text-sm font-semibold text-slate-300">
      <?php foreach ($publicNav as $key => $item): $isActive = ($activePublicNav ?? '') === $key; ?>
        <a href="<?= e(base_url($item['href'])) ?>" class="relative py-1 hover:text-white transition <?= $isActive ? 'text-white' : '' ?>">
          <?= e($item['label']) ?>
          <?php if ($isActive): ?><span class="absolute -bottom-[15px] left-0 right-0 h-[2px] bg-teal-400 rounded-full"></span><?php endif; ?>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="flex items-center gap-3">
      <a href="<?= e(base_url('auth/login.php')) ?>" class="hidden sm:inline-block bg-teal-500 hover:bg-teal-400 text-[#07110F] text-sm font-extrabold px-5 py-2.5 rounded-[10px] whitespace-nowrap btn-glow transition">Portal Login</a>
      <button type="button" onclick="document.getElementById('mobileNav').classList.toggle('hidden')" class="lg:hidden w-10 h-10 flex items-center justify-center rounded-[10px] border border-white/10 text-white">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-5 h-5"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
    </div>
  </div>
  <div id="mobileNav" class="chrome hidden lg:hidden border-t border-white/10 px-6 py-4 space-y-1">
    <?php foreach ($publicNav as $key => $item): $isActive = ($activePublicNav ?? '') === $key; ?>
      <a href="<?= e(base_url($item['href'])) ?>" class="block py-2.5 text-sm font-semibold <?= $isActive ? 'text-teal-400' : 'text-slate-300' ?>"><?= e($item['label']) ?></a>
    <?php endforeach; ?>
    <a href="<?= e(base_url('auth/login.php')) ?>" class="block mt-2 text-center bg-teal-500 text-[#07110F] text-sm font-extrabold px-5 py-2.5 rounded-[10px]">Portal Login</a>
  </div>
</header>
<main>
