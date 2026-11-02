<?php
require_once 'header.php';

$pdo = getConnection();
$msg = ''; $error = '';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM property_types WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $msg = 'Property type deleted.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $id = $_POST['id'] ?? null;
    $name = sanitize($_POST['name']);
    $slug = sanitize($_POST['slug']) ?: generateSlug($name);
    $icon = sanitize($_POST['icon'] ?? '');
    $status = isset($_POST['status']) ? 1 : 0;

    if ($id) {
        $stmt = $pdo->prepare("UPDATE property_types SET name=?, slug=?, icon=?, status=? WHERE id=?");
        $stmt->execute([$name, $slug, $icon, $status, $id]);
        $msg = 'Property type updated.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO property_types (name, slug, icon, status) VALUES (?,?,?,?)");
        $stmt->execute([$name, $slug, $icon, $status]);
        $msg = 'Property type created.';
    }
    redirect('property-types.php');
}

$types = $pdo->query("SELECT * FROM property_types ORDER BY name")->fetchAll();
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Property Types</h1>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#typeModal"><i class="bi bi-plus-lg"></i> Add Type</button>
</div>
<?php if ($msg): ?><div class="alert alert-success py-2"><?= $msg ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger py-2"><?= $error ?></div><?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Name</th><th>Slug</th><th>Icon</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($types as $t): ?>
                <tr>
                    <td><strong><?= sanitize($t['name']) ?></strong></td>
                    <td><code><?= sanitize($t['slug']) ?></code></td>
                    <td><?= $t['icon'] ? '<i class="bi bi-' . sanitize($t['icon']) . '"></i> ' . sanitize($t['icon']) : '-' ?></td>
                    <td><?= $t['status'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary edit-type" data-id="<?= $t['id'] ?>" data-name="<?= sanitize($t['name']) ?>" data-slug="<?= sanitize($t['slug']) ?>" data-icon="<?= sanitize($t['icon']) ?>" data-status="<?= $t['status'] ?>"><i class="bi bi-pencil"></i></button>
                        <a href="?delete=<?= $t['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete?')"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="typeModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="post">
            <div class="modal-header"><h5 class="modal-title" id="typeModalTitle">Add Property Type</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="id" id="type_id">
                <input type="hidden" name="save" value="1">
                <div class="mb-3">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" id="type_name" class="form-control" required onkeyup="document.getElementById('type_slug').value=generateSlug(this.value)">
                </div>
                <div class="mb-3">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" id="type_slug" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Icon (Bootstrap icon name)</label>
                    <input type="text" name="icon" id="type_icon" class="form-control" placeholder="e.g. house, building, apartment">
                </div>
                <div class="form-check">
                    <input type="checkbox" name="status" id="type_status" class="form-check-input" value="1" checked>
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
document.querySelectorAll('.edit-type').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('typeModalTitle').textContent = 'Edit Property Type';
        document.getElementById('type_id').value = this.dataset.id;
        document.getElementById('type_name').value = this.dataset.name;
        document.getElementById('type_slug').value = this.dataset.slug;
        document.getElementById('type_icon').value = this.dataset.icon;
        document.getElementById('type_status').checked = this.dataset.status == '1';
        new bootstrap.Modal(document.getElementById('typeModal')).show();
    });
});
document.getElementById('typeModal').addEventListener('hidden.bs.modal', function() {
    if (!document.getElementById('type_id').value) document.getElementById('typeModalTitle').textContent = 'Add Property Type';
});
</script>
<?php require_once 'footer.php'; ?>
