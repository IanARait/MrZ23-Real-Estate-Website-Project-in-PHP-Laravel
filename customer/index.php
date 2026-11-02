<?php
$page_title = 'HomeHub - Find Your Dream Home';
require_once __DIR__ . '/../includes/functions.php';
include_once __DIR__ . '/header.php';

$base = 'http://' . $_SERVER['HTTP_HOST'] . '/E_COMMERCE/real-estate';

try {
    $pdo = getConnection();

    $featured = $pdo->prepare("SELECT p.*, ps.name AS status_name, ps.color AS status_color, pt.name AS type_name FROM properties p LEFT JOIN property_statuses ps ON p.status_id = ps.id LEFT JOIN property_types pt ON p.property_type_id = pt.id WHERE p.status = 1 AND p.is_featured = 1 ORDER BY p.created_at DESC LIMIT 6");
    $featured->execute();
    $featured_properties = $featured->fetchAll();

    $recent = $pdo->prepare("SELECT p.*, ps.name AS status_name, ps.color AS status_color, pt.name AS type_name FROM properties p LEFT JOIN property_statuses ps ON p.status_id = ps.id LEFT JOIN property_types pt ON p.property_type_id = pt.id WHERE p.status = 1 ORDER BY p.created_at DESC LIMIT 6");
    $recent->execute();
    $recent_properties = $recent->fetchAll();

    $top_agents = $pdo->prepare("SELECT a.*, (SELECT COUNT(*) FROM properties WHERE agent_id = a.id) as property_count FROM agents a WHERE a.status = 1 ORDER BY property_count DESC LIMIT 3");
    $top_agents->execute();
    $agents_list = $top_agents->fetchAll();

} catch (PDOException $e) {
    $featured_properties = [];
    $recent_properties = [];
    $agents_list = [];
}
?>

<!-- Hero Section -->
<section class="hero-section d-flex align-items-center">
    <div class="container text-center text-white">
        <h1 class="display-4 fw-bold mb-3 hero-title">Find, Tour &amp; Book Your Dream Home</h1>
        <p class="lead mb-4 hero-subtitle">Discover the perfect property and schedule a private viewing with our agents in minutes</p>
        <div class="d-flex justify-content-center gap-2 flex-wrap mb-4 hero-trust">
            <span class="badge"><i class="fas fa-calendar-check me-1"></i>Instant Booking</span>
            <span class="badge"><i class="fas fa-clock me-1"></i>Free Viewings</span>
            <span class="badge"><i class="fas fa-shield-alt me-1"></i>Verified Agents</span>
        </div>
        <form action="<?php echo $base; ?>/customer/properties.php" method="GET" class="row g-2 justify-content-center mx-auto hero-search">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-map-marker-alt text-success"></i></span>
                    <input type="text" class="form-control" name="location" placeholder="City, Address or ZIP">
                </div>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="type">
                    <option value="">All Types</option>
                    <option value="House">House</option>
                    <option value="Apartment">Apartment</option>
                    <option value="Condo">Condo</option>
                    <option value="Villa">Villa</option>
                    <option value="Land">Land</option>
                    <option value="Commercial">Commercial</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="min_price">
                    <option value="">Min Price</option>
                    <option value="50000">$50,000</option>
                    <option value="100000">$100,000</option>
                    <option value="200000">$200,000</option>
                    <option value="300000">$300,000</option>
                    <option value="500000">$500,000</option>
                    <option value="1000000">$1,000,000</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="max_price">
                    <option value="">Max Price</option>
                    <option value="100000">$100,000</option>
                    <option value="200000">$200,000</option>
                    <option value="300000">$300,000</option>
                    <option value="500000">$500,000</option>
                    <option value="1000000">$1,000,000</option>
                    <option value="5000000">$5,000,000+</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-success btn-lg w-100"><i class="fas fa-search me-2"></i>Search</button>
            </div>
        </form>
        <div class="mt-4 d-flex justify-content-center gap-4 flex-wrap hero-stats">
            <div><span class="fw-bold fs-4">500+</span><br><small>Properties</small></div>
            <div><span class="fw-bold fs-4">50+</span><br><small>Agents</small></div>
            <div><span class="fw-bold fs-4">200+</span><br><small>Happy Clients</small></div>
            <div><span class="fw-bold fs-4">10+</span><br><small>Years Experience</small></div>
        </div>
    </div>
</section>

<!-- Featured Properties -->
<section class="section-padding">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Featured Properties</h2>
            <p class="text-muted">Hand-picked properties selected just for you</p>
        </div>
        <div class="row g-4">
            <?php if (empty($featured_properties)): ?>
                <?php for ($i = 1; $i <= 6; $i++): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="property-card card border-0 shadow-sm h-100">
                        <div class="position-relative overflow-hidden">
                            <div class="property-img-placeholder bg-secondary d-flex align-items-center justify-content-center" style="height:220px;">
                                <i class="fas fa-image text-muted fs-1"></i>
                            </div>
                            <span class="badge bg-success position-absolute top-0 start-0 m-2">Featured</span>
                            <span class="badge bg-warning text-dark position-absolute top-0 end-0 m-2">For Sale</span>
                        </div>
                        <div class="card-body">
                            <h5 class="text-success fw-bold mb-1">$450,000</h5>
                            <h6 class="card-title">Modern Family Home</h6>
                            <p class="card-text text-muted small"><i class="fas fa-map-marker-alt me-1"></i>123 Main St, New York</p>
                            <div class="d-flex justify-content-between text-muted small border-top pt-2">
                                <span><i class="fas fa-bed text-success me-1"></i>4 Beds</span>
                                <span><i class="fas fa-bath text-success me-1"></i>3 Baths</span>
                                <span><i class="fas fa-vector-square text-success me-1"></i>2,400 sqft</span>
                            </div>
                        </div>
                        <a href="<?php echo $base; ?>/customer/property-detail.php?id=<?php echo $i; ?>" class="stretched-link"></a>
                    </div>
                </div>
                <?php endfor; ?>
            <?php else: ?>
                <?php foreach ($featured_properties as $prop): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="property-card card border-0 shadow-sm h-100">
                        <div class="position-relative overflow-hidden">
                            <?php echo customerPropImageTag($prop, '220px'); ?>
                            <span class="badge bg-success position-absolute top-0 start-0 m-2">Featured</span>
                            <span class="badge position-absolute top-0 end-0 m-2" style="background-color:<?php echo htmlspecialchars($prop['status_color'] ?? '#6c757d'); ?>;"><?php echo htmlspecialchars($prop['status_name']); ?></span>
                        </div>
                        <div class="card-body">
                            <h5 class="text-success fw-bold mb-1">$<?php echo number_format($prop['price']); ?></h5>
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
        <div class="text-center mt-4">
            <a href="<?php echo $base; ?>/customer/properties.php?featured=1" class="btn btn-outline-success">View All Featured</a>
        </div>
    </div>
</section>

<!-- Property Types -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Property Types</h2>
            <p class="text-muted">Browse properties by category</p>
        </div>
        <div class="row g-3 justify-content-center">
            <?php
            $types = [
                ['icon' => 'fa-home', 'label' => 'House', 'slug' => 'house'],
                ['icon' => 'fa-building', 'label' => 'Apartment', 'slug' => 'apartment'],
                ['icon' => 'fa-door-open', 'label' => 'Condo', 'slug' => 'condo'],
                ['icon' => 'fa-warehouse', 'label' => 'Villa', 'slug' => 'villa'],
                ['icon' => 'fa-tree', 'label' => 'Land', 'slug' => 'land'],
                ['icon' => 'fa-store', 'label' => 'Commercial', 'slug' => 'commercial'],
            ];
            foreach ($types as $t):
            ?>
            <div class="col-lg-2 col-md-4 col-6">
                <a href="<?php echo $base; ?>/customer/properties.php?type=<?php echo $t['slug']; ?>" class="text-decoration-none">
                    <div class="type-card card border-0 shadow-sm text-center p-4">
                        <div class="type-icon mb-3">
                            <i class="fas <?php echo $t['icon']; ?> fa-2x text-success"></i>
                        </div>
                        <h6 class="fw-bold mb-1"><?php echo $t['label']; ?></h6>
                        <small class="text-muted">Browse <?php echo strtolower($t['label']); ?> listings</small>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="section-padding">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Why Choose Us</h2>
            <p class="text-muted">We make the difference in your real estate journey</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="choose-card card border-0 shadow-sm text-center p-4 h-100">
                    <div class="choose-icon mx-auto mb-3">
                        <i class="fas fa-user-tie fa-3x text-success"></i>
                    </div>
                    <h5 class="fw-bold">Expert Agents</h5>
                    <p class="text-muted small">Our highly trained agents have years of local market experience to help you find the best deals.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="choose-card card border-0 shadow-sm text-center p-4 h-100">
                    <div class="choose-icon mx-auto mb-3">
                        <i class="fas fa-hand-holding-usd fa-3x text-success"></i>
                    </div>
                    <h5 class="fw-bold">Best Deals</h5>
                    <p class="text-muted small">We negotiate the best prices and terms, ensuring you get the most value for your investment.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="choose-card card border-0 shadow-sm text-center p-4 h-100">
                    <div class="choose-icon mx-auto mb-3">
                        <i class="fas fa-headset fa-3x text-success"></i>
                    </div>
                    <h5 class="fw-bold">Customer Support</h5>
                    <p class="text-muted small">Our dedicated support team is available 24/7 to answer your questions and guide you.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Recent Properties -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0">Recent Properties</h2>
                <p class="text-muted mb-0">Latest listings added</p>
            </div>
            <a href="<?php echo $base; ?>/customer/properties.php" class="btn btn-outline-success">View All</a>
        </div>
        <div class="row g-4">
            <?php if (empty($recent_properties)): ?>
                <?php for ($i = 1; $i <= 6; $i++): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="property-card card border-0 shadow-sm h-100">
                        <div class="position-relative overflow-hidden">
                            <div class="property-img-placeholder bg-secondary d-flex align-items-center justify-content-center" style="height:200px;">
                                <i class="fas fa-image text-muted fs-1"></i>
                            </div>
                            <span class="badge bg-warning text-dark position-absolute top-0 end-0 m-2">For Sale</span>
                        </div>
                        <div class="card-body">
                            <h5 class="text-success fw-bold mb-1">$350,000</h5>
                            <h6 class="card-title">Cozy Apartment</h6>
                            <p class="card-text text-muted small"><i class="fas fa-map-marker-alt me-1"></i>456 Oak Ave, New York</p>
                            <div class="d-flex justify-content-between text-muted small border-top pt-2">
                                <span><i class="fas fa-bed text-success me-1"></i>2 Beds</span>
                                <span><i class="fas fa-bath text-success me-1"></i>2 Baths</span>
                                <span><i class="fas fa-vector-square text-success me-1"></i>1,200 sqft</span>
                            </div>
                        </div>
                        <a href="<?php echo $base; ?>/customer/property-detail.php?id=<?php echo $i; ?>" class="stretched-link"></a>
                    </div>
                </div>
                <?php endfor; ?>
            <?php else: ?>
                <?php foreach ($recent_properties as $prop): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="property-card card border-0 shadow-sm h-100">
                        <div class="position-relative overflow-hidden">
                            <?php echo customerPropImageTag($prop, '200px'); ?>
                            <span class="badge position-absolute top-0 end-0 m-2" style="background-color:<?php echo htmlspecialchars($prop['status_color'] ?? '#6c757d'); ?>;"><?php echo htmlspecialchars($prop['status_name']); ?></span>
                        </div>
                        <div class="card-body">
                            <h5 class="text-success fw-bold mb-1">$<?php echo number_format($prop['price']); ?></h5>
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
    </div>
</section>

<!-- Top Agents -->
<section class="section-padding">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Meet Our Top Agents</h2>
            <p class="text-muted">Professional agents ready to help you</p>
        </div>
        <div class="row g-4 justify-content-center">
            <?php if (empty($agents_list)): ?>
                <?php $sample_agents = [
                    ['name' => 'Sarah Johnson', 'title' => 'Senior Agent', 'phone' => '+1 (555) 234-5678', 'email' => 'sarah@homehub.com', 'count' => 24],
                    ['name' => 'Michael Chen', 'title' => 'Luxury Specialist', 'phone' => '+1 (555) 345-6789', 'email' => 'michael@homehub.com', 'count' => 18],
                    ['name' => 'Emily Rodriguez', 'title' => 'Commercial Expert', 'phone' => '+1 (555) 456-7890', 'email' => 'emily@homehub.com', 'count' => 15],
                ];
                foreach ($sample_agents as $a):
                ?>
                <div class="col-lg-4 col-md-6">
                    <div class="agent-card card border-0 shadow-sm text-center p-4">
                        <div class="agent-img mx-auto mb-3">
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white fs-2" style="width:100px;height:100px;">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                        <h5 class="fw-bold mb-1"><?php echo $a['name']; ?></h5>
                        <p class="text-success small mb-2"><?php echo $a['title']; ?></p>
                        <p class="text-muted small mb-1"><i class="fas fa-phone me-1"></i><?php echo $a['phone']; ?></p>
                        <p class="text-muted small mb-2"><i class="fas fa-envelope me-1"></i><?php echo $a['email']; ?></p>
                        <div class="d-flex justify-content-center gap-3 mt-2">
                            <span class="badge bg-success"><?php echo $a['count']; ?> Properties</span>
                        </div>
                        <a href="<?php echo $base; ?>/customer/agent-detail.php?id=1" class="btn btn-outline-success btn-sm mt-3">View Profile</a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <?php foreach ($agents_list as $agent): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="agent-card card border-0 shadow-sm text-center p-4">
                        <div class="agent-img mx-auto mb-3">
                            <?php if ($agent['photo'] && file_exists($_SERVER['DOCUMENT_ROOT'] . '/E_COMMERCE/real-estate/uploads/agents/' . $agent['photo'])): ?>
                                <img src="<?php echo $base; ?>/uploads/agents/<?php echo htmlspecialchars($agent['photo']); ?>" alt="<?php echo htmlspecialchars($agent['full_name']); ?>" class="rounded-circle" style="width:100px;height:100px;object-fit:cover;">
                            <?php else: ?>
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white fs-2" style="width:100px;height:100px;">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($agent['full_name']); ?></h5>
                        <p class="text-success small mb-2"><?php echo htmlspecialchars(trim($agent['specialties'] ?? '') ?: 'Real Estate Agent'); ?></p>
                        <p class="text-muted small mb-1"><i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($agent['phone']); ?></p>
                        <p class="text-muted small mb-2"><i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($agent['email']); ?></p>
                        <div class="d-flex justify-content-center gap-3 mt-2">
                            <span class="badge bg-success"><?php echo $agent['property_count']; ?> Properties</span>
                        </div>
                        <a href="<?php echo $base; ?>/customer/agent-detail.php?id=<?php echo $agent['id']; ?>" class="btn btn-outline-success btn-sm mt-3">View Profile</a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Newsletter -->
<section class="section-padding clay-cta">
    <div class="container text-center">
        <h2 class="fw-bold mb-2">Book a Viewing Today</h2>
        <p class="mb-4">Let us know when suits you — our agents will confirm your appointment</p>
        <form class="row g-2 justify-content-center mx-auto" style="max-width:500px;" id="newsletterForm">
            <div class="col-8">
                <input type="email" class="form-control form-control-lg" placeholder="Enter your email" required>
            </div>
            <div class="col-4">
                <button type="submit" class="btn btn-success fw-bold btn-lg w-100">Subscribe</button>
            </div>
        </form>
    </div>
</section>

<?php include_once __DIR__ . '/footer.php'; ?>
