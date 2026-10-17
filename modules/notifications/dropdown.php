<?php
// Returns just the notification-list HTML fragment (no layout) - fetched by the topbar bell via JS
// each time it's opened, so the list is always fresh even after an AJAX in-app navigation.
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_role(['admin']);

$notifications = recent_notifications($pdo, 8);
$unread = unread_notification_count($pdo);
?>
<div data-unread-count="<?= $unread ?>">
  <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
    <span class="text-sm font-extrabold text-slate-900">Notifications</span>
    <?php if ($unread > 0): ?>
      <a href="<?= e(csrf_url(base_url('modules/notifications/index.php?mark_all_read=1'))) ?>" class="text-xs font-bold text-teal-600 hover:text-teal-700">Mark all read</a>
    <?php endif; ?>
  </div>
  <div class="max-h-80 overflow-y-auto divide-y divide-slate-100">
    <?php foreach ($notifications as $n):
      $style = notification_style($n['type']);
      $target = $n['link'] ? csrf_url(base_url('modules/notifications/read.php?id=' . $n['notification_id'] . '&goto=' . urlencode($n['link']))) : base_url('modules/notifications/index.php');
    ?>
      <a href="<?= e($target) ?>" class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 transition <?= $n['is_read'] ? 'opacity-60' : '' ?>">
        <span class="w-8 h-8 rounded-lg bg-<?= e($style['tone']) ?>-50 text-<?= e($style['tone']) ?>-600 flex items-center justify-center shrink-0"><?= sidebar_icon($style['icon']) ?></span>
        <span class="flex-1 min-w-0">
          <span class="block text-xs font-semibold text-slate-800 leading-snug"><?= e($n['message']) ?></span>
          <span class="block text-[11px] text-slate-400 mt-0.5"><?= e(time_ago($n['created_at'])) ?></span>
        </span>
        <?php if (!$n['is_read']): ?><span class="w-1.5 h-1.5 rounded-full bg-teal-500 mt-1.5 shrink-0"></span><?php endif; ?>
      </a>
    <?php endforeach; ?>
    <?php if (!$notifications): ?>
      <div class="px-4 py-8 text-center text-slate-400 text-xs">No notifications yet.</div>
    <?php endif; ?>
  </div>
  <a href="<?= e(base_url('modules/notifications/index.php')) ?>" class="block text-center text-xs font-bold text-teal-600 hover:text-teal-700 px-4 py-3 border-t border-slate-100">View all</a>
</div>
