<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if (!isset($_SESSION['admin_id'])) {
    redirect('login.php');
}

$current_page = basename($_SERVER['SCRIPT_NAME']);
$site_name = getSetting('site_name') ?: 'Real Estate CMS';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($site_name) ?> - Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<nav class="navbar fixed-top px-3 clay-topnav">
    <a class="navbar-brand d-flex align-items-center" href="index.php">
        <i class="bi bi-house-heart-fill me-2" style="color:var(--bs-primary)"></i>
        <span><?= sanitize($site_name) ?></span>
    </a>
    <div class="d-flex align-items-center">
        <span class="clay-user me-3"><i class="bi bi-person-circle me-1"></i><?= sanitize($_SESSION['admin_name']) ?> (<?= sanitize($_SESSION['admin_role']) ?>)</span>
        <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-md-block sidebar clay-sidebar pt-4">
            <div class="position-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?= $current_page == 'index.php' ? 'active' : '' ?>" href="index.php">
                            <i class="bi bi-speedometer2 me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $current_page == 'properties.php' ? 'active' : '' ?>" href="properties.php">
                            <i class="bi bi-building me-2"></i>Properties
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $current_page == 'property-types.php' ? 'active' : '' ?>" href="property-types.php">
                            <i class="bi bi-tags me-2"></i>Property Types
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $current_page == 'property-statuses.php' ? 'active' : '' ?>" href="property-statuses.php">
                            <i class="bi bi-flag me-2"></i>Statuses
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $current_page == 'features.php' ? 'active' : '' ?>" href="features.php">
                            <i class="bi bi-list-check me-2"></i>Features
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $current_page == 'agents.php' ? 'active' : '' ?>" href="agents.php">
                            <i class="bi bi-people me-2"></i>Agents
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $current_page == 'inquiries.php' ? 'active' : '' ?>" href="inquiries.php">
                            <i class="bi bi-envelope me-2"></i>Inquiries
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $current_page == 'customers.php' ? 'active' : '' ?>" href="customers.php">
                            <i class="bi bi-person-badge me-2"></i>Customers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $current_page == 'users.php' ? 'active' : '' ?>" href="users.php">
                            <i class="bi bi-shield-lock me-2"></i>Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $current_page == 'reports.php' ? 'active' : '' ?>" href="reports.php">
                            <i class="bi bi-bar-chart me-2"></i>Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $current_page == 'settings.php' ? 'active' : '' ?>" href="settings.php">
                            <i class="bi bi-gear me-2"></i>Settings
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
        <main class="col-md-10 ms-sm-auto px-4 main-content">
