<?php
require_once 'header.php';

$pdo = getConnection();
$msg = ''; $error = '';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM property_statuses WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $msg = 'Status deleted.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $id = $_POST['id'] ?? null;
    $name = sanitize($_POST['name']);
    $slug = sanitize($_POST['slug']) ?: generateSlug($name);
    $color = sanitize($_POST['color'] ?? '#6c757d');
    $status = isset($_POST['status']) ? 1 : 0;

    if ($id) {
        $stmt = $pdo->prepare("UPDATE property_statuses SET name=?, slug=?, color=?, status=? WHERE id=?");
        $stmt->execute([$name, $slug, $color, $status, $id]);
        $msg = 'Status updated.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO property_statuses (name, slug, color, status) VALUES (?,?,?,?)");
        $stmt->execute([$name, $slug, $color, $status]);
        $msg = 'Status created.';
    }
    redirect('property-statuses.php');
}

$statuses_list = $pdo->query("SELECT * FROM property_statuses ORDER BY name")->fetchAll();
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Property Statuses</h1>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#statusModal"><i class="bi bi-plus-lg"></i> Add Status</button>
</div>
<?php if ($msg): ?><div class="alert alert-success py-2"><?= $msg ?></div><?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Name</th><th>Slug</th><th>Color</th><th>Preview</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($statuses_list as $s): ?>
                <tr>
                    <td><strong><?= sanitize($s['name']) ?></strong></td>
                    <td><code><?= sanitize($s['slug']) ?></code></td>
                    <td><span style="display:inline-block;width:24px;height:24px;background:<?= sanitize($s['color']) ?>;border-radius:4px;"></span> <?= sanitize($s['color']) ?></td>
                    <td><?= getStatusBadge(sanitize($s['name']), $s['color']) ?></td>
                    <td><?= $s['status'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary edit-status" data-id="<?= $s['id'] ?>" data-name="<?= sanitize($s['name']) ?>" data-slug="<?= sanitize($s['slug']) ?>" data-color="<?= sanitize($s['color']) ?>" data-status="<?= $s['status'] ?>"><i class="bi bi-pencil"></i></button>
                        <a href="?delete=<?= $s['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete?')"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="post">
            <div class="modal-header"><h5 class="modal-title" id="statusModalTitle">Add Status</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="id" id="status_id">
                <input type="hidden" name="save" value="1">
                <div class="mb-3">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" id="status_name" class="form-control" required onkeyup="document.getElementById('status_slug').value=generateSlug(this.value)">
                </div>
                <div class="mb-3">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" id="status_slug" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Color</label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="color" name="color" id="status_color" class="form-control form-control-color" value="#6c757d">
                        <span id="color_hex">#6c757d</span>
                    </div>
                </div>
                <div class="form-check">
                    <input type="checkbox" name="status" id="status_active" class="form-check-input" value="1" checked>
                    <label class="form-check-label">Active</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div></div>
</div>

<script>
document.getElementById('status_color').addEventListener('input', function() {
    document.getElementById('color_hex').textContent = this.value;
});
document.querySelectorAll('.edit-status').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('statusModalTitle').textContent = 'Edit Status';
        document.getElementById('status_id').value = this.dataset.id;
        document.getElementById('status_name').value = this.dataset.name;
        document.getElementById('status_slug').value = this.dataset.slug;
        document.getElementById('status_color').value = this.dataset.color;
        document.getElementById('color_hex').textContent = this.dataset.color;
        document.getElementById('status_active').checked = this.dataset.status == '1';
        new bootstrap.Modal(document.getElementById('statusModal')).show();
    });
});
</script>
<?php require_once 'footer.php'; ?>
