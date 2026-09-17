<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$id = (int) ($_GET['id'] ?? 0);
$pdo->prepare("UPDATE classes SET status = 'cancelled' WHERE class_id = ?")->execute([$id]);
redirect_with_flash('modules/classes/index.php', 'success', 'Class cancelled.');
