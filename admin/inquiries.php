<?php
require_once 'header.php';

$pdo = getConnection();
$msg = ''; $error = '';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM inquiries WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $msg = 'Inquiry deleted.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $stmt = $pdo->prepare("UPDATE inquiries SET status = ? WHERE id = ?");
        $stmt->execute([$_POST['status'], $_POST['inquiry_id']]);
        $msg = 'Status updated.';
    }
    redirect('inquiries.php');
}

$inquiries = $pdo->query("SELECT i.*, p.title AS property_title, p.id AS pid FROM inquiries i LEFT JOIN properties p ON i.property_id = p.id ORDER BY i.created_at DESC")->fetchAll();
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Inquiries</h1>
</div>
<?php if ($msg): ?><div class="alert alert-success py-2"><?= $msg ?></div><?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table id="inquiriesTable" class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Property</th><th>Name</th><th>Email</th><th>Type</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($inquiries as $inq): ?>
                <tr class="<?= $inq['status'] == 'new' ? 'table-primary' : '' ?>">
                    <td><?= truncate(sanitize($inq['property_title'] ?? 'N/A'), 40) ?></td>
                    <td><?= sanitize($inq['name']) ?></td>
                    <td><?= sanitize($inq['email']) ?></td>
                    <td><span class="badge bg-secondary"><?= sanitize($inq['type']) ?></span></td>
                    <td>
                        <form method="post" class="d-inline status-form">
                            <input type="hidden" name="inquiry_id" value="<?= $inq['id'] ?>">
                            <input type="hidden" name="update_status" value="1">
                            <select name="status" class="form-select form-select-sm status-select" style="width:auto;display:inline-block;" onchange="this.form.submit()">
                                <option value="new" <?= $inq['status'] == 'new' ? 'selected' : '' ?>>New</option>
                                <option value="read" <?= $inq['status'] == 'read' ? 'selected' : '' ?>>Read</option>
                                <option value="replied" <?= $inq['status'] == 'replied' ? 'selected' : '' ?>>Replied</option>
                                <option value="closed" <?= $inq['status'] == 'closed' ? 'selected' : '' ?>>Closed</option>
                            </select>
                        </form>
                    </td>
                    <td><?= timeAgo($inq['created_at']) ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-info view-inquiry" data-id="<?= $inq['id'] ?>" data-property="<?= sanitize($inq['property_title'] ?? 'N/A') ?>" data-name="<?= sanitize($inq['name']) ?>" data-email="<?= sanitize($inq['email']) ?>" data-phone="<?= sanitize($inq['phone']) ?>" data-message="<?= sanitize($inq['message']) ?>" data-type="<?= sanitize($inq['type']) ?>" data-date="<?= formatDate($inq['created_at'], 'M d, Y h:i A') ?>"><i class="bi bi-eye"></i></button>
                        <a href="?delete=<?= $inq['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete?')"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="inquiryModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Inquiry Detail</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Property</dt><dd class="col-sm-9" id="inq_property"></dd>
                <dt class="col-sm-3">Name</dt><dd class="col-sm-9" id="inq_name"></dd>
                <dt class="col-sm-3">Email</dt><dd class="col-sm-9" id="inq_email"></dd>
                <dt class="col-sm-3">Phone</dt><dd class="col-sm-9" id="inq_phone"></dd>
                <dt class="col-sm-3">Type</dt><dd class="col-sm-9" id="inq_type"></dd>
                <dt class="col-sm-3">Date</dt><dd class="col-sm-9" id="inq_date"></dd>
                <dt class="col-sm-3">Message</dt><dd class="col-sm-9" id="inq_message"></dd>
            </dl>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <a href="#" id="inq_reply" class="btn btn-primary" target="_blank"><i class="bi bi-reply"></i> Reply</a>
        </div>
    </div></div>
</div>

<script>
document.querySelectorAll('.view-inquiry').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('inq_property').textContent = this.dataset.property;
        document.getElementById('inq_name').textContent = this.dataset.name;
        document.getElementById('inq_email').textContent = this.dataset.email;
        document.getElementById('inq_phone').textContent = this.dataset.phone || '-';
        document.getElementById('inq_type').textContent = this.dataset.type;
        document.getElementById('inq_date').textContent = this.dataset.date;
        document.getElementById('inq_message').textContent = this.dataset.message;
        document.getElementById('inq_reply').href = 'mailto:' + this.dataset.email;
        new bootstrap.Modal(document.getElementById('inquiryModal')).show();
    });
});
</script>
<?php require_once 'footer.php'; ?>
