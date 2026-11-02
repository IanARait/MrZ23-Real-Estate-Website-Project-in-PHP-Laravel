<?php
$page_title = 'My Profile - HomeHub';
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

    $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['customer_id']]);
    $user = $stmt->fetch();
    if ($user) {
        $user['name'] = trim($user['first_name'] . ' ' . $user['last_name']);
    }

    $saved_count = $pdo->prepare("SELECT COUNT(*) FROM saved_properties WHERE customer_id = :uid");
    $saved_count->execute(['uid' => $_SESSION['customer_id']]);
    $saved_total = $saved_count->fetchColumn();

    $inquiry_count = $pdo->prepare("SELECT COUNT(*) FROM inquiries WHERE customer_id = :uid");
    $inquiry_count->execute(['uid' => $_SESSION['customer_id']]);
    $inquiry_total = $inquiry_count->fetchColumn();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        if (!empty($name)) {
            $parts = preg_split('/\s+/', $name);
            $first_name = array_shift($parts) ?? '';
            $last_name = array_pop($parts) ?? '';
            if (empty($last_name)) $last_name = $first_name;
            $upd = $pdo->prepare("UPDATE customers SET first_name = :fn, last_name = :ln, phone = :phone WHERE id = :id");
            $upd->execute(['fn' => $first_name, 'ln' => $last_name, 'phone' => $phone, 'id' => $_SESSION['customer_id']]);
            $_SESSION['customer_name'] = trim($first_name . ' ' . $last_name);
            $user['name'] = trim($first_name . ' ' . $last_name);
            $user['phone'] = $phone;
            $success = 'Profile updated successfully.';
        }
        if (!empty($_POST['new_password']) && !empty($_POST['current_password'])) {
            $chk = $pdo->prepare("SELECT password FROM customers WHERE id = :id");
            $chk->execute(['id' => $_SESSION['customer_id']]);
            $row = $chk->fetch();
            if ($row && password_verify($_POST['current_password'], $row['password'])) {
                $hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                $upd = $pdo->prepare("UPDATE customers SET password = :pw WHERE id = :id");
                $upd->execute(['pw' => $hash, 'id' => $_SESSION['customer_id']]);
                $success = 'Password changed successfully.';
            } else {
                $error = 'Current password is incorrect.';
            }
        }
    }

} catch (PDOException $e) {
    $user = [];
    $saved_total = 0;
    $inquiry_total = 0;
}
?>

<div class="container py-4">
    <h3 class="fw-bold mb-4"><i class="fas fa-id-card text-success me-2"></i>My Profile</h3>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-success d-flex align-items-center justify-content-center text-white mx-auto mb-3" style="width:100px;height:100px;font-size:2.5rem;">
                        <?php echo strtoupper(substr($user['name'] ?? 'U', 0, 1)); ?>
                    </div>
                    <h5 class="fw-bold"><?php echo htmlspecialchars($user['name'] ?? ''); ?></h5>
                    <p class="text-muted small"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                    <hr>
                    <div class="d-flex justify-content-around">
                        <div class="text-center">
                            <h5 class="fw-bold text-success"><?php echo $saved_total; ?></h5>
                            <small class="text-muted">Saved</small>
                        </div>
                        <div class="text-center">
                            <h5 class="fw-bold text-success"><?php echo $inquiry_total; ?></h5>
                            <small class="text-muted">Inquiries</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="list-group mt-3 shadow-sm">
                <a href="<?php echo $base; ?>/customer/profile.php" class="list-group-item list-group-item-action active"><i class="fas fa-user me-2"></i>Profile</a>
                <a href="<?php echo $base; ?>/customer/saved-properties.php" class="list-group-item list-group-item-action"><i class="fas fa-heart me-2"></i>Saved Properties</a>
                <a href="<?php echo $base; ?>/customer/inquiries.php" class="list-group-item list-group-item-action"><i class="fas fa-envelope me-2"></i>My Inquiries</a>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Edit Profile</h5>

                    <?php if (isset($success)): ?>
                    <div class="alert alert-success py-2 small"><?php echo $success; ?></div>
                    <?php endif; ?>
                    <?php if (isset($error)): ?>
                    <div class="alert alert-danger py-2 small"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Full Name</label>
                            <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Email</label>
                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" disabled>
                            <small class="text-muted">Email cannot be changed</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Phone</label>
                            <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                        <button type="submit" class="btn btn-success">Update Profile</button>
                    </form>

                    <hr class="my-4">

                    <h5 class="fw-bold mb-4">Change Password</h5>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Current Password</label>
                            <input type="password" class="form-control" name="current_password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">New Password</label>
                            <input type="password" class="form-control" name="new_password" minlength="6">
                        </div>
                        <button type="submit" class="btn btn-outline-success">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/footer.php'; ?>