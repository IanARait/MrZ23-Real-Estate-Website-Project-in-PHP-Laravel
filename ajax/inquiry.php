<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$message = trim($_POST['message'] ?? '');
$type = $_POST['type'] ?? 'inquiry';
$property_id = !empty($_POST['property_id']) ? (int)$_POST['property_id'] : null;

if (!in_array($type, ['inquiry', 'showing', 'offer'])) $type = 'inquiry';

if (empty($name) || empty($email) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

try {
    $pdo = getConnection();

    $stmt = $pdo->prepare("INSERT INTO inquiries (property_id, customer_id, name, email, phone, message, type, status) VALUES (:property_id, :customer_id, :name, :email, :phone, :message, :type, 'new')");
    $stmt->execute([
        'property_id' => $property_id,
        'customer_id' => $_SESSION['customer_id'] ?? null,
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'message' => $message,
        'type' => $type,
    ]);

    echo json_encode(['success' => true, 'message' => 'Your inquiry has been sent successfully. We will get back to you shortly.']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error. Please try again.']);
}