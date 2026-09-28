<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$id = (int) ($_GET['id'] ?? 0);
$goto = $_GET['goto'] ?? '';

$pdo->prepare('UPDATE notifications SET is_read = 1 WHERE notification_id = ?')->execute([$id]);

// Only ever redirect to a path inside this app - never follow an arbitrary external URL from the query string.
if ($goto !== '' && $goto[0] !== '/' && !str_contains($goto, '://')) {
    header('Location: ' . base_url($goto));
} else {
    header('Location: ' . base_url('modules/notifications/index.php'));
}
exit;
