<?php
session_start();
require_once __DIR__ . '/../config/database.php';
$base = 'http://' . $_SERVER['HTTP_HOST'] . '/E_COMMERCE/real-estate';

$type_options = [];
try {
    $type_options = getConnection()->query("SELECT id, name FROM property_types WHERE status = 1 ORDER BY name")->fetchAll();
} catch (Exception $e) {
    $type_options = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'HomeHub - Real Estate'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/customer.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark top-navbar">
    <div class="container">
        <a class="navbar-brand fw-bold fs-3" href="<?php echo $base; ?>/customer/index.php">
            <i class="fas fa-home me-2"></i>HomeHub
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>" href="<?php echo $base; ?>/customer/index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'properties') !== false ? 'active' : ''; ?>" href="<?php echo $base; ?>/customer/properties.php?status=buy">Buy</a></li>
                <li class="nav-item"><a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'properties') !== false ? 'active' : ''; ?>" href="<?php echo $base; ?>/customer/properties.php?status=rent">Rent</a></li>
                <li class="nav-item"><a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'agent') !== false ? 'active' : ''; ?>" href="<?php echo $base; ?>/customer/agents.php">Agents</a></li>
                <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <?php if (isset($_SESSION['customer_id'])): ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['customer_name']); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo $base; ?>/customer/profile.php"><i class="fas fa-id-card me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="<?php echo $base; ?>/customer/saved-properties.php"><i class="fas fa-heart me-2"></i>Saved Properties</a></li>
                            <li><a class="dropdown-item" href="<?php echo $base; ?>/customer/inquiries.php"><i class="fas fa-envelope me-2"></i>My Inquiries</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo $base; ?>/customer/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?php echo $base; ?>/customer/login.php" class="btn btn-outline-light btn-sm"><i class="fas fa-sign-in-alt me-1"></i>Login</a>
                    <a href="<?php echo $base; ?>/customer/register.php" class="btn btn-light btn-sm text-success"><i class="fas fa-user-plus me-1"></i>Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<?php if (!isset($hide_search) || !$hide_search): ?>
<div class="search-bar bg-white shadow-sm py-3 border-bottom sticky-top" style="z-index:1020;">
    <div class="container">
        <form action="<?php echo $base; ?>/customer/properties.php" method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fas fa-map-marker-alt text-success"></i></span>
                    <input type="text" class="form-control" name="location" placeholder="City or Address" value="<?php echo htmlspecialchars($_GET['location'] ?? ''); ?>">
                </div>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="type">
                    <option value="">Property Type</option>
                    <?php foreach ($type_options as $t): ?>
                    <option value="<?php echo $t['id']; ?>" <?php echo (isset($_GET['type']) && $_GET['type'] === (string)$t['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($t['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control form-control-sm" name="min_price" placeholder="Min Price" value="<?php echo htmlspecialchars($_GET['min_price'] ?? ''); ?>">
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control form-control-sm" name="max_price" placeholder="Max Price" value="<?php echo htmlspecialchars($_GET['max_price'] ?? ''); ?>">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-success btn-sm w-100"><i class="fas fa-search me-1"></i>Search</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
<main>
