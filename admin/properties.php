<?php
require_once 'header.php';

$pdo = getConnection();
$msg = '';
$error = '';

$types = getPropertyTypes(null);
$statuses = getStatuses(null);
$agents = $pdo->query("SELECT id, full_name FROM agents WHERE status = 1 ORDER BY full_name")->fetchAll();
$features_list = getFeatures(null);

// Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("SELECT featured_image FROM properties WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $prop = $stmt->fetch();
    if ($prop && $prop['featured_image']) {
        $imgPath = __DIR__ . '/../uploads/properties/' . $prop['featured_image'];
        if (file_exists($imgPath)) unlink($imgPath);
    }
    $stmt = $pdo->prepare("DELETE FROM properties WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $msg = 'Property deleted successfully.';
}

// Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $id = $_POST['id'] ?? null;
    $title = sanitize($_POST['title']);
    $slug = sanitize($_POST['slug']) ?: generateSlug($title);
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? 0;
    $property_type_id = $_POST['property_type_id'] ?: null;
    $status_id = $_POST['status_id'] ?: 1;
    $bedrooms = intval($_POST['bedrooms'] ?? 0);
    $bathrooms = intval($_POST['bathrooms'] ?? 0);
    $sqft = $_POST['sqft'] ?? 0;
    $lot_size = $_POST['lot_size'] ?? 0;
    $year_built = $_POST['year_built'] ?: null;
    $address_line1 = sanitize($_POST['address_line1'] ?? '');
    $address_line2 = sanitize($_POST['address_line2'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $state = sanitize($_POST['state'] ?? '');
    $zip_code = sanitize($_POST['zip_code'] ?? '');
    $latitude = $_POST['latitude'] ?: null;
    $longitude = $_POST['longitude'] ?: null;
    $virtual_tour_url = sanitize($_POST['virtual_tour_url'] ?? '');
    $video_url = sanitize($_POST['video_url'] ?? '');
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $agent_id = $_POST['agent_id'] ?: null;
    $listing_date = $_POST['listing_date'] ?: null;

    $uploadDir = __DIR__ . '/../uploads/properties/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $featured_image = $_POST['existing_image'] ?? '';

    if (!empty($_FILES['featured_image']['name'])) {
        $uploaded = uploadImage($_FILES['featured_image'], $uploadDir, 'prop');
        if ($uploaded) {
            if ($featured_image && file_exists($uploadDir . $featured_image)) unlink($uploadDir . $featured_image);
            $featured_image = $uploaded;
        }
    }

    if ($id) {
        $stmt = $pdo->prepare("UPDATE properties SET title=?, slug=?, description=?, price=?, property_type_id=?, status_id=?, bedrooms=?, bathrooms=?, sqft=?, lot_size=?, year_built=?, address_line1=?, address_line2=?, city=?, state=?, zip_code=?, latitude=?, longitude=?, featured_image=?, virtual_tour_url=?, video_url=?, is_featured=?, agent_id=?, listing_date=? WHERE id=?");
        $stmt->execute([$title, $slug, $description, $price, $property_type_id, $status_id, $bedrooms, $bathrooms, $sqft, $lot_size, $year_built, $address_line1, $address_line2, $city, $state, $zip_code, $latitude, $longitude, $featured_image, $virtual_tour_url, $video_url, $is_featured, $agent_id, $listing_date, $id]);
        $msg = 'Property updated successfully.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO properties (title, slug, description, price, property_type_id, status_id, bedrooms, bathrooms, sqft, lot_size, year_built, address_line1, address_line2, city, state, zip_code, latitude, longitude, featured_image, virtual_tour_url, video_url, is_featured, agent_id, listing_date) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$title, $slug, $description, $price, $property_type_id, $status_id, $bedrooms, $bathrooms, $sqft, $lot_size, $year_built, $address_line1, $address_line2, $city, $state, $zip_code, $latitude, $longitude, $featured_image, $virtual_tour_url, $video_url, $is_featured, $agent_id, $listing_date]);
        $id = $pdo->lastInsertId();
        $msg = 'Property created successfully.';
    }

    // Gallery uploads
    if (!empty($_FILES['gallery']['name'][0])) {
        $galleryImages = [];
        if ($id && $_POST['existing_gallery']) {
            $galleryImages = explode(',', $_POST['existing_gallery']);
        }
        foreach ($_FILES['gallery']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['gallery']['error'][$key] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $_FILES['gallery']['name'][$key],
                    'type' => $_FILES['gallery']['type'][$key],
                    'tmp_name' => $tmp_name,
                    'error' => $_FILES['gallery']['error'][$key],
                    'size' => $_FILES['gallery']['size'][$key],
                ];
                $uploaded = uploadImage($file, $uploadDir, 'gal');
                if ($uploaded) {
                    $galleryImages[] = $uploaded;
                    $stmt = $pdo->prepare("INSERT INTO property_images (property_id, image) VALUES (?,?)");
                    $stmt->execute([$id, $uploaded]);
                }
            }
        }
    }

    // Features
    if ($id && isset($_POST['features'])) {
        $pdo->prepare("DELETE FROM property_features WHERE property_id = ?")->execute([$id]);
        foreach ($_POST['features'] as $fid) {
            $val = $_POST['feature_value_' . $fid] ?? '';
            $stmt = $pdo->prepare("INSERT INTO property_features (property_id, feature_id, value) VALUES (?,?,?)");
            $stmt->execute([$id, $fid, $val]);
        }
    }

    redirect('properties.php');
}

$editProp = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editProp = $stmt->fetch();
    if (!$editProp) { $error = 'Property not found.'; }
}

// Filters
$filter_type = $_GET['filter_type'] ?? '';
$filter_status = $_GET['filter_status'] ?? '';
$filter_city = $_GET['filter_city'] ?? '';
$search = $_GET['search'] ?? '';

$where = ["p.status = 1"];
$binds = [];
if ($filter_type) { $where[] = "p.property_type_id = :ft"; $binds[':ft'] = $filter_type; }
if ($filter_status) { $where[] = "p.status_id = :fs"; $binds[':fs'] = $filter_status; }
if ($filter_city) { $where[] = "p.city LIKE :fc"; $binds[':fc'] = "%$filter_city%"; }
if ($search) { $where[] = "(p.title LIKE :s OR p.city LIKE :s2)"; $binds[':s'] = "%$search%"; $binds[':s2'] = "%$search%"; }

$sql = "SELECT p.*, pt.name AS type_name, ps.name AS status_name, ps.color AS status_color, a.full_name AS agent_name FROM properties p LEFT JOIN property_types pt ON p.property_type_id = pt.id LEFT JOIN property_statuses ps ON p.status_id = ps.id LEFT JOIN agents a ON p.agent_id = a.id WHERE " . implode(' AND ', $where) . " ORDER BY p.created_at DESC";
$props = $pdo->prepare($sql);
$props->execute($binds);
$properties = $props->fetchAll();

$cities = $pdo->query("SELECT DISTINCT city FROM properties WHERE status = 1 AND city IS NOT NULL ORDER BY city")->fetchAll(PDO::FETCH_COLUMN);
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= $editProp ? 'Edit Property' : 'Properties' ?></h1>
    <div>
        <button class="btn btn-outline-primary btn-sm me-2" onclick="toggleMap()"><i class="bi bi-geo-alt"></i> Map</button>
        <a href="properties.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-list"></i> All</a>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#propertyModal"><i class="bi bi-plus-lg"></i> Add Property</button>
    </div>
</div>

<?php if ($msg): ?><div class="alert alert-success py-2"><?= $msg ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger py-2"><?= $error ?></div><?php endif; ?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="get" class="row g-2">
            <div class="col-md-3">
                <select name="filter_type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    <?php foreach ($types as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= $filter_type == $t['id'] ? 'selected' : '' ?>><?= sanitize($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="filter_status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <?php foreach ($statuses as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $filter_status == $s['id'] ? 'selected' : '' ?>><?= sanitize($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="filter_city" class="form-select form-select-sm">
                    <option value="">All Cities</option>
                    <?php foreach ($cities as $c): ?>
                    <option value="<?= sanitize($c) ?>" <?= $filter_city == $c ? 'selected' : '' ?>><?= sanitize($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." value="<?= sanitize($search) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="propertiesTable" class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px">Image</th>
                        <th>Title</th>
                        <th>Price</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Beds</th>
                        <th>Baths</th>
                        <th>Featured</th>
                        <th style="width:120px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($properties as $p): ?>
                    <tr>
                        <td>
                            <?php if ($p['featured_image']): ?>
                            <img src="../uploads/properties/<?= sanitize($p['featured_image']) ?>" style="width:50px;height:40px;object-fit:cover;border-radius:4px;" alt="">
                            <?php else: ?>
                            <div style="width:50px;height:40px;background:#e9ecef;border-radius:4px;display:flex;align-items:center;justify-content:center;"><i class="bi bi-building text-muted"></i></div>
                            <?php endif; ?>
                        </td>
                        <td><a href="#" class="text-decoration-none fw-medium"><?= truncate(sanitize($p['title']), 40) ?></a><br><small class="text-muted"><?= sanitize($p['city']) ?><?= $p['city'] ? ', ' : '' ?><?= sanitize($p['state']) ?></small></td>
                        <td><?= formatPrice($p['price']) ?></td>
                        <td><?= sanitize($p['type_name'] ?? 'N/A') ?></td>
                        <td><?= getStatusBadge(sanitize($p['status_name']), $p['status_color']) ?></td>
                        <td><?= $p['bedrooms'] ?></td>
                        <td><?= $p['bathrooms'] ?></td>
                        <td><?= $p['is_featured'] ? '<i class="bi bi-star-fill text-warning"></i>' : '-' ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary edit-btn" data-id="<?= $p['id'] ?>" title="Edit"><i class="bi bi-pencil"></i></button>
                            <a href="?delete=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this property?')" title="Delete"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($properties)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">No properties found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Map View Modal -->
<div class="modal fade" id="mapModal" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Property Map</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div id="map" style="height:450px;background:#f5f5f5;display:flex;align-items:center;justify-content:center;color:#999;">
                <span>Map loading... (Google Maps API key required)</span>
            </div>
        </div>
    </div></div>
</div>

<!-- Property Modal -->
<div class="modal fade" id="propertyModal" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl"><div class="modal-content">
        <form method="post" enctype="multipart/form-data" id="propertyForm">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Property</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="prop_id" value="">
                <input type="hidden" name="existing_image" id="existing_image" value="">
                <input type="hidden" name="existing_gallery" id="existing_gallery" value="">
                <input type="hidden" name="save" value="1">

                <ul class="nav nav-tabs mb-3" id="propTabs" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#basic">Basic Info</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#details">Details</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#address">Address</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#media">Media</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#features_tab">Features</button></li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="basic">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Title *</label>
                                <input type="text" name="title" id="prop_title" class="form-control" required onkeyup="document.getElementById('prop_slug').value = generateSlug(this.value)">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Slug</label>
                                <input type="text" name="slug" id="prop_slug" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" id="prop_description" class="form-control" rows="4"></textarea>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Price *</label>
                                <input type="number" step="0.01" name="price" id="prop_price" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Property Type</label>
                                <select name="property_type_id" id="prop_type" class="form-select">
                                    <option value="">Select Type</option>
                                    <?php foreach ($types as $t): ?>
                                    <option value="<?= $t['id'] ?>"><?= sanitize($t['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status_id" id="prop_status" class="form-select">
                                    <?php foreach ($statuses as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= sanitize($s['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Listing Date</label>
                                <input type="date" name="listing_date" id="prop_listing_date" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="details">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Bedrooms</label>
                                <input type="number" name="bedrooms" id="prop_bedrooms" class="form-control" min="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Bathrooms</label>
                                <input type="number" name="bathrooms" id="prop_bathrooms" class="form-control" min="0" step="0.5">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Sq. Ft.</label>
                                <input type="number" step="0.01" name="sqft" id="prop_sqft" class="form-control" min="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Lot Size</label>
                                <input type="number" step="0.01" name="lot_size" id="prop_lot_size" class="form-control" min="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Year Built</label>
                                <input type="number" name="year_built" id="prop_year_built" class="form-control" min="1800" max="<?= date('Y') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Agent</label>
                                <select name="agent_id" id="prop_agent" class="form-select">
                                    <option value="">Select Agent</option>
                                    <?php foreach ($agents as $a): ?>
                                    <option value="<?= $a['id'] ?>"><?= sanitize($a['full_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="form-check mt-2">
                                    <input type="checkbox" name="is_featured" id="prop_featured" class="form-check-input" value="1">
                                    <label class="form-check-label" for="prop_featured">Featured Property</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="address">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Address Line 1</label>
                                <input type="text" name="address_line1" id="prop_addr1" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Address Line 2</label>
                                <input type="text" name="address_line2" id="prop_addr2" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">City</label>
                                <input type="text" name="city" id="prop_city" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">State</label>
                                <input type="text" name="state" id="prop_state" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Zip Code</label>
                                <input type="text" name="zip_code" id="prop_zip" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Latitude</label>
                                <input type="text" step="any" name="latitude" id="prop_lat" class="form-control" placeholder="e.g. 34.052235">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Longitude</label>
                                <input type="text" step="any" name="longitude" id="prop_lng" class="form-control" placeholder="e.g. -118.243683">
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="media">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Featured Image</label>
                                <input type="file" name="featured_image" class="form-control" accept="image/*">
                                <div id="image_preview" class="mt-2"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gallery Images</label>
                                <input type="file" name="gallery[]" class="form-control" multiple accept="image/*">
                                <div id="gallery_preview" class="mt-2 d-flex flex-wrap gap-2"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Virtual Tour URL</label>
                                <input type="url" name="virtual_tour_url" id="prop_vtour" class="form-control" placeholder="https://">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Video URL</label>
                                <input type="url" name="video_url" id="prop_video" class="form-control" placeholder="https://">
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="features_tab">
                        <div class="row g-3">
                            <?php
                            $cats = [];
                            foreach ($features_list as $f) {
                                $cats[$f['category'] ?? 'General'][] = $f;
                            }
                            ?>
                            <?php foreach ($cats as $cat => $feats): ?>
                            <div class="col-md-6">
                                <h6 class="text-muted border-bottom pb-2"><?= sanitize($cat) ?></h6>
                                <?php foreach ($feats as $f): ?>
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="features[]" value="<?= $f['id'] ?>" class="form-check-input feature-check" id="feat_<?= $f['id'] ?>">
                                    <label class="form-check-label" for="feat_<?= $f['id'] ?>">
                                        <?php if ($f['icon']): ?><i class="bi bi-<?= sanitize($f['icon']) ?> me-1"></i><?php endif; ?>
                                        <?= sanitize($f['name']) ?>
                                    </label>
                                    <input type="text" name="feature_value_<?= $f['id'] ?>" class="form-control form-control-sm mt-1" placeholder="Value (optional)" style="display:none" id="feat_val_<?= $f['id'] ?>">
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Property</button>
            </div>
        </form>
    </div></div>
</div>

<script>
function generateSlug(str) {
    return str.toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
}

function toggleMap() {
    new bootstrap.Modal(document.getElementById('mapModal')).show();
}

document.querySelectorAll('.feature-check').forEach(cb => {
    cb.addEventListener('change', function() {
        document.getElementById('feat_val_' + this.value).style.display = this.checked ? 'block' : 'none';
    });
});

document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        fetch('properties_get.php?id=' + id)
            .then(r => r.json())
            .then(data => {
                if (data.error) { alert(data.error); return; }
                document.getElementById('modalTitle').textContent = 'Edit Property';
                document.getElementById('prop_id').value = data.id;
                document.getElementById('prop_title').value = data.title;
                document.getElementById('prop_slug').value = data.slug;
                document.getElementById('prop_description').value = data.description || '';
                document.getElementById('prop_price').value = data.price;
                document.getElementById('prop_type').value = data.property_type_id || '';
                document.getElementById('prop_status').value = data.status_id;
                document.getElementById('prop_bedrooms').value = data.bedrooms;
                document.getElementById('prop_bathrooms').value = data.bathrooms;
                document.getElementById('prop_sqft').value = data.sqft;
                document.getElementById('prop_lot_size').value = data.lot_size;
                document.getElementById('prop_year_built').value = data.year_built || '';
                document.getElementById('prop_agent').value = data.agent_id || '';
                document.getElementById('prop_featured').checked = data.is_featured == 1;
                document.getElementById('prop_addr1').value = data.address_line1 || '';
                document.getElementById('prop_addr2').value = data.address_line2 || '';
                document.getElementById('prop_city').value = data.city || '';
                document.getElementById('prop_state').value = data.state || '';
                document.getElementById('prop_zip').value = data.zip_code || '';
                document.getElementById('prop_lat').value = data.latitude || '';
                document.getElementById('prop_lng').value = data.longitude || '';
                document.getElementById('prop_vtour').value = data.virtual_tour_url || '';
                document.getElementById('prop_video').value = data.video_url || '';
                document.getElementById('prop_listing_date').value = data.listing_date || '';
                document.getElementById('existing_image').value = data.featured_image || '';

                if (data.featured_image) {
                    document.getElementById('image_preview').innerHTML = '<img src="../uploads/properties/' + data.featured_image + '" style="height:80px;border-radius:4px;">';
                } else {
                    document.getElementById('image_preview').innerHTML = '';
                }

                document.querySelectorAll('.feature-check').forEach(cb => cb.checked = false);
                document.querySelectorAll('[id^=feat_val_]').forEach(el => { el.style.display = 'none'; el.value = ''; });

                if (data.features) {
                    data.features.forEach(function(f) {
                        const cb = document.getElementById('feat_' + f.feature_id);
                        if (cb) {
                            cb.checked = true;
                            const valEl = document.getElementById('feat_val_' + f.feature_id);
                            if (valEl) { valEl.style.display = 'block'; valEl.value = f.value || ''; }
                        }
                    });
                }

                new bootstrap.Modal(document.getElementById('propertyModal')).show();
            });
    });
});

// Reset modal on close
document.getElementById('propertyModal').addEventListener('hidden.bs.modal', function() {
    if (!document.getElementById('prop_id').value) {
        document.getElementById('propertyForm').reset();
        document.getElementById('image_preview').innerHTML = '';
        document.getElementById('gallery_preview').innerHTML = '';
        document.querySelectorAll('[id^=feat_val_]').forEach(el => { el.style.display = 'none'; });
    }
    document.getElementById('modalTitle').textContent = 'Add Property';
});
</script>

<?php require_once 'footer.php'; ?>
