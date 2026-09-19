<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

//retrive current plans from the database
$plans = $pdo->query('SELECT * FROM membership_plans ORDER BY duration_months ASC')->fetchAll();

$pageTitle = 'Membership Plans';
$pageSubtitle = 'Manage 1, 3, 6, and 12-month tier plans, pricing and benefits (FR-13, BR-05, BR-06, UC-10)';
$activeNav = 'plans';
$ownerTag = 'Methul';
$headerActions = btn('+ Add Plan', base_url('modules/plans/create.php'));
require __DIR__ . '/../../includes/layout_start.php';
?>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
  <?php foreach ($plans as $p):
    $perks = array_filter(explode('|', $p['description']));
    $featured = (bool) $p['is_featured'];
    $cardClass = $featured
        ? 'bg-gradient-to-br from-teal-600 to-teal-500 text-white border-transparent'
        : 'bg-white text-slate-900 border-slate-200';
    ?>
    <div class="rounded-2xl border shadow-sm p-6 flex flex-col <?= $cardClass ?>">
      <?php if ($featured): ?>
        <span class="inline-block text-[10px] font-bold px-2 py-1 rounded-full bg-white/20 w-fit mb-3">★ Most Popular</span>
      <?php else: ?>
        <span class="inline-block text-[10px] font-bold px-2 py-1 rounded-full bg-slate-100 text-slate-500 w-fit mb-3"><?= (int) $p['duration_months'] ?> Month<?= $p['duration_months'] > 1 ? 's' : '' ?></span>
      <?php endif; ?>
      <h3 class="text-lg font-extrabold mb-1"><?= e($p['plan_name']) ?></h3>
      <p class="text-2xl font-extrabold mb-4">LKR <?= number_format((float) $p['price']) ?></p>
      <ul class="space-y-1.5 text-sm mb-6 flex-1 <?= $featured ? 'text-white/90' : 'text-slate-600' ?>">
        <?php foreach ($perks as $perk): ?>
          <li class="flex items-center gap-2"><span>✓</span><?= e(trim($perk)) ?></li>
        <?php endforeach; ?>
      </ul>
      <?php if ($p['status'] === 'deactivated'): ?>
        <p class="text-xs font-bold mb-2 <?= $featured ? 'text-white/80' : 'text-rose-500' ?>">Deactivated</p>
      <?php endif; ?>
      <div class="flex items-center justify-between pt-3 border-t <?= $featured ? 'border-white/20' : 'border-slate-100' ?>">
        <a href="<?= e(base_url('modules/plans/edit.php?id=' . $p['plan_id'])) ?>" class="text-sm font-bold <?= $featured ? 'text-white' : 'text-slate-600 hover:text-teal-600' ?>">Edit</a>
        <?php if ($p['status'] === 'active'): ?>
          <a href="<?= e(base_url('modules/plans/delete.php?id=' . $p['plan_id'])) ?>" onclick="return confirm('Deactivate this plan? (kept for historical subscriptions - BR-06)')" class="text-sm font-bold <?= $featured ? 'text-white' : 'text-rose-500' ?>">Deactivate</a>
        <?php else: ?>
          <a href="<?= e(base_url('modules/plans/delete.php?id=' . $p['plan_id'] . '&reactivate=1')) ?>" class="text-sm font-bold <?= $featured ? 'text-white' : 'text-emerald-600' ?>">Reactivate</a>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
