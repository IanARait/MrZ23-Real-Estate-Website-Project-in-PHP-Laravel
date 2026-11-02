<?php
require_once 'header.php';

$pdo = getConnection();

// Stats
$totalProps = $pdo->query("SELECT COUNT(*) FROM properties WHERE status = 1")->fetchColumn();
$forSale = $pdo->prepare("SELECT COUNT(*) FROM properties WHERE status_id = (SELECT id FROM property_statuses WHERE slug = 'for-sale') AND status = 1");
$forSale->execute();
$forSaleCount = $forSale->fetchColumn();

$forRent = $pdo->prepare("SELECT COUNT(*) FROM properties WHERE status_id = (SELECT id FROM property_statuses WHERE slug = 'for-rent') AND status = 1");
$forRent->execute();
$forRentCount = $forRent->fetchColumn();

$sold = $pdo->prepare("SELECT COUNT(*) FROM properties WHERE status_id = (SELECT id FROM property_statuses WHERE slug = 'sold') AND status = 1");
$sold->execute();
$soldCount = $sold->fetchColumn();

$totalAgents = $pdo->query("SELECT COUNT(*) FROM agents WHERE status = 1")->fetchColumn();
$totalInquiries = $pdo->query("SELECT COUNT(*) FROM inquiries")->fetchColumn();
$totalCustomers = $pdo->query("SELECT COUNT(*) FROM customers WHERE status = 1")->fetchColumn();
$totalRevenue = $pdo->query("SELECT COALESCE(SUM(price), 0) FROM properties WHERE status_id = (SELECT id FROM property_statuses WHERE slug = 'sold')")->fetchColumn();

// Recent inquiries
$stmt = $pdo->query("SELECT i.*, p.title AS property_title FROM inquiries i LEFT JOIN properties p ON i.property_id = p.id ORDER BY i.created_at DESC LIMIT 5");
$recentInquiries = $stmt->fetchAll();

// Recent properties
$stmt = $pdo->query("SELECT p.*, pt.name AS type_name, ps.name AS status_name, ps.color AS status_color FROM properties p LEFT JOIN property_types pt ON p.property_type_id = pt.id LEFT JOIN property_statuses ps ON p.status_id = ps.id WHERE p.status = 1 ORDER BY p.created_at DESC LIMIT 5");
$recentProperties = $stmt->fetchAll();

// Chart data: properties by status
$chartStatus = $pdo->query("SELECT ps.name, ps.color, COUNT(p.id) AS total FROM property_statuses ps LEFT JOIN properties p ON p.status_id = ps.id AND p.status = 1 GROUP BY ps.id");
$chartStatusData = $chartStatus->fetchAll();

// Properties by type
$chartType = $pdo->query("SELECT pt.name, COUNT(p.id) AS total FROM property_types pt LEFT JOIN properties p ON p.property_type_id = pt.id AND p.status = 1 GROUP BY pt.id");
$chartTypeData = $chartType->fetchAll();

// Monthly listings
$chartMonthly = $pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS total FROM properties WHERE status = 1 AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH) GROUP BY month ORDER BY month");
$chartMonthlyData = $chartMonthly->fetchAll();

// Inquiries over time
$chartInquiries = $pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m-%d') AS day, COUNT(*) AS total FROM inquiries WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY day ORDER BY day");
$chartInquiriesData = $chartInquiries->fetchAll();
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
    <small class="text-muted">Last updated: <?= date('M d, Y h:i A') ?></small>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Total Properties</h6>
                        <h2 class="mb-0"><?= $totalProps ?></h2>
                    </div>
                    <div class="stat-icon bg-primary"><i class="bi bi-building"></i></div>
                </div>
                <small class="text-success"><i class="bi bi-arrow-up"></i> <?= $forSaleCount ?> for sale</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">For Sale</h6>
                        <h2 class="mb-0"><?= $forSaleCount ?></h2>
                    </div>
                    <div class="stat-icon bg-info"><i class="bi bi-tag"></i></div>
                </div>
                <small class="text-muted"><?= $forRentCount ?> for rent</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Revenue (Sold)</h6>
                        <h2 class="mb-0"><?= formatPrice($totalRevenue) ?></h2>
                    </div>
                    <div class="stat-icon bg-success"><i class="bi bi-currency-dollar"></i></div>
                </div>
                <small class="text-muted"><?= $soldCount ?> properties sold</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Inquiries</h6>
                        <h2 class="mb-0"><?= $totalInquiries ?></h2>
                    </div>
                    <div class="stat-icon bg-warning"><i class="bi bi-envelope"></i></div>
                </div>
                <small class="text-muted"><?= $totalCustomers ?> customers</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Properties by Status</h6></div>
            <div class="card-body">
                <canvas id="statusChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Properties by Type</h6></div>
            <div class="card-body">
                <canvas id="typeChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-people me-2"></i>Quick Stats</h6></div>
            <div class="card-body">
                <div class="mb-2 d-flex justify-content-between"><span>Total Agents</span><strong><?= $totalAgents ?></strong></div>
                <div class="mb-2 d-flex justify-content-between"><span>Total Customers</span><strong><?= $totalCustomers ?></strong></div>
                <div class="mb-2 d-flex justify-content-between"><span>Sold Properties</span><strong><?= $soldCount ?></strong></div>
                <div class="d-flex justify-content-between"><span>Avg Price</span><strong><?= $totalProps ? formatPrice($totalRevenue / max($soldCount, 1)) : '$0' ?></strong></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Monthly Listings (12 months)</h6></div>
            <div class="card-body">
                <canvas id="monthlyChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="bi bi-activity me-2"></i>Inquiries (30 days)</h6></div>
            <div class="card-body">
                <canvas id="inquiriesChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-envelope me-2"></i>Recent Inquiries</h6>
                <a href="inquiries.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light"><tr><th>Property</th><th>Name</th><th>Type</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                            <?php if (empty($recentInquiries)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">No inquiries yet</td></tr>
                            <?php else: ?>
                            <?php foreach ($recentInquiries as $inq): ?>
                            <tr>
                                <td><?= truncate($inq['property_title'] ?? 'N/A', 30) ?></td>
                                <td><?= sanitize($inq['name']) ?></td>
                                <td><span class="badge bg-secondary"><?= sanitize($inq['type']) ?></span></td>
                                <td><?= getStatusBadge(ucfirst($inq['status']), $inq['status'] == 'new' ? '#2563eb' : ($inq['status'] == 'read' ? '#d97706' : ($inq['status'] == 'replied' ? '#059669' : '#6c757d'))) ?></td>
                                <td><?= timeAgo($inq['created_at']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-building me-2"></i>Recent Properties</h6>
                <a href="properties.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light"><tr><th>Title</th><th>Type</th><th>Price</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                            <?php if (empty($recentProperties)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">No properties yet</td></tr>
                            <?php else: ?>
                            <?php foreach ($recentProperties as $prop): ?>
                            <tr>
                                <td><?= truncate(sanitize($prop['title']), 35) ?></td>
                                <td><?= sanitize($prop['type_name'] ?? 'N/A') ?></td>
                                <td><?= formatPrice($prop['price']) ?></td>
                                <td><?= getStatusBadge(sanitize($prop['status_name']), $prop['status_color']) ?></td>
                                <td><?= formatDate($prop['created_at']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const statusLabels = <?= json_encode(array_column($chartStatusData, 'name')) ?>;
const statusData = <?= json_encode(array_column($chartStatusData, 'total')) ?>;
const statusColors = <?= json_encode(array_column($chartStatusData, 'color')) ?>;

new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: { labels: statusLabels, datasets: [{ data: statusData, backgroundColor: statusColors, borderWidth: 0 }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10 } } } }
});

const typeLabels = <?= json_encode(array_column($chartTypeData, 'name')) ?>;
const typeData = <?= json_encode(array_column($chartTypeData, 'total')) ?>;
new Chart(document.getElementById('typeChart'), {
    type: 'bar',
    data: { labels: typeLabels, datasets: [{ label: 'Properties', data: typeData, backgroundColor: '#059669', borderRadius: 4 }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});

const monthlyLabels = <?= json_encode(array_column($chartMonthlyData, 'month')) ?>;
const monthlyData = <?= json_encode(array_column($chartMonthlyData, 'total')) ?>;
new Chart(document.getElementById('monthlyChart'), {
    type: 'line',
    data: { labels: monthlyLabels, datasets: [{ label: 'Listings', data: monthlyData, borderColor: '#059669', backgroundColor: 'rgba(5,150,105,0.1)', fill: true, tension: 0.4 }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});

const inqLabels = <?= json_encode(array_column($chartInquiriesData, 'day')) ?>;
const inqData = <?= json_encode(array_column($chartInquiriesData, 'total')) ?>;
new Chart(document.getElementById('inquiriesChart'), {
    type: 'line',
    data: { labels: inqLabels, datasets: [{ label: 'Inquiries', data: inqData, borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,0.1)', fill: true, tension: 0.4 }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});
</script>

<?php require_once 'footer.php'; ?>
