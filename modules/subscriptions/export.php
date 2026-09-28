<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$subs = $pdo->query("SELECT s.*, m.full_name, m.member_code, pl.plan_name FROM subscriptions s
    JOIN members m ON m.member_id = s.member_id
    JOIN membership_plans pl ON pl.plan_id = s.plan_id
    ORDER BY s.subscription_id")->fetchAll();

$rows = array_map(function ($s) {
    [, $label] = subscription_display_status($s['expiry_date'], $s['status']);
    return [$s['member_code'], $s['full_name'], $s['plan_name'], $s['start_date'], $s['expiry_date'], $label];
}, $subs);

csv_download('subscriptions_' . date('Ymd') . '.csv',
    ['Member Code', 'Member Name', 'Plan', 'Start Date', 'Expiry Date', 'Status'], $rows);
