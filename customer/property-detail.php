<?php
$page_title = 'Property Detail - HomeHub';
$hide_search = true;
require_once __DIR__ . '/../includes/functions.php';
include_once __DIR__ . '/header.php';

$base = 'http://' . $_SERVER['HTTP_HOST'] . '/E_COMMERCE/real-estate';
$id = (int)($_GET['id'] ?? 0);

try {
    $pdo = getConnection();

    $stmt = $pdo->prepare("SELECT p.*, pt.name AS type_name, ps.name AS status_name, ps.color AS status_color,
        a.full_name AS agent_name, a.phone AS agent_phone, a.email AS agent_email,
        a.photo AS agent_photo, a.bio AS agent_bio, a.facebook, a.twitter, a.linkedin, a.instagram,
        a.specialties AS agent_specialties
        FROM properties p
        LEFT JOIN property_types pt ON p.property_type_id = pt.id
        LEFT JOIN property_statuses ps ON p.status_id = ps.id
        LEFT JOIN agents a ON p.agent_id = a.id
        WHERE p.id = :id");
    $stmt->execute(['id' => $id]);
    $prop = $stmt->fetch();

    if (!$prop) {
        echo '<div class="container py-5 text-center"><h3>Property not found</h3><a href="' . $base . '/customer/properties.php" class="btn btn-success mt-3">Back to Properties</a></div>';
        include_once __DIR__ . '/footer.php';
        exit;
    }

    $similar = $pdo->prepare("SELECT p.*, ps.name AS status_name, ps.color AS status_color FROM properties p LEFT JOIN property_statuses ps ON p.status_id = ps.id WHERE p.status = 1 AND p.property_type_id = :type AND p.id != :id ORDER BY RAND() LIMIT 3");
    $similar->execute(['type' => $prop['property_type_id'], 'id' => $id]);
    $similar_properties = $similar->fetchAll();

    $gallery_stmt = $pdo->prepare("SELECT image FROM property_images WHERE property_id = :pid ORDER BY sort_order, id");
    $gallery_stmt->execute(['pid' => $id]);
    $property_images = array_column($gallery_stmt->fetchAll(), 'image');
    if (!empty($prop['featured_image'])) {
        array_unshift($property_images, $prop['featured_image']);
    }
    $property_images = array_values(array_unique(array_filter($property_images)));

    $feature_stmt = $pdo->prepare("SELECT f.name, pf.value FROM property_features pf JOIN features f ON pf.feature_id = f.id WHERE pf.property_id = :pid ORDER BY f.name");
    $feature_stmt->execute(['pid' => $id]);
    $feature_rows = $feature_stmt->fetchAll();
    $features = [];
    foreach ($feature_rows as $fr) {
        $features[] = trim($fr['name'] . ($fr['value'] !== '' && $fr['value'] !== null ? ': ' . $fr['value'] : ''));
    }

    $is_saved = false;
    if (isset($_SESSION['customer_id'])) {
        $chk = $pdo->prepare("SELECT id FROM saved_properties WHERE customer_id = :uid AND property_id = :pid");
        $chk->execute(['uid' => $_SESSION['customer_id'], 'pid' => $id]);
        $is_saved = $chk->fetch() ? true : false;
    }

} catch (PDOException $e) {
    echo '<div class="container py-5 text-center"><h3>Database error</h3></div>';
    include_once __DIR__ . '/footer.php';
    exit;
}
?>
<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo $base; ?>/customer/index.php" class="text-success">Home</a></li>
            <li class="breadcrumb-item"><a href="<?php echo $base; ?>/customer/properties.php" class="text-success">Properties</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($prop['title']); ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Image Gallery -->
            <div class="gallery-container mb-4">
                <div class="main-image rounded overflow-hidden mb-2 position-relative">
                    <?php $mainImg = customerPropImage($prop); ?>
                    <?php if ($mainImg): ?>
                        <img id="mainImage" src="<?php echo $base; ?>/<?php echo $mainImg; ?>" alt="<?php echo htmlspecialchars($prop['title']); ?>" class="w-100" style="height:450px;object-fit:cover;">
                    <?php else: ?>
                        <div class="bg-secondary d-flex align-items-center justify-content-center" style="height:450px;"><i class="fas fa-image text-muted fa-3x"></i></div>
                    <?php endif; ?>
                    <span class="badge position-absolute top-0 end-0 m-3 fs-6" style="background-color:<?php echo htmlspecialchars($prop['status_color'] ?? '#6c757d'); ?>;"><?php echo htmlspecialchars($prop['status_name']); ?></span>
                    <?php if ($prop['is_featured']): ?>
                    <span class="badge bg-success position-absolute top-0 start-0 m-3 fs-6">Featured</span>
                    <?php endif; ?>
                </div>
                <?php if (count($property_images) > 1): ?>
                <div class="thumbnails d-flex gap-2 overflow-auto">
                    <?php foreach ($property_images as $i => $img): ?>
                    <?php $path = $_SERVER['DOCUMENT_ROOT'] . '/E_COMMERCE/real-estate/uploads/properties/' . $img; ?>
                    <?php if (file_exists($path)): ?>
                    <img src="<?php echo $base; ?>/uploads/properties/<?php echo htmlspecialchars($img); ?>" alt="Thumbnail" class="thumb-img rounded border <?php echo $i === 0 ? 'border-success' : ''; ?>" style="width:100px;height:75px;object-fit:cover;cursor:pointer;" onclick="setMainImage(this.src, this)">
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Property Info -->
            <h2 class="fw-bold"><?php echo htmlspecialchars($prop['title']); ?></h2>
            <p class="text-muted"><i class="fas fa-map-marker-alt text-success me-1"></i><?php echo htmlspecialchars(customerPropAddress($prop)); ?></p>
            <h3 class="text-success fw-bold">$<?php echo number_format($prop['price']); ?><?php echo (stripos($prop['status_name'], 'Rent') !== false) ? '<span class="fs-6 text-muted">/month</span>' : ''; ?></h3>

            <!-- Key Details -->
            <div class="row g-2 my-4">
                <div class="col-4 col-md-2">
                    <div class="detail-box bg-light rounded p-3 text-center">
                        <i class="fas fa-bed fa-2x text-success mb-1"></i>
                        <h6 class="mb-0 fw-bold"><?php echo $prop['bedrooms']; ?></h6>
                        <small class="text-muted">Bedrooms</small>
                    </div>
                </div>
                <div class="col-4 col-md-2">
                    <div class="detail-box bg-light rounded p-3 text-center">
                        <i class="fas fa-bath fa-2x text-success mb-1"></i>
                        <h6 class="mb-0 fw-bold"><?php echo $prop['bathrooms']; ?></h6>
                        <small class="text-muted">Bathrooms</small>
                    </div>
                </div>
                <div class="col-4 col-md-2">
                    <div class="detail-box bg-light rounded p-3 text-center">
                        <i class="fas fa-vector-square fa-2x text-success mb-1"></i>
                        <h6 class="mb-0 fw-bold"><?php echo number_format($prop['sqft']); ?></h6>
                        <small class="text-muted">Sq Ft</small>
                    </div>
                </div>
                <div class="col-4 col-md-2">
                    <div class="detail-box bg-light rounded p-3 text-center">
                        <i class="fas fa-expand fa-2x text-success mb-1"></i>
                        <h6 class="mb-0 fw-bold"><?php echo $prop['lot_size'] ? number_format($prop['lot_size']) : 'N/A'; ?></h6>
                        <small class="text-muted">Lot Size</small>
                    </div>
                </div>
                <div class="col-4 col-md-2">
                    <div class="detail-box bg-light rounded p-3 text-center">
                        <i class="fas fa-calendar fa-2x text-success mb-1"></i>
                        <h6 class="mb-0 fw-bold"><?php echo $prop['year_built'] ?? 'N/A'; ?></h6>
                        <small class="text-muted">Year Built</small>
                    </div>
                </div>
                <div class="col-4 col-md-2">
                    <div class="detail-box bg-light rounded p-3 text-center">
                        <i class="fas fa-tag fa-2x text-success mb-1"></i>
                        <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($prop['type_name'] ?? 'N/A'); ?></h6>
                        <small class="text-muted">Type</small>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="mb-4">
                <h5 class="fw-bold">Description</h5>
                <p class="text-muted"><?php echo nl2br(htmlspecialchars($prop['description'] ?? 'No description available.')); ?></p>
            </div>

            <!-- Features -->
            <?php if (!empty($features)): ?>
            <div class="mb-4">
                <h5 class="fw-bold">Features & Amenities</h5>
                <div class="row g-2">
                    <?php foreach ($features as $feature): ?>
                    <div class="col-md-4 col-6">
                        <p class="mb-1"><i class="fas fa-check-circle text-success me-2"></i><?php echo htmlspecialchars($feature); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Virtual Tour & Video -->
            <div class="row g-3 mb-4">
                <?php if (!empty($prop['virtual_tour_url'])): ?>
                <div class="col-md-6">
                    <a href="<?php echo htmlspecialchars($prop['virtual_tour_url']); ?>" target="_blank" class="btn btn-success w-100" id="virtualTourBtn"><i class="fas fa-vr-cardboard me-2"></i>Virtual Tour</a>
                </div>
                <?php endif; ?>
                <?php if (!empty($prop['video_url'])): ?>
                <div class="col-md-6">
                    <button class="btn btn-outline-success w-100" data-bs-toggle="modal" data-bs-target="#videoModal"><i class="fas fa-video me-2"></i>Watch Video</button>
                </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($prop['video_url'])): ?>
            <div class="modal fade" id="videoModal" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header border-0">
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-0">
                            <div class="ratio ratio-16x9">
                                <?php
                                $video = $prop['video_url'];
                                if (preg_match('/(?:youtube\.com\/(?:embed\/|watch\?v=)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $video, $m)) {
                                    echo '<iframe src="https://www.youtube.com/embed/' . $m[1] . '" allowfullscreen></iframe>';
                                } elseif (preg_match('/(?:vimeo\.com\/)(\d+)/', $video, $m)) {
                                    echo '<iframe src="https://player.vimeo.com/video/' . $m[1] . '" allowfullscreen></iframe>';
                                } else {
                                    echo '<video src="' . htmlspecialchars($video) . '" controls class="w-100"></video>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Location / Map -->
            <div class="mb-4">
                <h5 class="fw-bold">Location</h5>
                <div class="map-placeholder bg-light rounded d-flex align-items-center justify-content-center" style="height:300px;">
                    <div class="text-center">
                        <i class="fas fa-map-marked-alt fa-4x text-muted mb-2"></i>
                        <p class="text-muted mb-0"><?php echo htmlspecialchars(customerPropAddress($prop)); ?></p>
                        <?php if (!empty($prop['latitude']) && !empty($prop['longitude'])): ?>
                        <small class="text-muted">Lat: <?php echo $prop['latitude']; ?>, Lng: <?php echo $prop['longitude']; ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Similar Properties -->
            <?php if (!empty($similar_properties)): ?>
            <div class="mb-4">
                <h5 class="fw-bold mb-3">Similar Properties</h5>
                <div class="row g-3">
                    <?php foreach ($similar_properties as $sim): ?>
                    <div class="col-md-4">
                        <div class="property-card card border-0 shadow-sm h-100">
                            <div class="position-relative overflow-hidden">
                                <?php echo customerPropImageTag($sim, '160px'); ?>
                                <span class="badge position-absolute top-0 end-0 m-1 small" style="background-color:<?php echo htmlspecialchars($sim['status_color'] ?? '#6c757d'); ?>;"><?php echo htmlspecialchars($sim['status_name']); ?></span>
                            </div>
                            <div class="card-body p-2">
                                <h6 class="text-success fw-bold mb-0">$<?php echo number_format($sim['price']); ?></h6>
                                <small class="text-muted"><?php echo htmlspecialchars($sim['title']); ?></small>
                            </div>
                            <a href="<?php echo $base; ?>/customer/property-detail.php?id=<?php echo $sim['id']; ?>" class="stretched-link"></a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <!-- Agent Info Card -->
            <?php if ($prop['agent_name']): ?>
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body text-center">
                    <h5 class="fw-bold mb-3">Listed By</h5>
                    <div class="agent-img mb-3">
                        <?php if ($prop['agent_photo'] && file_exists($_SERVER['DOCUMENT_ROOT'] . '/E_COMMERCE/real-estate/uploads/agents/' . $prop['agent_photo'])): ?>
                            <img src="<?php echo $base; ?>/uploads/agents/<?php echo htmlspecialchars($prop['agent_photo']); ?>" alt="<?php echo htmlspecialchars($prop['agent_name']); ?>" class="rounded-circle" style="width:100px;height:100px;object-fit:cover;">
                        <?php else: ?>
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white fs-2 mx-auto" style="width:100px;height:100px;"><i class="fas fa-user"></i></div>
                        <?php endif; ?>
                    </div>
                    <h6 class="fw-bold"><?php echo htmlspecialchars($prop['agent_name']); ?></h6>
                    <p class="text-success small"><?php echo htmlspecialchars(trim($prop['agent_specialties'] ?? '') ?: 'Real Estate Agent'); ?></p>
                    <p class="text-muted small mb-1"><i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($prop['agent_phone']); ?></p>
                    <p class="text-muted small mb-3"><i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($prop['agent_email']); ?></p>
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <?php if ($prop['facebook']): ?><a href="<?php echo htmlspecialchars($prop['facebook']); ?>" target="_blank" class="text-muted"><i class="fab fa-facebook"></i></a><?php endif; ?>
                        <?php if ($prop['twitter']): ?><a href="<?php echo htmlspecialchars($prop['twitter']); ?>" target="_blank" class="text-muted"><i class="fab fa-twitter"></i></a><?php endif; ?>
                        <?php if ($prop['linkedin']): ?><a href="<?php echo htmlspecialchars($prop['linkedin']); ?>" target="_blank" class="text-muted"><i class="fab fa-linkedin"></i></a><?php endif; ?>
                        <?php if ($prop['instagram']): ?><a href="<?php echo htmlspecialchars($prop['instagram']); ?>" target="_blank" class="text-muted"><i class="fab fa-instagram"></i></a><?php endif; ?>
                    </div>
                    <a href="<?php echo $base; ?>/customer/agent-detail.php?id=<?php echo $prop['agent_id']; ?>" class="btn btn-outline-success btn-sm w-100">View Full Profile</a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Save & Share -->
            <div class="d-flex gap-2 mb-4">
                <button class="btn <?php echo $is_saved ? 'btn-success' : 'btn-outline-success'; ?> flex-fill save-property-btn" data-property="<?php echo $id; ?>">
                    <i class="fas fa-heart me-1"></i> <?php echo $is_saved ? 'Saved' : 'Save Property'; ?>
                </button>
                <div class="dropdown">
                    <button class="btn btn-outline-success dropdown-toggle" data-bs-toggle="dropdown"><i class="fas fa-share-alt"></i></button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="window.open('https://www.facebook.com/sharer/sharer.php?u='+encodeURIComponent(location.href),'_blank')"><i class="fab fa-facebook me-2"></i>Facebook</a></li>
                        <li><a class="dropdown-item" href="#" onclick="window.open('https://twitter.com/intent/tweet?url='+encodeURIComponent(location.href),'_blank')"><i class="fab fa-twitter me-2"></i>Twitter</a></li>
                        <li><a class="dropdown-item" href="#" onclick="navigator.clipboard.writeText(location.href);alert('Link copied!')"><i class="fas fa-link me-2"></i>Copy Link</a></li>
                    </ul>
                </div>
            </div>

            <!-- Inquiry Form -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3"><i class="fas fa-calendar-check text-success me-2"></i>Book a Viewing / Inquiry</h5>
                    <form id="inquiryForm">
                        <input type="hidden" name="property_id" value="<?php echo $id; ?>">
                        <div class="mb-2">
                            <input type="text" class="form-control form-control-sm" name="name" placeholder="Your Name" value="<?php echo htmlspecialchars($_SESSION['customer_name'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-2">
                            <input type="email" class="form-control form-control-sm" name="email" placeholder="Your Email" required>
                        </div>
                        <div class="mb-2">
                            <input type="tel" class="form-control form-control-sm" name="phone" placeholder="Your Phone">
                        </div>
                        <div class="mb-2">
                            <select class="form-select form-select-sm" name="type">
                                <option value="inquiry">General Inquiry</option>
                                <option value="showing">Schedule Showing</option>
                                <option value="offer">Make an Offer</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <textarea class="form-control form-control-sm" name="message" rows="3" placeholder="Your Message" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100 btn-sm"><i class="fas fa-calendar-check me-1"></i>Book Appointment</button>
                    </form>
                    <div id="inquiryResponse" class="mt-2"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/footer.php'; ?>