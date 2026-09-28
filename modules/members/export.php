<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$members = $pdo->query('SELECT * FROM members ORDER BY member_id')->fetchAll();
$rows = array_map(fn($m) => [
    $m['member_code'], $m['full_name'], $m['nic_passport'], $m['phone'], $m['email'],
    $m['join_date'], $m['account_status'],
], $members);

csv_download('members_' . date('Ymd') . '.csv',
    ['Member Code', 'Full Name', 'NIC/Passport', 'Phone', 'Email', 'Join Date', 'Status'], $rows);
