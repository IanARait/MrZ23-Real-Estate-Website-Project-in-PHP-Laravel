<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to save properties.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$property_id = (int)($_POST['property_id'] ?? 0);
if ($property_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid property.']);
    exit;
}

try {
    $pdo = getConnection();

    $check = $pdo->prepare("SELECT id FROM saved_properties WHERE customer_id = :uid AND property_id = :pid");
    $check->execute(['uid' => $_SESSION['customer_id'], 'pid' => $property_id]);
    $existing = $check->fetch();

    if ($existing) {
        $del = $pdo->prepare("DELETE FROM saved_properties WHERE id = :id");
        $del->execute(['id' => $existing['id']]);
        echo json_encode(['success' => true, 'saved' => false, 'message' => 'Property removed from saved.']);
    } else {
        $ins = $pdo->prepare("INSERT INTO saved_properties (customer_id, property_id) VALUES (:uid, :pid)");
        $ins->execute(['uid' => $_SESSION['customer_id'], 'pid' => $property_id]);
        echo json_encode(['success' => true, 'saved' => true, 'message' => 'Property saved successfully!']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}