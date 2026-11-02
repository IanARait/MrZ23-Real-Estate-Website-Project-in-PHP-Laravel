<?php
$page_title = 'Agent Detail - HomeHub';
$hide_search = true;
require_once __DIR__ . '/../includes/functions.php';
include_once __DIR__ . '/header.php';

$base = 'http://' . $_SERVER['HTTP_HOST'] . '/E_COMMERCE/real-estate';
$id = (int)($_GET['id'] ?? 0);

try {
    $pdo = getConnection();

    $stmt = $pdo->prepare("SELECT a.*, (SELECT COUNT(*) FROM properties WHERE agent_id = a.id) as property_count FROM agents a WHERE a.id = :id");
    $stmt->execute(['id' => $id]);
    $agent = $stmt->fetch();

    if (!$agent) {
        echo '<div class="container py-5 text-center"><h3>Agent not found</h3><a href="' . $base . '/customer/agents.php" class="btn btn-success mt-3">Back to Agents</a></div>';
        include_once __DIR__ . '/footer.php';
        exit;
    }

    $props_stmt = $pdo->prepare("SELECT p.*, ps.name AS status_name, ps.color AS status_color, pt.name AS type_name FROM properties p LEFT JOIN property_statuses ps ON p.status_id = ps.id LEFT JOIN property_types pt ON p.property_type_id = pt.id WHERE p.agent_id = :id AND p.status = 1 ORDER BY p.created_at DESC LIMIT 9");
    $props_stmt->execute(['id' => $id]);
    $properties = $props_stmt->fetchAll();

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
            <li class="breadcrumb-item"><a href="<?php echo $base; ?>/customer/agents.php" class="text-success">Agents</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($agent['full_name']); ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Agent Info -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center p-4">
                    <div class="agent-img mx-auto mb-3">
                        <?php if ($agent['photo'] && file_exists($_SERVER['DOCUMENT_ROOT'] . '/E_COMMERCE/real-estate/uploads/agents/' . $agent['photo'])): ?>
                            <img src="<?php echo $base; ?>/uploads/agents/<?php echo htmlspecialchars($agent['photo']); ?>" alt="<?php echo htmlspecialchars($agent['full_name']); ?>" class="rounded-circle" style="width:150px;height:150px;object-fit:cover;">
                        <?php else: ?>
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white fs-1 mx-auto" style="width:150px;height:150px;"><i class="fas fa-user"></i></div>
                        <?php endif; ?>
                    </div>
                    <h4 class="fw-bold"><?php echo htmlspecialchars($agent['full_name']); ?></h4>
                    <p class="text-success mb-2"><?php echo htmlspecialchars(trim($agent['specialties'] ?? '') ?: 'Real Estate Agent'); ?></p>
                    <div class="d-flex justify-content-center gap-2 mb-3 flex-wrap">
                        <span class="badge bg-success fs-6"><?php echo $agent['property_count']; ?> Properties</span>
                        <?php if ($agent['experience_years']): ?>
                        <span class="badge bg-info fs-6"><?php echo $agent['experience_years']; ?> Years Experience</span>
                        <?php endif; ?>
                    </div>
                    <hr>
                    <div class="text-start">
                        <p class="mb-2"><i class="fas fa-phone text-success me-2"></i><?php echo htmlspecialchars($agent['phone']); ?></p>
                        <p class="mb-2"><i class="fas fa-envelope text-success me-2"></i><?php echo htmlspecialchars($agent['email']); ?></p>
                        <?php if (!empty($agent['specialties'])): ?>
                        <p class="mb-2"><i class="fas fa-star text-success me-2"></i><?php echo htmlspecialchars($agent['specialties']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($agent['license_number'])): ?>
                        <p class="mb-2"><i class="fas fa-id-badge text-success me-2"></i>Lic: <?php echo htmlspecialchars($agent['license_number']); ?></p>
                        <?php endif; ?>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-center gap-3">
                        <?php if ($agent['facebook']): ?><a href="<?php echo htmlspecialchars($agent['facebook']); ?>" target="_blank" class="text-muted fs-5"><i class="fab fa-facebook"></i></a><?php endif; ?>
                        <?php if ($agent['twitter']): ?><a href="<?php echo htmlspecialchars($agent['twitter']); ?>" target="_blank" class="text-muted fs-5"><i class="fab fa-twitter"></i></a><?php endif; ?>
                        <?php if ($agent['linkedin']): ?><a href="<?php echo htmlspecialchars($agent['linkedin']); ?>" target="_blank" class="text-muted fs-5"><i class="fab fa-linkedin"></i></a><?php endif; ?>
                        <?php if ($agent['instagram']): ?><a href="<?php echo htmlspecialchars($agent['instagram']); ?>" target="_blank" class="text-muted fs-5"><i class="fab fa-instagram"></i></a><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <!-- Bio -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">About <?php echo htmlspecialchars($agent['full_name']); ?></h5>
                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($agent['bio'] ?? 'No biography available.')); ?></p>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3"><i class="fas fa-calendar-check text-success me-2"></i>Book an Appointment with <?php echo htmlspecialchars($agent['full_name']); ?></h5>
                    <form id="inquiryForm">
                        <input type="hidden" name="agent_id" value="<?php echo $agent['id']; ?>">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="name" placeholder="Your Name" value="<?php echo htmlspecialchars($_SESSION['customer_name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <input type="email" class="form-control" name="email" placeholder="Your Email" required>
                            </div>
                            <div class="col-md-6">
                                <input type="tel" class="form-control" name="phone" placeholder="Your Phone">
                            </div>
                            <div class="col-md-6">
                                <select class="form-select" name="type">
                                    <option value="inquiry">General Inquiry</option>
                                    <option value="showing">Schedule Showing</option>
                                    <option value="offer">Make an Offer</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <textarea class="form-control" name="message" rows="4" placeholder="Your Message" required></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-success"><i class="fas fa-calendar-check me-1"></i>Book Appointment</button>
                            </div>
                        </div>
                    </form>
                    <div id="inquiryResponse" class="mt-2"></div>
                </div>
            </div>

            <!-- Agent's Properties -->
            <h5 class="fw-bold mb-3">Properties by <?php echo htmlspecialchars($agent['full_name']); ?></h5>
            <div class="row g-3">
                <?php if (empty($properties)): ?>
                <div class="col-12 text-center py-4 text-muted">
                    <i class="fas fa-home fa-3x mb-2"></i>
                    <p>No active listings by this agent at the moment.</p>
                </div>
                <?php else: ?>
                    <?php foreach ($properties as $prop): ?>
                    <div class="col-md-4">
                        <div class="property-card card border-0 shadow-sm h-100">
                            <div class="position-relative overflow-hidden">
                                <?php echo customerPropImageTag($prop, '160px'); ?>
                                <span class="badge position-absolute top-0 end-0 m-1" style="background-color:<?php echo htmlspecialchars($prop['status_color'] ?? '#6c757d'); ?>;"><?php echo htmlspecialchars($prop['status_name']); ?></span>
                            </div>
                            <div class="card-body p-2">
                                <h6 class="text-success fw-bold mb-0">$<?php echo number_format($prop['price']); ?></h6>
                                <small class="text-muted"><?php echo htmlspecialchars($prop['title']); ?></small>
                            </div>
                            <a href="<?php echo $base; ?>/customer/property-detail.php?id=<?php echo $prop['id']; ?>" class="stretched-link"></a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/footer.php'; ?>