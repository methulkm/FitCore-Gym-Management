<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

if (isset($_GET['mark_all_read'])) {
    csrf_verify('modules/notifications/index.php');
    $pdo->exec("UPDATE notifications SET is_read = 1 WHERE target_role='admin' AND is_read = 0");
    redirect_with_flash('modules/notifications/index.php', 'success', 'All notifications marked as read.');
}

$filter = $_GET['filter'] ?? 'all';
$sql = "SELECT * FROM notifications WHERE target_role='admin'";
if ($filter === 'unread') $sql .= ' AND is_read = 0';
$sql .= ' ORDER BY notification_id DESC LIMIT 100';
$notifications = $pdo->query($sql)->fetchAll();

$unread = unread_notification_count($pdo);

$pageTitle = 'Notifications';
$pageSubtitle = 'All system alerts: new payments, leave requests, enquiries and equipment issues';
$activeNav = 'notifications';
$headerActions = $unread > 0 ? btn('Mark all as read', csrf_url(base_url('modules/notifications/index.php?mark_all_read=1')), 'ghost') : '';
require __DIR__ . '/../../includes/layout_start.php';
?>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
  <div class="flex items-center gap-2 px-5 py-4 border-b border-slate-100">
    <?php foreach (['all' => 'All', 'unread' => 'Unread'] as $key => $label): ?>
      <a href="?filter=<?= e($key) ?>" class="text-xs font-bold px-3 py-1.5 rounded-full <?= $filter === $key ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
  </div>
  <div class="divide-y divide-slate-100">
    <?php foreach ($notifications as $n):
      $style = notification_style($n['type']);
      $target = $n['link'] ? csrf_url(base_url('modules/notifications/read.php?id=' . $n['notification_id'] . '&goto=' . urlencode($n['link']))) : '#';
    ?>
      <a href="<?= e($target) ?>" class="flex items-start gap-4 px-5 py-4 hover:bg-slate-50 transition <?= $n['is_read'] ? 'opacity-60' : '' ?>">
        <span class="w-10 h-10 rounded-xl bg-<?= e($style['tone']) ?>-50 text-<?= e($style['tone']) ?>-600 flex items-center justify-center shrink-0"><?= sidebar_icon($style['icon']) ?></span>
        <span class="flex-1 min-w-0">
          <span class="block text-sm font-semibold text-slate-900"><?= e($n['message']) ?></span>
          <span class="block text-xs text-slate-400 mt-0.5"><?= e(time_ago($n['created_at'])) ?></span>
        </span>
        <?php if (!$n['is_read']): ?><span class="w-2 h-2 rounded-full bg-teal-500 mt-2 shrink-0"></span><?php endif; ?>
      </a>
    <?php endforeach; ?>
    <?php if (!$notifications): ?>
      <div class="px-5 py-10 text-center text-slate-400 text-sm">No notifications<?= $filter === 'unread' ? ' - you\'re all caught up' : '' ?>.</div>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
