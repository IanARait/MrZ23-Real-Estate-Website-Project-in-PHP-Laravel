<?php
require_once 'header.php';

$pdo = getConnection();
$monthFilter = $_GET['month'] ?? date('Y-m');
$year = substr($monthFilter, 0, 4);
$month = substr($monthFilter, 5, 2);

// Sales by month
$salesByMonth = $pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS total, SUM(price) AS revenue FROM properties WHERE status_id = (SELECT id FROM property_statuses WHERE slug = 'sold') AND status = 1 GROUP BY month ORDER BY month DESC LIMIT 12")->fetchAll();

// Properties by type
$byType = $pdo->query("SELECT pt.name, COUNT(*) AS total FROM properties p JOIN property_types pt ON p.property_type_id = pt.id WHERE p.status = 1 GROUP BY pt.id ORDER BY total DESC")->fetchAll();

// Properties by city
$byCity = $pdo->query("SELECT city, COUNT(*) AS total FROM properties WHERE status = 1 AND city IS NOT NULL GROUP BY city ORDER BY total DESC LIMIT 10")->fetchAll();

// Top agents
$topAgents = $pdo->query("SELECT a.full_name, a.photo, COUNT(p.id) AS total, COALESCE(SUM(CASE WHEN p.status_id = (SELECT id FROM property_statuses WHERE slug = 'sold') THEN p.price ELSE 0 END), 0) AS revenue FROM agents a LEFT JOIN properties p ON a.id = p.agent_id AND p.status = 1 GROUP BY a.id ORDER BY total DESC LIMIT 10")->fetchAll();

// Monthly detail
$monthlyDetail = $pdo->prepare("SELECT p.*, pt.name AS type_name, a.full_name AS agent_name FROM properties p LEFT JOIN property_types pt ON p.property_type_id = pt.id LEFT JOIN agents a ON p.agent_id = a.id WHERE YEAR(p.created_at) = ? AND MONTH(p.created_at) = ? AND p.status = 1 ORDER BY p.created_at DESC");
$monthlyDetail->execute([$year, $month]);
$monthlyProps = $monthlyDetail->fetchAll();
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Reports</h1>
    <div>
        <a href="../reports/export-csv.php?month=<?= $monthFilter ?>" class="btn btn-sm btn-success"><i class="bi bi-download"></i> Export CSV</a>
        <a href="../reports/print.php?month=<?= $monthFilter ?>" class="btn btn-sm btn-secondary" target="_blank"><i class="bi bi-printer"></i> Print</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Sales by Month</h6></div>
            <div class="card-body">
                <canvas id="salesChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Properties by Type</h6></div>
            <div class="card-body">
                <canvas id="typeChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Properties by City</h6></div>
            <div class="card-body">
                <canvas id="cityChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-trophy me-2"></i>Top Agents</h6></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>#</th><th>Agent</th><th>Properties</th><th>Revenue</th></tr></thead>
                    <tbody>
                        <?php $i = 1; foreach ($topAgents as $a): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td>
                                <?php if ($a['photo']): ?>
                                <img src="../uploads/agents/<?= sanitize($a['photo']) ?>" style="width:30px;height:30px;object-fit:cover;border-radius:50%;" class="me-1">
                                <?php endif; ?>
                                <?= sanitize($a['full_name']) ?>
                            </td>
                            <td><?= $a['total'] ?></td>
                            <td><?= formatPrice($a['revenue']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-calendar me-2"></i>Monthly Detail</h6>
        <input type="month" class="form-control form-control-sm" style="width:200px" value="<?= $monthFilter ?>" onchange="window.location.href='?month='+this.value">
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Title</th><th>Type</th><th>Price</th><th>Agent</th><th>Date</th></tr></thead>
            <tbody>
                <?php foreach ($monthlyProps as $p): ?>
                <tr>
                    <td><?= truncate(sanitize($p['title']), 40) ?></td>
                    <td><?= sanitize($p['type_name'] ?? 'N/A') ?></td>
                    <td><?= formatPrice($p['price']) ?></td>
                    <td><?= sanitize($p['agent_name'] ?? 'N/A') ?></td>
                    <td><?= formatDate($p['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($monthlyProps)): ?>
                <tr><td colspan="5" class="text-center text-muted py-3">No properties for this month</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
new Chart(document.getElementById('salesChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_reverse(array_column($salesByMonth, 'month'))) ?>,
        datasets: [
            { label: 'Sold', data: <?= json_encode(array_reverse(array_column($salesByMonth, 'total'))) ?>, backgroundColor: '#059669', borderRadius: 4 },
            { label: 'Revenue ($)', data: <?= json_encode(array_reverse(array_column($salesByMonth, 'revenue'))) ?>, backgroundColor: '#2563eb', borderRadius: 4, yAxisID: 'y1' }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } }, y1: { position: 'right', beginAtZero: true, grid: { drawOnChartArea: false } } }
    }
});

new Chart(document.getElementById('typeChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_column($byType, 'name')) ?>,
        datasets: [{ data: <?= json_encode(array_column($byType, 'total')) ?>, backgroundColor: ['#059669','#2563eb','#d97706','#dc2626','#7c3aed','#0891b2'], borderWidth: 0 }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } } }
});

new Chart(document.getElementById('cityChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($byCity, 'city')) ?>,
        datasets: [{ label: 'Properties', data: <?= json_encode(array_column($byCity, 'total')) ?>, backgroundColor: '#059669', borderRadius: 4 }]
    },
    options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});
</script>
<?php require_once 'footer.php'; ?>
