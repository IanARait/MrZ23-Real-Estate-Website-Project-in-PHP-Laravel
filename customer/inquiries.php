<?php
$page_title = 'My Inquiries - HomeHub';
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

    $stmt = $pdo->prepare("SELECT i.*, p.title as property_title, p.price, a.full_name as agent_name
        FROM inquiries i
        LEFT JOIN properties p ON i.property_id = p.id
        LEFT JOIN agents a ON p.agent_id = a.id
        WHERE i.customer_id = :uid ORDER BY i.created_at DESC");
    $stmt->execute(['uid' => $_SESSION['customer_id']]);
    $inquiries = $stmt->fetchAll();

} catch (PDOException $e) {
    $inquiries = [];
}
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0"><i class="fas fa-envelope text-success me-2"></i>My Inquiries</h3>
            <p class="text-muted mb-0"><?php echo count($inquiries); ?> total inquiries</p>
        </div>
    </div>

    <?php if (empty($inquiries)): ?>
    <div class="text-center py-5">
        <i class="fas fa-envelope fa-4x text-muted mb-3"></i>
        <h5 class="text-muted">No inquiries yet</h5>
        <p class="text-muted">Send an inquiry on a property to get started</p>
        <a href="<?php echo $base; ?>/customer/properties.php" class="btn btn-success">Browse Properties</a>
    </div>
    <?php else: ?>
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-success">
                    <tr>
                        <th>Property</th>
                        <th>Type</th>
                        <th>Agent</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inquiries as $inq): ?>
                    <tr>
                        <td>
                            <?php if ($inq['property_title']): ?>
                                <a href="<?php echo $base; ?>/customer/property-detail.php?id=<?php echo $inq['property_id']; ?>" class="text-success text-decoration-none fw-bold"><?php echo htmlspecialchars($inq['property_title']); ?></a>
                            <?php else: ?>
                                <span class="text-muted">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-secondary"><?php echo ucfirst($inq['type'] ?? 'inquiry'); ?></span></td>
                        <td><?php echo htmlspecialchars($inq['agent_name'] ?? 'N/A'); ?></td>
                        <td><small class="text-muted"><?php echo htmlspecialchars(substr($inq['message'], 0, 50)) . (strlen($inq['message'] ?? '') > 50 ? '...' : ''); ?></small></td>
                        <td>
                            <?php
                            $status_badges = ['new' => 'bg-warning text-dark', 'read' => 'bg-info', 'replied' => 'bg-success', 'closed' => 'bg-secondary'];
                            $badge = $status_badges[$inq['status']] ?? 'bg-secondary';
                            ?>
                            <span class="badge <?php echo $badge; ?>"><?php echo ucfirst($inq['status'] ?? 'new'); ?></span>
                        </td>
                        <td><small class="text-muted"><?php echo date('M d, Y', strtotime($inq['created_at'])); ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include_once __DIR__ . '/footer.php'; ?>