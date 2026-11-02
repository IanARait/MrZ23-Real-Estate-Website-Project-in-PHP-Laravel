<?php
require_once 'header.php';

$pdo = getConnection();
$msg = ''; $error = '';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("SELECT photo FROM agents WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $agent = $stmt->fetch();
    if ($agent && $agent['photo']) {
        $p = __DIR__ . '/../uploads/agents/' . $agent['photo'];
        if (file_exists($p)) unlink($p);
    }
    $stmt = $pdo->prepare("DELETE FROM agents WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $msg = 'Agent deleted.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $id = $_POST['id'] ?? null;
    $user_id = $_POST['user_id'] ?: null;
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone'] ?? '');
    $license_number = sanitize($_POST['license_number'] ?? '');
    $bio = $_POST['bio'] ?? '';
    $facebook = sanitize($_POST['facebook'] ?? '');
    $twitter = sanitize($_POST['twitter'] ?? '');
    $linkedin = sanitize($_POST['linkedin'] ?? '');
    $instagram = sanitize($_POST['instagram'] ?? '');
    $experience_years = intval($_POST['experience_years'] ?? 0);
    $specialties = sanitize($_POST['specialties'] ?? '');
    $status = isset($_POST['status']) ? 1 : 0;

    $uploadDir = __DIR__ . '/../uploads/agents/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $photo = $_POST['existing_photo'] ?? '';
    if (!empty($_FILES['photo']['name'])) {
        $up = uploadImage($_FILES['photo'], $uploadDir, 'agent');
        if ($up) {
            if ($photo && file_exists($uploadDir . $photo)) unlink($uploadDir . $photo);
            $photo = $up;
        }
    }

    if ($id) {
        $stmt = $pdo->prepare("UPDATE agents SET user_id=?, full_name=?, email=?, phone=?, photo=?, license_number=?, bio=?, facebook=?, twitter=?, linkedin=?, instagram=?, experience_years=?, specialties=?, status=? WHERE id=?");
        $stmt->execute([$user_id, $full_name, $email, $phone, $photo, $license_number, $bio, $facebook, $twitter, $linkedin, $instagram, $experience_years, $specialties, $status, $id]);
        $msg = 'Agent updated.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO agents (user_id, full_name, email, phone, photo, license_number, bio, facebook, twitter, linkedin, instagram, experience_years, specialties, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$user_id, $full_name, $email, $phone, $photo, $license_number, $bio, $facebook, $twitter, $linkedin, $instagram, $experience_years, $specialties, $status]);
        $msg = 'Agent created.';
    }
    redirect('agents.php');
}

$agents_list = $pdo->query("SELECT a.*, (SELECT COUNT(*) FROM properties WHERE agent_id = a.id AND status = 1) AS prop_count FROM agents a ORDER BY a.full_name")->fetchAll();
$users = $pdo->query("SELECT id, username, full_name FROM users WHERE status = 1 ORDER BY full_name")->fetchAll();
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Agents</h1>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#agentModal"><i class="bi bi-plus-lg"></i> Add Agent</button>
</div>
<?php if ($msg): ?><div class="alert alert-success py-2"><?= $msg ?></div><?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table id="agentsTable" class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Photo</th><th>Name</th><th>Email</th><th>Phone</th><th>Properties</th><th>Experience</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($agents_list as $a): ?>
                <tr>
                    <td>
                        <?php if ($a['photo']): ?>
                        <img src="../uploads/agents/<?= sanitize($a['photo']) ?>" style="width:40px;height:40px;object-fit:cover;border-radius:50%;">
                        <?php else: ?>
                        <div style="width:40px;height:40px;border-radius:50%;background:#e9ecef;display:flex;align-items:center;justify-content:center;"><i class="bi bi-person text-muted"></i></div>
                        <?php endif; ?>
                    </td>
                    <td><strong><?= sanitize($a['full_name']) ?></strong></td>
                    <td><?= sanitize($a['email']) ?></td>
                    <td><?= sanitize($a['phone'] ?: '-') ?></td>
                    <td><span class="badge bg-info"><?= $a['prop_count'] ?></span></td>
                    <td><?= $a['experience_years'] ?> yrs</td>
                    <td><?= $a['status'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary edit-agent" data-id="<?= $a['id'] ?>" data-user="<?= $a['user_id'] ?>" data-name="<?= sanitize($a['full_name']) ?>" data-email="<?= sanitize($a['email']) ?>" data-phone="<?= sanitize($a['phone']) ?>" data-photo="<?= sanitize($a['photo']) ?>" data-license="<?= sanitize($a['license_number']) ?>" data-bio="<?= sanitize($a['bio']) ?>" data-fb="<?= sanitize($a['facebook']) ?>" data-tw="<?= sanitize($a['twitter']) ?>" data-li="<?= sanitize($a['linkedin']) ?>" data-ig="<?= sanitize($a['instagram']) ?>" data-exp="<?= $a['experience_years'] ?>" data-spec="<?= sanitize($a['specialties']) ?>" data-status="<?= $a['status'] ?>"><i class="bi bi-pencil"></i></button>
                        <a href="?delete=<?= $a['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete agent?')"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="agentModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <form method="post" enctype="multipart/form-data">
            <div class="modal-header"><h5 class="modal-title" id="agentModalTitle">Add Agent</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="id" id="agent_id">
                <input type="hidden" name="existing_photo" id="agent_existing_photo">
                <input type="hidden" name="save" value="1">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name" id="agent_name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" id="agent_email" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" id="agent_phone" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">License Number</label>
                        <input type="text" name="license_number" id="agent_license" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Experience (years)</label>
                        <input type="number" name="experience_years" id="agent_exp" class="form-control" min="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Photo</label>
                        <input type="file" name="photo" class="form-control" accept="image/*">
                        <div id="agent_photo_preview" class="mt-2"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">User Account (optional)</label>
                        <select name="user_id" id="agent_user" class="form-select">
                            <option value="">None</option>
                            <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= sanitize($u['full_name']) ?> (<?= sanitize($u['username']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Bio</label>
                        <textarea name="bio" id="agent_bio" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Specialties</label>
                        <textarea name="specialties" id="agent_spec" class="form-control" rows="2" placeholder="Comma separated"></textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Facebook</label>
                        <input type="url" name="facebook" id="agent_fb" class="form-control" placeholder="https://">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Twitter</label>
                        <input type="url" name="twitter" id="agent_tw" class="form-control" placeholder="https://">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">LinkedIn</label>
                        <input type="url" name="linkedin" id="agent_li" class="form-control" placeholder="https://">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Instagram</label>
                        <input type="url" name="instagram" id="agent_ig" class="form-control" placeholder="https://">
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" name="status" id="agent_status" class="form-check-input" value="1" checked>
                            <label class="form-check-label">Active</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Agent</button>
            </div>
        </form>
    </div></div>
</div>

<script>
document.querySelectorAll('.edit-agent').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('agentModalTitle').textContent = 'Edit Agent';
        document.getElementById('agent_id').value = this.dataset.id;
        document.getElementById('agent_name').value = this.dataset.name;
        document.getElementById('agent_email').value = this.dataset.email;
        document.getElementById('agent_phone').value = this.dataset.phone;
        document.getElementById('agent_license').value = this.dataset.license;
        document.getElementById('agent_exp').value = this.dataset.exp;
        document.getElementById('agent_bio').value = this.dataset.bio;
        document.getElementById('agent_spec').value = this.dataset.spec;
        document.getElementById('agent_fb').value = this.dataset.fb;
        document.getElementById('agent_tw').value = this.dataset.tw;
        document.getElementById('agent_li').value = this.dataset.li;
        document.getElementById('agent_ig').value = this.dataset.ig;
        document.getElementById('agent_user').value = this.dataset.user || '';
        document.getElementById('agent_status').checked = this.dataset.status == '1';
        document.getElementById('agent_existing_photo').value = this.dataset.photo || '';
        if (this.dataset.photo) {
            document.getElementById('agent_photo_preview').innerHTML = '<img src="../uploads/agents/' + this.dataset.photo + '" style="height:60px;border-radius:4px;">';
        } else {
            document.getElementById('agent_photo_preview').innerHTML = '';
        }
        new bootstrap.Modal(document.getElementById('agentModal')).show();
    });
});
</script>
<?php require_once 'footer.php'; ?>
