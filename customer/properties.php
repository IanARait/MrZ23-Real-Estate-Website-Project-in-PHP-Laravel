<?php
$page_title = 'Properties - HomeHub';
require_once __DIR__ . '/../includes/functions.php';
include_once __DIR__ . '/header.php';

$base = 'http://' . $_SERVER['HTTP_HOST'] . '/E_COMMERCE/real-estate';

$where = [];
$params = [];

if (!empty($_GET['location'])) {
    $where[] = "(p.address_line1 LIKE :loc OR p.address_line2 LIKE :loc2 OR p.city LIKE :loc3 OR p.state LIKE :loc4 OR p.zip_code LIKE :loc5)";
    $loc = '%' . $_GET['location'] . '%';
    $params['loc'] = $loc;
    $params['loc2'] = $loc;
    $params['loc3'] = $loc;
    $params['loc4'] = $loc;
    $params['loc5'] = $loc;
}

$type_ids = [];
if (!empty($_GET['type'])) {
    $type_ids[] = $_GET['type'];
}
$type_filter = $_GET['type_filter'] ?? [];
if (!empty($type_filter) && is_array($type_filter)) {
    foreach ($type_filter as $tf) {
        if ($tf !== '') $type_ids[] = $tf;
    }
}
$type_ids = array_values(array_unique(array_filter($type_ids)));
if (!empty($type_ids)) {
    $numPart = [];
    $slugs = [];
    foreach ($type_ids as $t) {
        if (is_numeric($t)) $numPart[] = (int)$t;
        else $slugs[] = strtolower(trim($t));
    }
    $orParts = [];
    if (!empty($numPart)) {
        $ph = [];
        foreach ($numPart as $i => $id) { $key = "ti_$i"; $ph[] = ":$key"; $params[$key] = $id; }
        $orParts[] = "(p.property_type_id IN (" . implode(',', $ph) . "))";
    }
    if (!empty($slugs)) {
        $ph = [];
        foreach ($slugs as $i => $s) { $key = "ts_$i"; $ph[] = ":$key"; $params[$key] = $s; }
        $orParts[] = "(p.property_type_id IN (SELECT id FROM property_types WHERE slug IN (" . implode(',', $ph) . ")))";
    }
    if (!empty($orParts)) $where[] = '(' . implode(' OR ', $orParts) . ')';
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
if (!empty($_GET['min_price'])) {
    $where[] = "p.price >= :min_price";
    $params['min_price'] = (float)$_GET['min_price'];
}
if (!empty($_GET['max_price'])) {
    $where[] = "p.price <= :max_price";
    $params['max_price'] = (float)$_GET['max_price'];
}
if (!empty($_GET['bedrooms'])) {
    $where[] = "p.bedrooms >= :bedrooms";
    $params['bedrooms'] = (int)$_GET['bedrooms'];
}
if (!empty($_GET['bathrooms'])) {
    $where[] = "p.bathrooms >= :bathrooms";
    $params['bathrooms'] = (int)$_GET['bathrooms'];
}
if (!empty($_GET['min_sqft'])) {
    $where[] = "p.sqft >= :min_sqft";
    $params['min_sqft'] = (float)$_GET['min_sqft'];
}
if (!empty($_GET['max_sqft'])) {
    $where[] = "p.sqft <= :max_sqft";
    $params['max_sqft'] = (float)$_GET['max_sqft'];
}
if (!empty($_GET['city'])) {
    $where[] = "p.city LIKE :city";
    $params['city'] = '%' . $_GET['city'] . '%';
}
if (!empty($_GET['state'])) {
    $where[] = "p.state LIKE :state";
    $params['state'] = '%' . $_GET['state'] . '%';
}
if (!empty($_GET['featured'])) {
    $where[] = "p.is_featured = 1";
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

try {
    $pdo = getConnection();

    $count_sql = "SELECT COUNT(*) FROM properties p LEFT JOIN property_types pt ON p.property_type_id = pt.id $where_clause";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_properties = $count_stmt->fetchColumn();

    $per_page = 9;
    $page = max(1, (int)($_GET['page'] ?? 1));
    $offset = ($page - 1) * $per_page;
    $total_pages = ceil($total_properties / $per_page);

    $sql = "SELECT p.*, pt.name AS type_name, ps.name AS status_name, ps.color AS status_color FROM properties p
            LEFT JOIN property_types pt ON p.property_type_id = pt.id
            LEFT JOIN property_statuses ps ON p.status_id = ps.id
            $where_clause ORDER BY p.is_featured DESC, $order LIMIT $per_page OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $properties = $stmt->fetchAll();

    $all_types = $pdo->query("SELECT id, name, slug FROM property_types WHERE status = 1 ORDER BY name")->fetchAll();

} catch (PDOException $e) {
    $total_properties = 0;
    $properties = [];
    $all_types = [];
    $total_pages = 1;
}
?>
<div class="container-fluid py-4">
    <div class="container">
        <div class="row">
            <!-- Filter Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="filter-sidebar card shadow-sm border-0 sticky-top" style="top:100px;">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3"><i class="fas fa-sliders-h text-success me-2"></i>Filters</h5>
                        <form id="filterForm" method="GET" action="<?php echo $base; ?>/customer/properties.php">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Location</label>
                                <input type="text" class="form-control form-control-sm" name="location" placeholder="City, Address or ZIP" value="<?php echo htmlspecialchars($_GET['location'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Property Type</label>
                                <?php foreach ($all_types as $t): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="type_filter[]" value="<?php echo $t['id']; ?>" id="type_<?php echo $t['id']; ?>" <?php echo (isset($_GET['type_filter']) && in_array((string)$t['id'], array_map('strval', (array)$_GET['type_filter']))) || (isset($_GET['type']) && !empty($type_ids) && in_array((string)$t['id'], $type_ids)) ? 'checked' : ''; ?>>
                                    <label class="form-check-label small" for="type_<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['name']); ?></label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Status</label>
                                <select class="form-select form-select-sm" name="status">
                                    <option value="">All</option>
                                    <option value="sale" <?php echo (isset($_GET['status']) && $_GET['status'] === 'sale') ? 'selected' : ''; ?>>For Sale</option>
                                    <option value="rent" <?php echo (isset($_GET['status']) && $_GET['status'] === 'rent') ? 'selected' : ''; ?>>For Rent</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Bedrooms</label>
                                <select class="form-select form-select-sm" name="bedrooms">
                                    <option value="">Any</option>
                                    <?php for ($i = 1; $i <= 6; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo (isset($_GET['bedrooms']) && (int)$_GET['bedrooms'] === $i) ? 'selected' : ''; ?>><?php echo $i; ?>+</option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Bathrooms</label>
                                <select class="form-select form-select-sm" name="bathrooms">
                                    <option value="">Any</option>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo (isset($_GET['bathrooms']) && (int)$_GET['bathrooms'] === $i) ? 'selected' : ''; ?>><?php echo $i; ?>+</option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Price Range</label>
                                <div class="row g-1">
                                    <div class="col-6">
                                        <input type="number" class="form-control form-control-sm" name="min_price" placeholder="Min" value="<?php echo htmlspecialchars($_GET['min_price'] ?? ''); ?>">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" class="form-control form-control-sm" name="max_price" placeholder="Max" value="<?php echo htmlspecialchars($_GET['max_price'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Sqft Range</label>
                                <div class="row g-1">
                                    <div class="col-6">
                                        <input type="number" class="form-control form-control-sm" name="min_sqft" placeholder="Min" value="<?php echo htmlspecialchars($_GET['min_sqft'] ?? ''); ?>">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" class="form-control form-control-sm" name="max_sqft" placeholder="Max" value="<?php echo htmlspecialchars($_GET['max_sqft'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">City</label>
                                <input type="text" class="form-control form-control-sm" name="city" placeholder="City" value="<?php echo htmlspecialchars($_GET['city'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">State</label>
                                <input type="text" class="form-control form-control-sm" name="state" placeholder="State" value="<?php echo htmlspecialchars($_GET['state'] ?? ''); ?>">
                            </div>
                            <button type="submit" class="btn btn-success w-100 btn-sm mb-2"><i class="fas fa-filter me-1"></i>Apply Filters</button>
                            <a href="<?php echo $base; ?>/customer/properties.php" class="btn btn-outline-secondary w-100 btn-sm">Clear Filters</a>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Property Listings -->
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <h4 class="fw-bold mb-0"><?php echo $total_properties; ?> Properties Found</h4>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label class="small text-muted">Sort:</label>
                        <select class="form-select form-select-sm" style="width:auto;" id="sortSelect" onchange="location.href=updateQueryString('sort',this.value)">
                            <option value="newest" <?php echo ($sort === 'newest') ? 'selected' : ''; ?>>Newest</option>
                            <option value="oldest" <?php echo ($sort === 'oldest') ? 'selected' : ''; ?>>Oldest</option>
                            <option value="price_low" <?php echo ($sort === 'price_low') ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo ($sort === 'price_high') ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="largest" <?php echo ($sort === 'largest') ? 'selected' : ''; ?>>Largest</option>
                        </select>
                        <button class="btn btn-outline-success btn-sm" id="mapViewBtn" type="button"><i class="fas fa-map-marked-alt"></i></button>
                    </div>
                </div>

                <div id="mapView" class="mb-4" style="display:none;">
                    <div class="map-placeholder bg-light rounded d-flex align-items-center justify-content-center" style="height:400px;">
                        <div class="text-center">
                            <i class="fas fa-map-marked-alt fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">Map View</h5>
                            <p class="text-muted small">Interactive map would be loaded here with Google Maps or Leaflet</p>
                        </div>
                    </div>
                </div>

                <div id="propertyGrid" class="row g-4">
                    <?php if (empty($properties)): ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-home fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No properties found</h5>
                        <p class="text-muted">Try adjusting your filters or search criteria</p>
                    </div>
                    <?php else: ?>
                        <?php foreach ($properties as $prop): ?>
                        <div class="col-md-6 col-xl-4">
                            <div class="property-card card border-0 shadow-sm h-100">
                                <div class="position-relative overflow-hidden">
                                    <?php echo customerPropImageTag($prop, '200px'); ?>
                                    <?php if ($prop['is_featured']): ?>
                                    <span class="badge bg-success position-absolute top-0 start-0 m-2">Featured</span>
                                    <?php endif; ?>
                                    <span class="badge position-absolute top-0 end-0 m-2" style="background-color:<?php echo htmlspecialchars($prop['status_color'] ?? '#6c757d'); ?>;"><?php echo htmlspecialchars($prop['status_name']); ?></span>
                                </div>
                                <div class="card-body">
                                    <h5 class="text-success fw-bold mb-1">$<?php echo number_format($prop['price']); ?><?php echo (isset($prop['status_name']) && stripos($prop['status_name'], 'Rent') !== false) ? '<span class="fs-6 text-muted">/mo</span>' : ''; ?></h5>
                                    <h6 class="card-title"><?php echo htmlspecialchars($prop['title']); ?></h6>
                                    <p class="card-text text-muted small"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars(customerPropAddress($prop)); ?></p>
                                    <div class="d-flex justify-content-between text-muted small border-top pt-2">
                                        <span><i class="fas fa-bed text-success me-1"></i><?php echo $prop['bedrooms']; ?> Beds</span>
                                        <span><i class="fas fa-bath text-success me-1"></i><?php echo $prop['bathrooms']; ?> Baths</span>
                                        <span><i class="fas fa-vector-square text-success me-1"></i><?php echo number_format($prop['sqft']); ?> sqft</span>
                                    </div>
                                </div>
                                <a href="<?php echo $base; ?>/customer/property-detail.php?id=<?php echo $prop['id']; ?>" class="stretched-link"></a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <?php if ($total_pages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                        <li class="page-item"><a class="page-link" href="<?php echo update_qs(['page' => $page - 1], $base); ?>">Previous</a></li>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>"><a class="page-link" href="<?php echo update_qs(['page' => $i], $base); ?>"><?php echo $i; ?></a></li>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?>
                        <li class="page-item"><a class="page-link" href="<?php echo update_qs(['page' => $page + 1], $base); ?>">Next</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
function update_qs($replace, $base) {
    $params = $_GET;
    foreach ($replace as $k => $v) $params[$k] = $v;
    $qs = http_build_query($params);
    return $base . '/customer/properties.php' . ($qs ? '?' . $qs : '');
}
?>

<?php include_once __DIR__ . '/footer.php'; ?>