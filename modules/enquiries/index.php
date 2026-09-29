<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

if (isset($_GET['set_status'], $_GET['id'])) {
    csrf_verify('modules/enquiries/index.php');
    $status = $_GET['set_status'];
    if (in_array($status, ['new', 'reviewed', 'closed'], true)) {
        $pdo->prepare('UPDATE enquiries SET status = ? WHERE enquiry_id = ?')->execute([$status, (int) $_GET['id']]);
    }
    redirect_with_flash('modules/enquiries/index.php', 'success', 'Enquiry updated.');
}

$statusFilter = $_GET['status'] ?? 'all';
$sql = 'SELECT * FROM enquiries';
$params = [];
if ($statusFilter !== 'all') { $sql .= ' WHERE status = ?'; $params[] = $statusFilter; }
$sql .= ' ORDER BY enquiry_id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$enquiries = $stmt->fetchAll();

$pageTitle = 'Visitor Enquiries';
$pageSubtitle = 'Messages submitted through the public Contact page (FR-02, UC-18)';
$activeNav = 'enquiries';
require __DIR__ . '/../../includes/layout_start.php';
?>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
  <div class="flex items-center gap-2 px-5 py-4 border-b border-slate-100">
    <?php foreach (['all' => 'All', 'new' => 'New', 'reviewed' => 'Reviewed', 'closed' => 'Closed'] as $key => $label): ?>
      <a href="?status=<?= e($key) ?>" class="text-xs font-bold px-3 py-1.5 rounded-full <?= $statusFilter === $key ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
  </div>
  <div class="divide-y divide-slate-100">
    <?php foreach ($enquiries as $en): ?>
      <div class="px-5 py-4">
        <div class="flex items-center justify-between gap-3 flex-wrap">
          <div>
            <p class="font-semibold text-slate-900"><?= e($en['name']) ?> <span class="text-slate-400 text-xs font-normal"><?= e($en['phone']) ?> <?= $en['email'] ? '&middot; ' . e($en['email']) : '' ?></span></p>
            <p class="text-sm text-slate-600 mt-1 max-w-xl"><?= nl2br(e($en['message'])) ?></p>
            <p class="text-xs text-slate-400 mt-1"><?= e(date('d M Y, h:i A', strtotime($en['created_at']))) ?></p>
          </div>
          <div class="flex items-center gap-2 shrink-0">
            <?= status_badge(ucfirst($en['status']), match ($en['status']) { 'reviewed' => 'amber', 'closed' => 'slate', default => 'emerald' }) ?>
            <?php if ($en['status'] === 'new'): ?>
              <a href="<?= e(csrf_url('?set_status=reviewed&id=' . (int) $en['enquiry_id'])) ?>" class="text-xs font-bold text-amber-600 hover:text-amber-700">Mark Reviewed</a>
            <?php elseif ($en['status'] === 'reviewed'): ?>
              <a href="<?= e(csrf_url('?set_status=closed&id=' . (int) $en['enquiry_id'])) ?>" class="text-xs font-bold text-slate-500 hover:text-slate-700">Close</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (!$enquiries): ?><div class="px-5 py-10 text-center text-slate-400 text-sm">No enquiries in this view.</div><?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
