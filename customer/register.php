<?php
$page_title = 'Register - HomeHub';
$hide_search = true;
require_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/header.php';

$base = 'http://' . $_SERVER['HTTP_HOST'] . '/E_COMMERCE/real-estate';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $parts = preg_split('/\s+/', $name);
    $first_name = array_shift($parts) ?? '';
    $last_name = array_pop($parts) ?? '';
    if (empty($last_name)) $last_name = $first_name;

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            $pdo = getConnection();

            $check = $pdo->prepare("SELECT id FROM customers WHERE email = :email");
            $check->execute(['email' => $email]);
            if ($check->fetch()) {
                $error = 'An account with this email already exists.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO customers (email, password, first_name, last_name, phone) VALUES (:email, :password, :first_name, :last_name, :phone)");
                $stmt->execute(['email' => $email, 'password' => $hash, 'first_name' => $first_name, 'last_name' => $last_name, 'phone' => $phone]);
                $success = 'Registration successful! You can now login.';
            }
        } catch (PDOException $e) {
            $error = 'Database error. Please try again.';
        }
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
                        <h3 class="fw-bold">Create Account</h3>
                        <p class="text-muted">Join HomeHub today</p>
                    </div>

                    <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                    <div class="alert alert-success py-2 small"><?php echo $success; ?> <a href="<?php echo $base; ?>/customer/login.php" class="text-success fw-bold">Login</a></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Full Name *</label>
                            <input type="text" class="form-control" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Email Address *</label>
                            <input type="email" class="form-control" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Phone Number</label>
                            <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Password *</label>
                            <input type="password" class="form-control" name="password" required minlength="6">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Confirm Password *</label>
                            <input type="password" class="form-control" name="confirm_password" required minlength="6">
                        </div>
                        <button type="submit" class="btn btn-success w-100 mb-3">Create Account</button>
                        <div class="text-center">
                            <small class="text-muted">Already have an account? <a href="<?php echo $base; ?>/customer/login.php" class="text-success">Sign in</a></small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/footer.php'; ?>
