<?php
require_once 'header.php';

$pdo = getConnection();
$msg = ''; $error = '';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM features WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $msg = 'Feature deleted.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $id = $_POST['id'] ?? null;
    $name = sanitize($_POST['name']);
    $icon = sanitize($_POST['icon'] ?? '');
    $category = sanitize($_POST['category'] ?? 'General');
    $status = isset($_POST['status']) ? 1 : 0;

    if ($id) {
        $stmt = $pdo->prepare("UPDATE features SET name=?, icon=?, category=?, status=? WHERE id=?");
        $stmt->execute([$name, $icon, $category, $status, $id]);
        $msg = 'Feature updated.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO features (name, icon, category, status) VALUES (?,?,?,?)");
        $stmt->execute([$name, $icon, $category, $status]);
        $msg = 'Feature created.';
    }
    redirect('features.php');
}

$features_list = $pdo->query("SELECT * FROM features ORDER BY category, name")->fetchAll();
$categories = $pdo->query("SELECT DISTINCT category FROM features WHERE category IS NOT NULL ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Features</h1>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#featureModal"><i class="bi bi-plus-lg"></i> Add Feature</button>
</div>
<?php if ($msg): ?><div class="alert alert-success py-2"><?= $msg ?></div><?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Name</th><th>Icon</th><th>Category</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($features_list as $f): ?>
                <tr>
                    <td><strong><?= sanitize($f['name']) ?></strong></td>
                    <td><?= $f['icon'] ? '<i class="bi bi-' . sanitize($f['icon']) . '"></i>' : '-' ?></td>
                    <td><span class="badge bg-light text-dark"><?= sanitize($f['category'] ?? 'General') ?></span></td>
                    <td><?= $f['status'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary edit-feature" data-id="<?= $f['id'] ?>" data-name="<?= sanitize($f['name']) ?>" data-icon="<?= sanitize($f['icon']) ?>" data-category="<?= sanitize($f['category'] ?? '') ?>" data-status="<?= $f['status'] ?>"><i class="bi bi-pencil"></i></button>
                        <a href="?delete=<?= $f['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete?')"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="featureModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="post">
            <div class="modal-header"><h5 class="modal-title" id="featureModalTitle">Add Feature</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="id" id="feature_id">
                <input type="hidden" name="save" value="1">
                <div class="mb-3">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" id="feature_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Icon (Bootstrap icon name)</label>
                    <input type="text" name="icon" id="feature_icon" class="form-control" placeholder="e.g. snow, car-front, wifi">
                </div>
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <div class="d-flex gap-2">
                        <select id="feature_category_select" class="form-select" onchange="document.getElementById('feature_category').value=this.value">
                            <option value="">General</option>
                            <?php foreach ($categories as $c): ?>
                            <option value="<?= sanitize($c) ?>"><?= sanitize($c) ?></option>
                            <?php endforeach; ?>
                            <option value="__custom__">Custom...</option>
                        </select>
                        <input type="text" name="category" id="feature_category" class="form-control" placeholder="New category">
                    </div>
                </div>
                <div class="form-check">
                    <input type="checkbox" name="status" id="feature_status" class="form-check-input" value="1" checked>
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
document.getElementById('feature_category_select').addEventListener('change', function() {
    if (this.value === '__custom__') {
        document.getElementById('feature_category').value = '';
        document.getElementById('feature_category').focus();
    } else {
        document.getElementById('feature_category').value = this.value;
    }
});
document.querySelectorAll('.edit-feature').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('featureModalTitle').textContent = 'Edit Feature';
        document.getElementById('feature_id').value = this.dataset.id;
        document.getElementById('feature_name').value = this.dataset.name;
        document.getElementById('feature_icon').value = this.dataset.icon;
        document.getElementById('feature_category').value = this.dataset.category;
        document.getElementById('feature_status').checked = this.dataset.status == '1';
        new bootstrap.Modal(document.getElementById('featureModal')).show();
    });
});
</script>
<?php require_once 'footer.php'; ?>
