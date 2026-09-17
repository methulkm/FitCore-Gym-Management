</main>
<footer class="chrome border-t border-white/10 mt-10">
  <div class="max-w-7xl mx-auto px-6 py-14 grid md:grid-cols-4 gap-10">
    <div>
      <div class="flex items-center gap-3 mb-4">
        <span class="w-10 h-10 rounded-[10px] bg-gradient-to-br from-teal-400 to-teal-600 flex items-center justify-center font-extrabold text-[#07110F]">FC</span>
        <span class="font-extrabold">Fit<span class="text-teal-400">Core</span></span>
      </div>
      <p class="text-slate-400 text-sm leading-relaxed">Colombo's elite high-performance gym &amp; wellness center. Built for people who train with intent.</p>
    </div>
    <div>
      <p class="text-xs font-bold tracking-widest text-slate-500 uppercase mb-4">Explore</p>
      <ul class="space-y-2.5 text-sm text-slate-400">
        <li><a href="<?= e(base_url('public/about.php')) ?>" class="hover:text-teal-400">About Us</a></li>
        <li><a href="<?= e(base_url('public/facilities.php')) ?>" class="hover:text-teal-400">Facilities</a></li>
        <li><a href="<?= e(base_url('public/trainers.php')) ?>" class="hover:text-teal-400">Trainers</a></li>
        <li><a href="<?= e(base_url('public/classes.php')) ?>" class="hover:text-teal-400">Classes</a></li>
        <li><a href="<?= e(base_url('public/plans.php')) ?>" class="hover:text-teal-400">Membership Plans</a></li>
      </ul>
    </div>
    <div>
      <p class="text-xs font-bold tracking-widest text-slate-500 uppercase mb-4">Contact</p>
      <ul class="space-y-2.5 text-sm text-slate-400">
        <li class="flex items-center gap-2"><?= gym_icon('pin', 'w-4 h-4 text-teal-400 shrink-0') ?> Galle Road, Colombo 03</li>
        <li class="flex items-center gap-2"><?= gym_icon('phone', 'w-4 h-4 text-teal-400 shrink-0') ?> +94 11 234 5678</li>
        <li class="flex items-center gap-2"><?= gym_icon('mail', 'w-4 h-4 text-teal-400 shrink-0') ?> hello@fitcore.lk</li>
      </ul>
    </div>
    <div>
      <p class="text-xs font-bold tracking-widest text-slate-500 uppercase mb-4">Hours</p>
      <ul class="space-y-2.5 text-sm text-slate-400">
        <li class="flex justify-between gap-4"><span>Mon - Sun</span><span class="text-white font-semibold">05:00 - 23:00</span></li>
      </ul>
      <a href="<?= e(base_url('auth/login.php')) ?>" class="inline-block mt-5 bg-white/5 hover:bg-white/10 border border-white/10 text-sm font-bold px-4 py-2.5 rounded-[10px] transition">Member / Staff Login</a>
    </div>
  </div>
  <div class="border-t border-white/10 py-6 px-6 text-center text-slate-500 text-xs">
    © 2026 FitCore Gym &middot; Apex Alliance (PPA Project)
  </div>
</footer>
</body>
</html>
