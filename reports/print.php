<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['admin_id'])) { die('Unauthorized'); }

$pdo = getConnection();
$month = $_GET['month'] ?? date('Y-m');
$year = substr($month, 0, 4);
$mon = substr($month, 5, 2);

$stmt = $pdo->prepare("SELECT p.*, pt.name AS type_name, ps.name AS status_name, a.full_name AS agent_name FROM properties p LEFT JOIN property_types pt ON p.property_type_id = pt.id LEFT JOIN property_statuses ps ON p.status_id = ps.id LEFT JOIN agents a ON p.agent_id = a.id WHERE YEAR(p.created_at) = ? AND MONTH(p.created_at) = ? AND p.status = 1 ORDER BY p.created_at DESC");
$stmt->execute([$year, $mon]);
$props = $stmt->fetchAll();

$totalValue = array_sum(array_column($props, 'price'));
$site_name = getSetting('site_name') ?: 'Real Estate';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report - <?= $month ?> - <?= sanitize($site_name) ?></title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; padding: 20px; color: #333; }
        h1 { font-size: 20px; margin-bottom: 5px; }
        .subtitle { color: #666; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f5f5f5; font-weight: 600; }
        .total { font-size: 16px; font-weight: 700; text-align: right; margin-top: 10px; }
        .footer { margin-top: 30px; text-align: center; color: #999; font-size: 11px; border-top: 1px solid #ddd; padding-top: 10px; }
        @media print { body { padding: 0; } .no-print { display: none; } }
    </style>
</head>
<body>
    <h1><?= sanitize($site_name) ?> - Monthly Report</h1>
    <div class="subtitle"><?= date('F Y', strtotime($month)) ?> &bull; <?= count($props) ?> properties</div>
    <button class="no-print" onclick="window.print()" style="margin-bottom:15px;padding:8px 16px;cursor:pointer;">Print</button>

    <table>
        <thead>
            <tr><th>Title</th><th>Type</th><th>Status</th><th>Price</th><th>Beds</th><th>Baths</th><th>City</th><th>Agent</th><th>Date</th></tr>
        </thead>
        <tbody>
            <?php foreach ($props as $p): ?>
            <tr>
                <td><?= sanitize($p['title']) ?></td>
                <td><?= sanitize($p['type_name'] ?? 'N/A') ?></td>
                <td><?= sanitize($p['status_name']) ?></td>
                <td><?= formatPrice($p['price']) ?></td>
                <td><?= $p['bedrooms'] ?></td>
                <td><?= $p['bathrooms'] ?></td>
                <td><?= sanitize($p['city']) ?></td>
                <td><?= sanitize($p['agent_name'] ?? 'N/A') ?></td>
                <td><?= formatDate($p['created_at'], 'M d, Y') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="total">Total Value: <?= formatPrice($totalValue) ?></div>
    <div class="footer">Generated on <?= date('F d, Y \a\t h:i A') ?> &bull; <?= sanitize($site_name) ?></div>
</body>
</html>
