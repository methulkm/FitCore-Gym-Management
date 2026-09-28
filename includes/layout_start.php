<?php
/**
 * Usage in every module page:
 *   $pageTitle = 'Membership Plans'; $pageSubtitle = '...'; $activeNav = 'plans'; $ownerTag = 'Methul';
 *   require __DIR__ . '/../../includes/layout_start.php';
 *   ... page content (KPI cards, tables, forms) ...
 *   require __DIR__ . '/../../includes/layout_end.php';
 */
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? 'FitCore Gym Manager') ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<script>
  tailwind.config = {
    theme: { extend: {
      colors: { teal: { 50:'#F0FDFA',500:'#14B8A6',600:'#0D9488',700:'#0F766E' } },
      fontFamily: { sans: ["'Plus Jakarta Sans'","Inter","sans-serif"] }
    } }
  };
</script>
<style> body { font-family: 'Plus Jakarta Sans', Inter, sans-serif; } </style>
</head>
<body class="bg-slate-50 text-slate-900" data-ajax-scope="<?= e(base_url('modules/')) ?>">
<div class="flex min-h-screen">
    <?php render_sidebar($activeNav ?? ''); ?>
    <div class="flex-1 flex flex-col min-w-0">
        <header class="h-[70px] shrink-0 bg-white border-b border-slate-200 flex items-center gap-4 px-6">
            <form action="<?= e(base_url('modules/search.php')) ?>" method="get" class="flex-1 max-w-md relative">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 w-4 h-4"><?= sidebar_icon('search') ?></span>
                <input type="text" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Search members, payments, trainers, equipment..." class="w-full bg-slate-100 rounded-[10px] pl-10 pr-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-500/30">
            </form>
            <div class="relative" id="notif-wrap">
                <button type="button" id="notif-btn" data-no-ajax class="relative w-10 h-10 flex items-center justify-center rounded-[10px] hover:bg-slate-100 text-slate-500">
                    <span class="w-5 h-5"><?= sidebar_icon('bell') ?></span>
                    <span id="notif-badge" class="hidden absolute top-1.5 right-1.5 min-w-[16px] h-4 px-1 rounded-full bg-rose-500 text-white text-[10px] font-bold flex items-center justify-center"></span>
                </button>
                <div id="notif-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-2xl border border-slate-200 shadow-xl z-40"></div>
            </div>
        </header>
        <script>
        (function () {
            var btn = document.getElementById('notif-btn'), panel = document.getElementById('notif-dropdown'), badge = document.getElementById('notif-badge');
            function refreshBadge() {
                fetch('<?= e(base_url('modules/notifications/dropdown.php')) ?>?peek=1', { credentials: 'same-origin' })
                    .then(function (r) { return r.text(); })
                    .then(function (html) {
                        var m = html.match(/data-unread-count="(\d+)"/);
                        var n = m ? parseInt(m[1], 10) : 0;
                        if (n > 0) { badge.textContent = n > 9 ? '9+' : n; badge.classList.remove('hidden'); }
                        else { badge.classList.add('hidden'); }
                        if (!panel.classList.contains('hidden')) panel.innerHTML = html;
                    }).catch(function () {});
            }
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                var willOpen = panel.classList.contains('hidden');
                panel.classList.toggle('hidden');
                if (willOpen) refreshBadge();
            });
            document.addEventListener('click', function (e) {
                if (!panel.contains(e.target) && e.target !== btn) panel.classList.add('hidden');
            });
            window.addEventListener('app-nav-start', function () { panel.classList.add('hidden'); });
            refreshBadge();
        })();
        </script>
        <main id="app-content" class="flex-1 p-8 space-y-6">
            <?php if ($msg = flash('success')): ?>
                <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-medium px-4 py-3"><?= e($msg) ?></div>
            <?php endif; ?>
            <?php if ($msg = flash('error')): ?>
                <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium px-4 py-3"><?= e($msg) ?></div>
            <?php endif; ?>
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h1 class="text-2xl font-extrabold flex items-center gap-2">
                        <?= e($pageTitle ?? '') ?>
                        <?php if (!empty($ownerTag)): ?><span class="text-xs font-bold px-2 py-1 rounded-full bg-indigo-50 text-indigo-500 align-middle"><?= e($ownerTag) ?></span><?php endif; ?>
                    </h1>
                    <?php if (!empty($pageSubtitle)): ?><p class="text-slate-500 text-sm mt-1"><?= e($pageSubtitle) ?></p><?php endif; ?>
                </div>
                <div><?= $headerActions ?? '' ?></div>
            </div>
