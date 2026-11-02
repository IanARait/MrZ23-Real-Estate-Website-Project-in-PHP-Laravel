<?php
$page_title = 'Our Agents - HomeHub';
require_once __DIR__ . '/../includes/functions.php';
include_once __DIR__ . '/header.php';

$base = 'http://' . $_SERVER['HTTP_HOST'] . '/E_COMMERCE/real-estate';

try {
    $pdo = getConnection();

    $where = [];
    $params = [];

    if (!empty($_GET['search'])) {
        $where[] = "(a.full_name LIKE :search OR a.email LIKE :search2 OR a.phone LIKE :search3)";
        $s = '%' . $_GET['search'] . '%';
        $params['search'] = $s;
        $params['search2'] = $s;
        $params['search3'] = $s;
    }
    if (!empty($_GET['specialty'])) {
        $where[] = "a.specialties LIKE :specialty";
        $params['specialty'] = '%' . $_GET['specialty'] . '%';
    }

    $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    $stmt = $pdo->prepare("SELECT a.*, (SELECT COUNT(*) FROM properties WHERE agent_id = a.id) as property_count FROM agents a $where_clause ORDER BY property_count DESC");
    $stmt->execute($params);
    $agents = $stmt->fetchAll();

    $specialties_stmt = $pdo->query("SELECT DISTINCT specialties FROM agents WHERE specialties IS NOT NULL AND specialties != ''");
    $specialties_list = [];
    while ($row = $specialties_stmt->fetch()) {
        $parts = explode(',', $row['specialties']);
        foreach ($parts as $p) {
            $t = trim($p);
            if ($t && !in_array($t, $specialties_list)) $specialties_list[] = $t;
        }
    }
    sort($specialties_list);

} catch (PDOException $e) {
    $agents = [];
    $specialties_list = [];
}
?>

<div class="container py-4">
    <div class="text-center mb-5">
        <h1 class="fw-bold">Our Agents</h1>
        <p class="text-muted">Meet our professional team of real estate experts</p>
    </div>

    <!-- Search -->
    <div class="row justify-content-center mb-5">
        <div class="col-md-8">
            <form method="GET" class="row g-2">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-success"></i></span>
                        <input type="text" class="form-control" name="search" placeholder="Search by name, email, or phone" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="specialty">
                        <option value="">All Specialties</option>
                        <?php foreach ($specialties_list as $s): ?>
                        <option value="<?php echo htmlspecialchars($s); ?>" <?php echo (isset($_GET['specialty']) && $_GET['specialty'] === $s) ? 'selected' : ''; ?>><?php echo htmlspecialchars($s); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success w-100"><i class="fas fa-filter me-1"></i>Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Agents Grid -->
    <div class="row g-4">
        <?php if (empty($agents)): ?>
            <?php $sample_agents = [
                ['id' => 1, 'name' => 'Sarah Johnson', 'title' => 'Senior Real Estate Agent', 'phone' => '+1 (555) 234-5678', 'email' => 'sarah@homehub.com', 'experience' => 8, 'count' => 24, 'specialties' => 'Residential, Luxury Homes'],
                ['id' => 2, 'name' => 'Michael Chen', 'title' => 'Luxury Property Specialist', 'phone' => '+1 (555) 345-6789', 'email' => 'michael@homehub.com', 'experience' => 12, 'count' => 18, 'specialties' => 'Luxury, Commercial'],
                ['id' => 3, 'name' => 'Emily Rodriguez', 'title' => 'Commercial Real Estate Expert', 'phone' => '+1 (555) 456-7890', 'email' => 'emily@homehub.com', 'experience' => 6, 'count' => 15, 'specialties' => 'Commercial, Industrial'],
                ['id' => 4, 'name' => 'David Thompson', 'title' => 'Residential Specialist', 'phone' => '+1 (555) 567-8901', 'email' => 'david@homehub.com', 'experience' => 10, 'count' => 20, 'specialties' => 'Residential, First-Time Buyers'],
                ['id' => 5, 'name' => 'Jessica Williams', 'title' => 'Property Management Expert', 'phone' => '+1 (555) 678-9012', 'email' => 'jessica@homehub.com', 'experience' => 7, 'count' => 12, 'specialties' => 'Property Management, Rentals'],
                ['id' => 6, 'name' => 'Robert Martinez', 'title' => 'Investment Property Specialist', 'phone' => '+1 (555) 789-0123', 'email' => 'robert@homehub.com', 'experience' => 15, 'count' => 30, 'specialties' => 'Investment, Commercial'],
            ];
            foreach ($sample_agents as $agent): ?>
            <div class="col-lg-4 col-md-6">
                <div class="agent-card card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <div class="agent-img mx-auto mb-3">
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white fs-2" style="width:110px;height:110px;">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                        <h5 class="fw-bold mb-1"><?php echo $agent['name']; ?></h5>
                        <p class="text-success small mb-2"><?php echo $agent['title']; ?></p>
                        <p class="text-muted small mb-1"><i class="fas fa-phone me-1"></i><?php echo $agent['phone']; ?></p>
                        <p class="text-muted small mb-2"><i class="fas fa-envelope me-1"></i><?php echo $agent['email']; ?></p>
                        <?php if ($agent['specialties']): ?>
                        <p class="text-muted small mb-2"><i class="fas fa-star me-1"></i><?php echo $agent['specialties']; ?></p>
                        <?php endif; ?>
                        <div class="d-flex justify-content-center gap-2 mt-2 flex-wrap">
                            <span class="badge bg-success"><?php echo $agent['count']; ?> Properties</span>
                            <span class="badge bg-info"><?php echo $agent['experience']; ?> Years Exp.</span>
                        </div>
                        <div class="d-flex justify-content-center gap-3 mt-3">
                            <a href="#" class="text-muted"><i class="fab fa-facebook"></i></a>
                            <a href="#" class="text-muted"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="text-muted"><i class="fab fa-linkedin"></i></a>
                            <a href="#" class="text-muted"><i class="fab fa-instagram"></i></a>
                        </div>
                        <a href="<?php echo $base; ?>/customer/agent-detail.php?id=<?php echo $agent['id']; ?>" class="btn btn-outline-success btn-sm mt-3 w-100">View Profile</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <?php foreach ($agents as $agent): ?>
            <div class="col-lg-4 col-md-6">
                <div class="agent-card card border-0 shadow-sm h-100">
                    <div class="card-body text-center p-4">
                        <div class="agent-img mx-auto mb-3">
                            <?php if ($agent['photo'] && file_exists($_SERVER['DOCUMENT_ROOT'] . '/E_COMMERCE/real-estate/uploads/agents/' . $agent['photo'])): ?>
                                <img src="<?php echo $base; ?>/uploads/agents/<?php echo htmlspecialchars($agent['photo']); ?>" alt="<?php echo htmlspecialchars($agent['full_name']); ?>" class="rounded-circle" style="width:110px;height:110px;object-fit:cover;">
                            <?php else: ?>
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white fs-2" style="width:110px;height:110px;"><i class="fas fa-user"></i></div>
                            <?php endif; ?>
                        </div>
                        <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($agent['full_name']); ?></h5>
                        <p class="text-success small mb-2"><?php echo htmlspecialchars(trim($agent['specialties'] ?? '') ?: 'Real Estate Agent'); ?></p>
                        <p class="text-muted small mb-1"><i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($agent['phone']); ?></p>
                        <p class="text-muted small mb-2"><i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($agent['email']); ?></p>
                        <?php if (!empty($agent['specialties'])): ?>
                        <p class="text-muted small mb-2"><i class="fas fa-star me-1"></i><?php echo htmlspecialchars($agent['specialties']); ?></p>
                        <?php endif; ?>
                        <div class="d-flex justify-content-center gap-2 mt-2 flex-wrap">
                            <span class="badge bg-success"><?php echo $agent['property_count']; ?> Properties</span>
                            <?php if ($agent['experience_years']): ?><span class="badge bg-info"><?php echo $agent['experience_years']; ?> Years Exp.</span><?php endif; ?>
                        </div>
                        <div class="d-flex justify-content-center gap-3 mt-3">
                            <?php if ($agent['facebook']): ?><a href="<?php echo htmlspecialchars($agent['facebook']); ?>" target="_blank" class="text-muted"><i class="fab fa-facebook"></i></a><?php endif; ?>
                            <?php if ($agent['twitter']): ?><a href="<?php echo htmlspecialchars($agent['twitter']); ?>" target="_blank" class="text-muted"><i class="fab fa-twitter"></i></a><?php endif; ?>
                            <?php if ($agent['linkedin']): ?><a href="<?php echo htmlspecialchars($agent['linkedin']); ?>" target="_blank" class="text-muted"><i class="fab fa-linkedin"></i></a><?php endif; ?>
                            <?php if ($agent['instagram']): ?><a href="<?php echo htmlspecialchars($agent['instagram']); ?>" target="_blank" class="text-muted"><i class="fab fa-instagram"></i></a><?php endif; ?>
                        </div>
                        <a href="<?php echo $base; ?>/customer/agent-detail.php?id=<?php echo $agent['id']; ?>" class="btn btn-outline-success btn-sm mt-3 w-100">View Profile</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include_once __DIR__ . '/footer.php'; ?>
