<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_role(['admin']);

$totalRevenue = (float) $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='approved'")->fetchColumn();
$totalMembers = (int) $pdo->query("SELECT COUNT(*) FROM members")->fetchColumn();
$activeMembers = (int) $pdo->query("SELECT COUNT(DISTINCT member_id) FROM subscriptions WHERE status='active' AND expiry_date >= CURDATE()")->fetchColumn();
$todayCheckins = (int) $pdo->query("SELECT COUNT(*) FROM member_attendance WHERE attendance_date = CURDATE()")->fetchColumn();
$pendingSlips = (int) $pdo->query("SELECT COUNT(*) FROM payments WHERE status='pending'")->fetchColumn();
$equipTotal = (int) $pdo->query("SELECT COUNT(*) FROM equipment")->fetchColumn();
$equipReady = (int) $pdo->query("SELECT COUNT(*) FROM equipment WHERE status='available'")->fetchColumn();
$equipPct = $equipTotal > 0 ? round($equipReady / $equipTotal * 100, 1) : 0;
$monthlyGrowth = (int) $pdo->query("SELECT COUNT(*) FROM members WHERE join_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')")->fetchColumn();



// Last 6 months revenue + new-member trend for the chart.
$months = [];
$revenueSeries = [];
$growthSeries = [];
for ($i = 5; $i >= 0; $i--) {
    $label = date('M', strtotime("-{$i} months"));
    $monthStart = date('Y-m-01', strtotime("-{$i} months"));
    $monthEnd = date('Y-m-t', strtotime("-{$i} months"));
    $rev = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='approved' AND transfer_date BETWEEN ? AND ?");
    $rev->execute([$monthStart, $monthEnd]);
    $grow = $pdo->prepare('SELECT COUNT(*) FROM members WHERE join_date BETWEEN ? AND ?');
    $grow->execute([$monthStart, $monthEnd]);
    $months[] = $label;
    $revenueSeries[] = (float) $rev->fetchColumn();
    $growthSeries[] = (int) $grow->fetchColumn();
}

$pageTitle = 'Admin Dashboard & Reports';
$pageSubtitle = 'Centralized gym metrics, revenue trajectory and operational datasets (FR-16, FR-17, FR-20, FR-21, BR-20)';
$activeNav = 'dashboard';
$ownerTag = 'Janith';
require __DIR__ . '/../../includes/layout_start.php';
?>

<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
  <?= kpi_card('Total Revenue', 'LKR ' . number_format($totalRevenue)) ?>
  <?= kpi_card('Active Members', (string) $activeMembers, $totalMembers . ' total registered') ?>
  <?= kpi_card('Today Check-ins', (string) $todayCheckins) ?>
  <?= kpi_card('Pending Slips', (string) $pendingSlips, $pendingSlips > 0 ? 'Needs verification' : 'All clear', $pendingSlips > 0 ? 'text-amber-600' : 'text-emerald-600') ?>
  <?= kpi_card('Equipment Ready', $equipPct . '%', ($equipTotal - $equipReady) . ' under repair') ?>
  <?= kpi_card('Monthly Growth', '+' . $monthlyGrowth . ' New', '', 'text-teal-600') ?>
</div>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
  <h3 class="font-extrabold mb-1">Membership Growth &amp; Revenue Overview</h3>
  <p class="text-xs text-slate-400 mb-4">Monthly subscription collections and new enrollments (BR-20: simple operational report, no forecasting)</p>
  <canvas id="revenueChart" height="90"></canvas>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.5.1/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('revenueChart'), {
  type: 'line',
  data: {
    labels: <?= json_encode($months) ?>,
    datasets: [
      { label: 'Revenue (LKR)', data: <?= json_encode($revenueSeries) ?>, borderColor: '#14B8A6', backgroundColor: 'rgba(20,184,166,0.1)', tension: 0.35, fill: true, yAxisID: 'y' },
      { label: 'New Members', data: <?= json_encode($growthSeries) ?>, borderColor: '#6366F1', backgroundColor: 'transparent', borderDash: [6,6], tension: 0.35, yAxisID: 'y1' }
    ]
  },
  options: {
    responsive: true,
    interaction: { mode: 'index', intersect: false },
    scales: {
      y: { position: 'left', ticks: { callback: v => 'LKR ' + v } },
      y1: { position: 'right', grid: { drawOnChartArea: false } }
    }
  }
});
</script>

<?php require __DIR__ . '/../../includes/layout_end.php'; ?>
