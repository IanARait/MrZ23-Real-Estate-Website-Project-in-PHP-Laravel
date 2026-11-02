<?php
require_once 'header.php';

$pdo = getConnection();
$msg = ''; $error = '';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
    $stmt->execute([$_GET['delete']]);
    $msg = 'User deleted.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $id = $_POST['id'] ?? null;
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $full_name = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone'] ?? '');
    $role = sanitize($_POST['role'] ?? 'agent');
    $status = isset($_POST['status']) ? 1 : 0;
    $password = $_POST['password'] ?? '';

    if ($id) {
        if ($password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET username=?, email=?, full_name=?, phone=?, role=?, status=?, password=? WHERE id=?");
            $stmt->execute([$username, $email, $full_name, $phone, $role, $status, $hash, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username=?, email=?, full_name=?, phone=?, role=?, status=? WHERE id=?");
            $stmt->execute([$username, $email, $full_name, $phone, $role, $status, $id]);
        }
        $msg = 'User updated.';
    } else {
        if (!$password) { $error = 'Password is required.'; }
        else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, phone, role, status) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$username, $email, $hash, $full_name, $phone, $role, $status]);
            $msg = 'User created.';
        }
    }
    if ($msg) redirect('users.php');
}

$users_list = $pdo->query("SELECT * FROM users ORDER BY role, full_name")->fetchAll();
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Users</h1>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#userModal"><i class="bi bi-plus-lg"></i> Add User</button>
</div>
<?php if ($msg): ?><div class="alert alert-success py-2"><?= $msg ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger py-2"><?= $error ?></div><?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Username</th><th>Full Name</th><th>Email</th><th>Role</th><th>Last Login</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($users_list as $u): ?>
                <tr>
                    <td><strong><?= sanitize($u['username']) ?></strong></td>
                    <td><?= sanitize($u['full_name']) ?></td>
                    <td><?= sanitize($u['email']) ?></td>
                    <td><span class="badge bg-<?= $u['role'] == 'admin' ? 'danger' : ($u['role'] == 'manager' ? 'warning' : 'info') ?>"><?= sanitize($u['role']) ?></span></td>
                    <td><?= $u['last_login'] ? timeAgo($u['last_login']) : 'Never' ?></td>
                    <td><?= $u['status'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary edit-user" data-id="<?= $u['id'] ?>" data-username="<?= sanitize($u['username']) ?>" data-email="<?= sanitize($u['email']) ?>" data-name="<?= sanitize($u['full_name']) ?>" data-phone="<?= sanitize($u['phone']) ?>" data-role="<?= $u['role'] ?>" data-status="<?= $u['status'] ?>"><i class="bi bi-pencil"></i></button>
                        <?php if ($u['role'] !== 'admin'): ?>
                        <a href="?delete=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete user?')"><i class="bi bi-trash"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="post">
            <div class="modal-header"><h5 class="modal-title" id="userModalTitle">Add User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="id" id="user_id">
                <input type="hidden" name="save" value="1">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Username *</label>
                        <input type="text" name="username" id="user_username" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" id="user_email" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name" id="user_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" id="user_phone" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password <?= isset($_GET['edit']) ? '(leave empty to keep)' : '*' ?></label>
                        <input type="password" name="password" id="user_password" class="form-control" <?= isset($_GET['edit']) ? '' : 'required' ?>>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Role</label>
                        <select name="role" id="user_role" class="form-select">
                            <option value="agent">Agent</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" name="status" id="user_status" class="form-check-input" value="1" checked>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
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
document.querySelectorAll('.edit-user').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('userModalTitle').textContent = 'Edit User';
        document.getElementById('user_id').value = this.dataset.id;
        document.getElementById('user_username').value = this.dataset.username;
        document.getElementById('user_email').value = this.dataset.email;
        document.getElementById('user_name').value = this.dataset.name;
        document.getElementById('user_phone').value = this.dataset.phone;
        document.getElementById('user_role').value = this.dataset.role;
        document.getElementById('user_status').checked = this.dataset.status == '1';
        document.getElementById('user_password').required = false;
        new bootstrap.Modal(document.getElementById('userModal')).show();
    });
});
document.getElementById('userModal').addEventListener('hidden.bs.modal', function() {
    if (!document.getElementById('user_id').value) {
        document.getElementById('userModalTitle').textContent = 'Add User';
        document.getElementById('user_password').required = true;
    }
});
</script>
<?php require_once 'footer.php'; ?>
