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
<body class="bg-slate-50 text-slate-900">
<div class="flex min-h-screen">
    <?php render_sidebar($activeNav ?? ''); ?>
    <div class="flex-1 flex flex-col min-w-0">
        <header class="h-[70px] shrink-0 bg-white border-b border-slate-200 flex items-center gap-4 px-6">
            <div class="flex-1 max-w-md">
                <input type="text" placeholder="Search members, payments, trainers, equipment..." class="w-full bg-slate-100 rounded-[10px] px-4 py-2.5 text-sm text-slate-500 focus:outline-none">
            </div>
        </header>
        <main class="flex-1 p-8 space-y-6">
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
