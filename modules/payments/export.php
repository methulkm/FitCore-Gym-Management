<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$payments = $pdo->query("SELECT p.*, m.full_name, m.member_code, pl.plan_name FROM payments p
    JOIN members m ON m.member_id = p.member_id
    JOIN membership_plans pl ON pl.plan_id = p.plan_id
    ORDER BY p.payment_id")->fetchAll();

$rows = array_map(fn($p) => [
    $p['payment_code'], $p['member_code'], $p['full_name'], $p['plan_name'], $p['amount'],
    $p['transfer_date'], $p['reference_number'], $p['status'], $p['rejection_reason'],
], $payments);

csv_download('payments_' . date('Ymd') . '.csv',
    ['Payment Code', 'Member Code', 'Member Name', 'Plan', 'Amount (LKR)', 'Transfer Date', 'Reference', 'Status', 'Rejection Reason'], $rows);
