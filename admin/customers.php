<?php
require_once 'header.php';

$pdo = getConnection();

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $msg = 'Customer deleted.';
}

$customers = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM saved_properties WHERE customer_id = c.id) AS saved_count, (SELECT COUNT(*) FROM inquiries WHERE customer_id = c.id) AS inquiry_count FROM customers c ORDER BY c.created_at DESC")->fetchAll();
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Customers</h1>
</div>
<?php if (isset($msg)): ?><div class="alert alert-success py-2"><?= $msg ?></div><?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table id="customersTable" class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Name</th><th>Email</th><th>Phone</th><th>Saved</th><th>Inquiries</th><th>Status</th><th>Registered</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($customers as $c): ?>
                <tr>
                    <td><strong><?= sanitize($c['first_name'] . ' ' . $c['last_name']) ?></strong></td>
                    <td><?= sanitize($c['email']) ?></td>
                    <td><?= sanitize($c['phone'] ?: '-') ?></td>
                    <td><span class="badge bg-primary"><?= $c['saved_count'] ?></span></td>
                    <td><span class="badge bg-info"><?= $c['inquiry_count'] ?></span></td>
                    <td><?= $c['status'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                    <td><?= formatDate($c['created_at']) ?></td>
                    <td>
                        <a href="?delete=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete customer?')"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once 'footer.php'; ?>
