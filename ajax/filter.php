<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getConnection();

    $where = [];
    $params = [];

    if (!empty($_GET['type']) && is_array($_GET['type'])) {
        $parts = [];
        foreach (array_filter($_GET['type']) as $i => $t) {
            if (!is_numeric($t)) continue;
            $key = "type_$i";
            $parts[] = ":$key";
            $params[$key] = (int)$t;
        }
        if ($parts) $where[] = "p.property_type_id IN (" . implode(',', $parts) . ")";
    }
    if (!empty($_GET['status'])) {
        $status = $_GET['status'];
        if (in_array($status, ['sale', 'buy'])) {
            $where[] = "p.status_id = 1";
        } elseif ($status === 'rent') {
            $where[] = "p.status_id = 2";
        } elseif (is_numeric($status)) {
            $where[] = "p.status_id = :status";
            $params['status'] = (int)$status;
        }
    }
    if (!empty($_GET['bedrooms'])) {
        $where[] = "p.bedrooms >= :bedrooms";
        $params['bedrooms'] = (int)$_GET['bedrooms'];
    }
    if (!empty($_GET['bathrooms'])) {
        $where[] = "p.bathrooms >= :bathrooms";
        $params['bathrooms'] = (int)$_GET['bathrooms'];
    }
    if (!empty($_GET['min_price'])) {
        $where[] = "p.price >= :min_price";
        $params['min_price'] = (float)$_GET['min_price'];
    }
    if (!empty($_GET['max_price'])) {
        $where[] = "p.price <= :max_price";
        $params['max_price'] = (float)$_GET['max_price'];
    }
    if (!empty($_GET['min_sqft'])) {
        $where[] = "p.sqft >= :min_sqft";
        $params['min_sqft'] = (float)$_GET['min_sqft'];
    }
    if (!empty($_GET['max_sqft'])) {
        $where[] = "p.sqft <= :max_sqft";
        $params['max_sqft'] = (float)$_GET['max_sqft'];
    }
    if (!empty($_GET['location'])) {
        $loc = '%' . $_GET['location'] . '%';
        $where[] = "(p.address_line1 LIKE :loc OR p.address_line2 LIKE :loc2 OR p.city LIKE :loc3 OR p.state LIKE :loc4 OR p.zip_code LIKE :loc5)";
        $params['loc'] = $loc;
        $params['loc2'] = $loc;
        $params['loc3'] = $loc;
        $params['loc4'] = $loc;
        $params['loc5'] = $loc;
    }

    $sort = $_GET['sort'] ?? 'newest';
    $order = match ($sort) {
        'price_low' => 'p.price ASC',
        'price_high' => 'p.price DESC',
        'oldest' => 'p.created_at ASC',
        'largest' => 'p.sqft DESC',
        default => 'p.created_at DESC',
    };

    $where[] = "p.status = 1";
    $where_clause = 'WHERE ' . implode(' AND ', $where);
    $sql = "SELECT p.*, pt.name AS type_name, ps.name AS status_name FROM properties p
            LEFT JOIN property_types pt ON p.property_type_id = pt.id
            LEFT JOIN property_statuses ps ON p.status_id = ps.id
            $where_clause ORDER BY p.is_featured DESC, $order LIMIT 50";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $properties = $stmt->fetchAll();

    $base = 'http://' . $_SERVER['HTTP_HOST'] . '/E_COMMERCE/real-estate';
    $output = [];

    foreach ($properties as $prop) {
        $img = '';
        if (!empty($prop['featured_image'])) {
            $path = $_SERVER['DOCUMENT_ROOT'] . '/E_COMMERCE/real-estate/uploads/properties/' . $prop['featured_image'];
            if (file_exists($path)) $img = $base . '/uploads/properties/' . $prop['featured_image'];
        }
        $address = trim(($prop['address_line1'] ?? '') . ', ' . ($prop['city'] ?? '') . ', ' . ($prop['state'] ?? ''));
        $output[] = [
            'id' => $prop['id'],
            'title' => htmlspecialchars($prop['title']),
            'price' => '$' . number_format($prop['price']),
            'address' => htmlspecialchars($address),
            'bedrooms' => $prop['bedrooms'],
            'bathrooms' => $prop['bathrooms'],
            'sqft' => number_format($prop['sqft']),
            'status' => $prop['status_name'],
            'featured' => (bool)$prop['is_featured'],
            'image' => $img,
            'url' => $base . '/customer/property-detail.php?id=' . $prop['id'],
        ];
    }

    echo json_encode(['success' => true, 'count' => count($output), 'properties' => $output]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}