<?php
$page_title = 'Saved Properties - HomeHub';
$hide_search = true;
require_once __DIR__ . '/../includes/functions.php';
include_once __DIR__ . '/header.php';

$base = 'http://' . $_SERVER['HTTP_HOST'] . '/E_COMMERCE/real-estate';

if (!isset($_SESSION['customer_id'])) {
    header('Location: ' . $base . '/customer/login.php');
    exit;
}

try {
    $pdo = getConnection();

    $stmt = $pdo->prepare("SELECT p.*, ps.name AS status_name, ps.color AS status_color, sp.created_at as saved_date
        FROM saved_properties sp
        JOIN properties p ON sp.property_id = p.id
        LEFT JOIN property_statuses ps ON p.status_id = ps.id
        WHERE sp.customer_id = :uid ORDER BY sp.created_at DESC");
    $stmt->execute(['uid' => $_SESSION['customer_id']]);
    $saved_properties = $stmt->fetchAll();

} catch (PDOException $e) {
    $saved_properties = [];
}
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0"><i class="fas fa-heart text-success me-2"></i>Saved Properties</h3>
            <p class="text-muted mb-0"><?php echo count($saved_properties); ?> saved properties</p>
        </div>
        <a href="<?php echo $base; ?>/customer/properties.php" class="btn btn-outline-success"><i class="fas fa-search me-1"></i>Browse Properties</a>
    </div>

    <?php if (empty($saved_properties)): ?>
    <div class="text-center py-5">
        <i class="fas fa-heart fa-4x text-muted mb-3"></i>
        <h5 class="text-muted">No saved properties yet</h5>
        <p class="text-muted">Start browsing and save properties you like</p>
        <a href="<?php echo $base; ?>/customer/properties.php" class="btn btn-success">Browse Properties</a>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($saved_properties as $prop): ?>
        <div class="col-lg-4 col-md-6">
            <div class="property-card card border-0 shadow-sm h-100">
                <div class="position-relative overflow-hidden">
                    <?php echo customerPropImageTag($prop, '200px'); ?>
                    <button class="btn btn-sm bg-white text-danger position-absolute top-0 end-0 m-2 border-0 shadow-sm save-property-btn" data-property="<?php echo $prop['id']; ?>"><i class="fas fa-trash"></i></button>
                    <span class="badge position-absolute top-0 start-0 m-2" style="background-color:<?php echo htmlspecialchars($prop['status_color'] ?? '#6c757d'); ?>;"><?php echo htmlspecialchars($prop['status_name']); ?></span>
                </div>
                <div class="card-body">
                    <h5 class="text-success fw-bold mb-1">$<?php echo number_format($prop['price']); ?></h5>
                    <h6 class="card-title"><?php echo htmlspecialchars($prop['title']); ?></h6>
                    <p class="card-text text-muted small"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars(customerPropAddress($prop)); ?></p>
                    <div class="d-flex justify-content-between text-muted small border-top pt-2">
                        <span><i class="fas fa-bed text-success me-1"></i><?php echo $prop['bedrooms']; ?> Beds</span>
                        <span><i class="fas fa-bath text-success me-1"></i><?php echo $prop['bathrooms']; ?> Baths</span>
                        <span><i class="fas fa-vector-square text-success me-1"></i><?php echo number_format($prop['sqft']); ?> sqft</span>
                    </div>
                    <small class="text-muted mt-2 d-block">Saved on <?php echo date('M d, Y', strtotime($prop['saved_date'])); ?></small>
                </div>
                <a href="<?php echo $base; ?>/customer/property-detail.php?id=<?php echo $prop['id']; ?>" class="stretched-link"></a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php include_once __DIR__ . '/footer.php'; ?>