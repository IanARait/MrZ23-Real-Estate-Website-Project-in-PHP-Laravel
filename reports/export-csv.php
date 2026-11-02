<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['admin_id'])) { die('Unauthorized'); }

$pdo = getConnection();
$month = $_GET['month'] ?? date('Y-m');
$year = substr($month, 0, 4);
$mon = substr($month, 5, 2);

$stmt = $pdo->prepare("SELECT p.title, p.price, p.bedrooms, p.bathrooms, p.sqft, p.city, p.state, p.address_line1, pt.name AS type_name, ps.name AS status_name, a.full_name AS agent_name, p.created_at FROM properties p LEFT JOIN property_types pt ON p.property_type_id = pt.id LEFT JOIN property_statuses ps ON p.status_id = ps.id LEFT JOIN agents a ON p.agent_id = a.id WHERE YEAR(p.created_at) = ? AND MONTH(p.created_at) = ? AND p.status = 1 ORDER BY p.created_at DESC");
$stmt->execute([$year, $mon]);
$rows = $stmt->fetchAll();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="properties_' . $month . '.csv"');
$output = fopen('php://output', 'w');
fputcsv($output, ['Title', 'Price', 'Bedrooms', 'Bathrooms', 'SqFt', 'City', 'State', 'Address', 'Type', 'Status', 'Agent', 'Date']);
foreach ($rows as $r) {
    fputcsv($output, [
        $r['title'],
        $r['price'],
        $r['bedrooms'],
        $r['bathrooms'],
        $r['sqft'],
        $r['city'],
        $r['state'],
        $r['address_line1'],
        $r['type_name'],
        $r['status_name'],
        $r['agent_name'],
        $r['created_at'],
    ]);
}
fclose($output);
