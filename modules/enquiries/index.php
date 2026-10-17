<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$enquiries = $pdo->query('SELECT * FROM enquiries ORDER BY enquiry_id DESC')->fetchAll();

$pageTitle = 'Visitor Enquiries';
$pageSubtitle = 'Messages submitted through the public Contact page';
$activeNav = 'enquiries';
require __DIR__ . '/../../includes/layout_start.php';
?>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
  <div class="divide-y divide-slate-100">
    <?php foreach ($enquiries as $en): ?>
      <div class="px-5 py-4">
        <p class="font-semibold text-slate-900"><?= e($en['name']) ?> <span class="text-slate-400 text-xs font-normal"><?= e($en['phone']) ?> <?= $en['email'] ? '&middot; ' . e($en['email']) : '' ?></span></p>
        <p class="text-sm text-slate-600 mt-1 max-w-xl"><?= nl2br(e($en['message'])) ?></p>
        <p class="text-xs text-slate-400 mt-1"><?= e(date('d M Y, h:i A', strtotime($en['created_at']))) ?></p>
      </div>
    <?php endforeach; ?>
    <?php if (!$enquiries): ?><div class="px-5 py-10 text-center text-slate-400 text-sm">No enquiries yet.</div><?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
