<?php
require_once __DIR__ . '/../config/database.php';

function isLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function isCustomerLoggedIn() {
    return isset($_SESSION['customer_id']);
}

function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function formatPrice($amount, $currency = null) {
    if ($currency === null) {
        $currency = getSetting('currency_symbol') ?: '$';
    }
    return $currency . number_format($amount, 2);
}

function getSetting($key) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    return $row ? $row['setting_value'] : null;
}

function getPropertyTypes($status = 1) {
    $pdo = getConnection();
    if ($status !== null) {
        $stmt = $pdo->prepare("SELECT * FROM property_types WHERE status = ? ORDER BY name");
        $stmt->execute([$status]);
    } else {
        $stmt = $pdo->query("SELECT * FROM property_types ORDER BY name");
    }
    return $stmt->fetchAll();
}

function getStatuses($status = 1) {
    $pdo = getConnection();
    if ($status !== null) {
        $stmt = $pdo->prepare("SELECT * FROM property_statuses WHERE status = ? ORDER BY name");
        $stmt->execute([$status]);
    } else {
        $stmt = $pdo->query("SELECT * FROM property_statuses ORDER BY name");
    }
    return $stmt->fetchAll();
}

function getFeatures($status = 1) {
    $pdo = getConnection();
    if ($status !== null) {
        $stmt = $pdo->prepare("SELECT * FROM features WHERE status = ? ORDER BY category, name");
        $stmt->execute([$status]);
    } else {
        $stmt = $pdo->query("SELECT * FROM features ORDER BY category, name");
    }
    return $stmt->fetchAll();
}

function getProperties($params = []) {
    $pdo = getConnection();
    $where = [];
    $binds = [];

    $where[] = "p.status = 1";

    if (!empty($params['type'])) {
        $where[] = "p.property_type_id = :type";
        $binds[':type'] = $params['type'];
    }
    if (!empty($params['status_id'])) {
        $where[] = "p.status_id = :status_id";
        $binds[':status_id'] = $params['status_id'];
    }
    if (!empty($params['city'])) {
        $where[] = "p.city LIKE :city";
        $binds[':city'] = '%' . $params['city'] . '%';
    }
    if (!empty($params['state'])) {
        $where[] = "p.state = :state";
        $binds[':state'] = $params['state'];
    }
    if (!empty($params['min_price'])) {
        $where[] = "p.price >= :min_price";
        $binds[':min_price'] = $params['min_price'];
    }
    if (!empty($params['max_price'])) {
        $where[] = "p.price <= :max_price";
        $binds[':max_price'] = $params['max_price'];
    }
    if (!empty($params['bedrooms'])) {
        $where[] = "p.bedrooms >= :bedrooms";
        $binds[':bedrooms'] = $params['bedrooms'];
    }
    if (!empty($params['bathrooms'])) {
        $where[] = "p.bathrooms >= :bathrooms";
        $binds[':bathrooms'] = $params['bathrooms'];
    }
    if (!empty($params['search'])) {
        $where[] = "(p.title LIKE :search OR p.description LIKE :search2 OR p.city LIKE :search3)";
        $binds[':search'] = '%' . $params['search'] . '%';
        $binds[':search2'] = '%' . $params['search'] . '%';
        $binds[':search3'] = '%' . $params['search'] . '%';
    }
    if (!empty($params['featured'])) {
        $where[] = "p.is_featured = 1";
    }
    if (!empty($params['agent_id'])) {
        $where[] = "p.agent_id = :agent_id";
        $binds[':agent_id'] = $params['agent_id'];
    }

    $whereClause = implode(' AND ', $where);

    $sql = "SELECT p.*, pt.name AS type_name, ps.name AS status_name, ps.color AS status_color,
            a.full_name AS agent_name
            FROM properties p
            LEFT JOIN property_types pt ON p.property_type_id = pt.id
            LEFT JOIN property_statuses ps ON p.status_id = ps.id
            LEFT JOIN agents a ON p.agent_id = a.id
            WHERE $whereClause
            ORDER BY " . (!empty($params['order']) ? $params['order'] : 'p.created_at DESC');

    if (!empty($params['limit'])) {
        $sql .= " LIMIT " . intval($params['limit']);
        if (!empty($params['offset'])) {
            $sql .= " OFFSET " . intval($params['offset']);
        }
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($binds);
    return $stmt->fetchAll();
}

function getFeaturedProperties($limit = 6) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT p.*, pt.name AS type_name, ps.name AS status_name, ps.color AS status_color
            FROM properties p
            LEFT JOIN property_types pt ON p.property_type_id = pt.id
            LEFT JOIN property_statuses ps ON p.status_id = ps.id
            WHERE p.status = 1 AND p.is_featured = 1
            ORDER BY p.created_at DESC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getPropertyCountByCity() {
    $pdo = getConnection();
    $stmt = $pdo->query("SELECT city, COUNT(*) AS total FROM properties WHERE status = 1 AND city IS NOT NULL GROUP BY city ORDER BY total DESC LIMIT 10");
    return $stmt->fetchAll();
}

function paginate($page, $total, $perPage = 12, $url = '') {
    $totalPages = ceil($total / $perPage);
    if ($totalPages <= 1) return '';

    $html = '<nav><ul class="pagination">';

    if ($page > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . ($page - 1) . '">Previous</a></li>';
    }

    for ($i = 1; $i <= $totalPages; $i++) {
        $active = $i == $page ? ' active' : '';
        $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . $url . '?page=' . $i . '">' . $i . '</a></li>';
    }

    if ($page < $totalPages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $url . '?page=' . ($page + 1) . '">Next</a></li>';
    }

    $html .= '</ul></nav>';
    return $html;
}

function uploadImage($file, $targetDir, $prefix = '') {
    if ($file['error'] !== UPLOAD_ERR_OK) return false;

    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) return false;

    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

    $filename = ($prefix ? $prefix . '_' : '') . time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $filename;

    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return $filename;
    }
    return false;
}

function getStatusBadge($status, $color = null) {
    if ($color === null) $color = '#6c757d';
    return '<span class="badge" style="background-color:' . htmlspecialchars($color) . ';color:#fff;padding:4px 10px;border-radius:4px;font-size:12px;">' . htmlspecialchars($status) . '</span>';
}

function formatDate($date, $format = 'M d, Y') {
    if (!$date) return '-';
    return date($format, strtotime($date));
}

function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 2592000) return floor($diff / 86400) . 'd ago';
    return date('M d, Y', $timestamp);
}

function truncate($text, $length = 100) {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

function generateSlug($string) {
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function customerPropImage($prop) {
    static $root = null;
    if ($root === null) {
        $docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
        $root = rtrim($docRoot, '/') . '/E_COMMERCE/real-estate';
    }
    if (empty($prop['featured_image'])) return '';
    $file = $root . '/uploads/properties/' . $prop['featured_image'];
    return file_exists($file) ? 'uploads/properties/' . $prop['featured_image'] : '';
}

function customerPropImageTag($prop, $height = '220px', $alt = null) {
    $base = 'http://' . $_SERVER['HTTP_HOST'] . '/E_COMMERCE/real-estate';
    $label = htmlspecialchars($alt ?? (array_key_exists('title', $prop) && $prop['title'] ? $prop['title'] : 'Property'));
    $h = htmlspecialchars($height);
    $img = customerPropImage($prop);
    if ($img !== '') {
        return '<img class="property-img" src="' . $base . '/' . htmlspecialchars($img) . '" alt="' . $label . '" style="height:' . $h . ';width:100%;object-fit:cover;" loading="lazy" onerror="this.onerror=null;this.src=\'' . $base . '/uploads/properties/_placeholder.svg\';">';
    }
    return '<div class="property-img-placeholder" style="height:' . $h . ';width:100%;"><i class="fas fa-image"></i></div>';
}

function customerPropAddress($prop) {
    $parts = [
        trim($prop['address_line1'] ?? ''),
        trim($prop['city'] ?? ''),
        trim($prop['state'] ?? ''),
        trim($prop['zip_code'] ?? ''),
    ];
    return trim(implode(', ', array_filter($parts)));
}
