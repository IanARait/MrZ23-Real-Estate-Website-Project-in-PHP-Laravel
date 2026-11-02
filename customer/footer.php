<?php
$base = 'http://' . $_SERVER['HTTP_HOST'] . '/E_COMMERCE/real-estate';
?>
</main>

<footer class="site-footer pt-5 pb-3 mt-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <h5 class="fw-bold mb-3"><i class="fas fa-home text-success me-2"></i>HomeHub</h5>
                <p class="footer-text small">HomeHub is your trusted partner in finding the perfect property. With years of experience and a dedicated team of experts, we make real estate simple and enjoyable.</p>
                <div class="d-flex gap-3 mt-3">
                    <a href="#" class="footer-text fs-5"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="footer-text fs-5"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="footer-text fs-5"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="footer-text fs-5"><i class="fab fa-linkedin"></i></a>
                    <a href="#" class="footer-text fs-5"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <h6 class="fw-bold mb-3">Quick Links</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?php echo $base; ?>/customer/properties.php?status=buy" class="footer-text text-decoration-none">Buy</a></li>
                    <li class="mb-2"><a href="<?php echo $base; ?>/customer/properties.php?status=rent" class="footer-text text-decoration-none">Rent</a></li>
                    <li class="mb-2"><a href="<?php echo $base; ?>/customer/properties.php" class="footer-text text-decoration-none">Sell</a></li>
                    <li class="mb-2"><a href="<?php echo $base; ?>/customer/agents.php" class="footer-text text-decoration-none">Agents</a></li>
                    <li class="mb-2"><a href="<?php echo $base; ?>/customer/properties.php?featured=1" class="footer-text text-decoration-none">Featured</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6">
                <h6 class="fw-bold mb-3">Property Types</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="<?php echo $base; ?>/customer/properties.php?type=House" class="footer-text text-decoration-none">Houses</a></li>
                    <li class="mb-2"><a href="<?php echo $base; ?>/customer/properties.php?type=Apartment" class="footer-text text-decoration-none">Apartments</a></li>
                    <li class="mb-2"><a href="<?php echo $base; ?>/customer/properties.php?type=Condo" class="footer-text text-decoration-none">Condos</a></li>
                    <li class="mb-2"><a href="<?php echo $base; ?>/customer/properties.php?type=Villa" class="footer-text text-decoration-none">Villas</a></li>
                    <li class="mb-2"><a href="<?php echo $base; ?>/customer/properties.php?type=Land" class="footer-text text-decoration-none">Land</a></li>
                    <li class="mb-2"><a href="<?php echo $base; ?>/customer/properties.php?type=Commercial" class="footer-text text-decoration-none">Commercial</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6">
                <h6 class="fw-bold mb-3">Contact Info</h6>
                <ul class="list-unstyled small footer-text">
                    <li class="mb-2"><i class="fas fa-map-marker-alt text-success me-2"></i>123 Dream Street, New York, NY 10001</li>
                    <li class="mb-2"><i class="fas fa-phone text-success me-2"></i>+1 (555) 123-4567</li>
                    <li class="mb-2"><i class="fas fa-envelope text-success me-2"></i>info@homehub.com</li>
                    <li class="mb-2"><i class="fas fa-clock text-success me-2"></i>Mon-Sat: 9:00 AM - 7:00 PM</li>
                </ul>
            </div>
        </div>
        <hr class="my-4">
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <p class="small footer-text mb-0">&copy; <?php echo date('Y'); ?> HomeHub. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <a href="#" class="footer-text small text-decoration-none me-3">Privacy Policy</a>
                <a href="#" class="footer-text small text-decoration-none me-3">Terms of Service</a>
                <a href="#" class="footer-text small text-decoration-none">Sitemap</a>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo $base; ?>/assets/js/customer.js"></script>
</body>
</html>
