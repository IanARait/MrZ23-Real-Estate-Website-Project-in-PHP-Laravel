<?php
$page_title = 'Login - HomeHub';
$hide_search = true;
require_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/header.php';

$base = 'http://' . $_SERVER['HTTP_HOST'] . '/E_COMMERCE/real-estate';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = :email");
        $stmt->execute(['email' => trim($_POST['email'] ?? '')]);
        $customer = $stmt->fetch();

        if ($customer && password_verify($_POST['password'] ?? '', $customer['password'])) {
            $_SESSION['customer_id'] = $customer['id'];
            $_SESSION['customer_email'] = $customer['email'];
            $_SESSION['customer_name'] = $customer['first_name'] . ' ' . $customer['last_name'];
            header('Location: ' . $base . '/customer/index.php');
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    } catch (PDOException $e) {
        $error = 'Database error. Please try again.';
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="fas fa-home fa-3x text-success mb-2"></i>
                        <h3 class="fw-bold">Welcome Back</h3>
                        <p class="text-muted">Sign in to your HomeHub account</p>
                    </div>

                    <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Email Address</label>
                            <input type="email" class="form-control" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember">
                            <label class="form-check-label small" for="remember">Remember me</label>
                        </div>
                        <button type="submit" class="btn btn-success w-100 mb-3">Sign In</button>
                        <div class="text-center">
                            <small class="text-muted">Don't have an account? <a href="<?php echo $base; ?>/customer/register.php" class="text-success">Register here</a></small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/footer.php'; ?>