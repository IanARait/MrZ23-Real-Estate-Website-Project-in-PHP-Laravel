<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['admin_id'])) { http_response_code(401); echo json_encode(['error' => 'Unauthorized']); exit; }

$id = $_GET['id'] ?? 0;
if (!$id) { echo json_encode(['error' => 'No ID']); exit; }

$pdo = getConnection();
$stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ?");
$stmt->execute([$id]);
$prop = $stmt->fetch();
if (!$prop) { echo json_encode(['error' => 'Not found']); exit; }

$stmt = $pdo->prepare("SELECT * FROM property_features WHERE property_id = ?");
$stmt->execute([$id]);
$prop['features'] = $stmt->fetchAll();

echo json_encode($prop);
