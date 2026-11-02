<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username && $password) {
        if (login($username, $password)) {
            redirect('index.php');
        } else {
            $error = 'Invalid username/email or password.';
        }
    } else {
        $error = 'Please enter username and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Real Estate</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #e2ecf2;
            background-image:
                radial-gradient(circle at 15% 25%, #cfe8db 0%, rgba(207,232,219,0) 46%),
                radial-gradient(circle at 85% 20%, #cfdff5 0%, rgba(207,223,245,0) 42%),
                radial-gradient(circle at 50% 100%, #f6e3c3 0%, rgba(246,227,195,0) 48%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card { border: none; border-radius: 26px; overflow: hidden; box-shadow: 10px 10px 26px rgba(148,164,182,0.55), -10px -10px 26px rgba(255,255,255,0.9), inset 0 2px 0 rgba(255,255,255,0.9); }
        .login-header { background: linear-gradient(145deg, #8fe3b4, #69d19e); padding: 2rem; text-align: center; }
        .login-header h3 { color: #1c5a3f; margin: 0; font-weight: 700; }
        .login-header p { color: #2f6b4d; margin: 5px 0 0; font-size: 14px; }
        .login-body { padding: 2rem; background: #f3f7fb; }
        .btn-login { background: linear-gradient(145deg, #8fe3b4, #69d19e); border: none; padding: 10px; font-weight: 600; color: #1c5a3f; box-shadow: 4px 4px 10px rgba(148,164,182,0.55), -4px -4px 10px rgba(255,255,255,0.9), inset 0 2px 0 rgba(255,255,255,0.7); }
        .btn-login:hover { background: linear-gradient(145deg, #9deec0, #79dcab); color: #14432e; transform: translateY(-1px); }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card login-card">
                    <div class="login-header">
                        <h3><i class="bi bi-house-heart-fill me-2"></i>HomeHub</h3>
                        <p>Admin Panel Login</p>
                    </div>
                    <div class="login-body">
                        <?php if ($error): ?>
                        <div class="alert alert-danger py-2"><?= sanitize($error) ?></div>
                        <?php endif; ?>
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Username or Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" name="username" class="form-control" required autofocus>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-login w-100">Sign In</button>
                        </form>
                        <p class="text-center text-muted mt-3 mb-0 small">Default: admin / password</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
