/**
 * HomeHub Customer JS
 */

document.addEventListener('DOMContentLoaded', function () {

    // ===== Image Gallery =====
    window.setMainImage = function (src, thumb) {
        const main = document.getElementById('mainImage');
        if (main) {
            main.src = src;
            document.querySelectorAll('.thumb-img').forEach(function (t) {
                t.classList.remove('border-success');
            });
            thumb.classList.add('border-success');
        }
    };

    // ===== Save Property =====
    const saveBtns = document.querySelectorAll('.save-property-btn');
    saveBtns.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const propertyId = this.getAttribute('data-property');
            if (!propertyId) return;

            const formData = new FormData();
            formData.append('property_id', propertyId);

            fetch('../ajax/save-property.php', {
                method: 'POST',
                body: formData
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.success) {
                        if (data.saved) {
                            btn.classList.remove('btn-outline-success');
                            btn.classList.add('btn-success');
                            btn.innerHTML = '<i class="fas fa-heart me-1"></i> Saved';
                        } else {
                            btn.classList.remove('btn-success');
                            btn.classList.add('btn-outline-success');
                            btn.innerHTML = '<i class="fas fa-heart me-1"></i> Save Property';
                            if (btn.closest('.property-card')) {
                                btn.closest('.property-card').parentElement.remove();
                            }
                        }
                        showToast(data.message, 'success');
                    } else {
                        if (data.message.indexOf('login') > -1) {
                            window.location.href = 'login.php';
                        } else {
                            showToast(data.message, 'danger');
                        }
                    }
                })
                .catch(function () {
                    showToast('An error occurred. Please try again.', 'danger');
                });
        });
    });

    // ===== Inquiry Form =====
    const inquiryForms = document.querySelectorAll('#inquiryForm');
    inquiryForms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const responseEl = document.getElementById('inquiryResponse');
            if (!responseEl) return;

            const formData = new FormData(this);

            fetch('../ajax/inquiry.php', {
                method: 'POST',
                body: formData
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.success) {
                        responseEl.innerHTML = '<div class="alert alert-success py-2 small">' + data.message + '</div>';
                        form.reset();
                    } else {
                        responseEl.innerHTML = '<div class="alert alert-danger py-2 small">' + data.message + '</div>';
                    }
                })
                .catch(function () {
                    responseEl.innerHTML = '<div class="alert alert-danger py-2 small">An error occurred.</div>';
                });
        });
    });

    // ===== Map View Toggle =====
    const mapBtn = document.getElementById('mapViewBtn');
    const mapView = document.getElementById('mapView');
    if (mapBtn && mapView) {
        mapBtn.addEventListener('click', function () {
            if (mapView.style.display === 'none') {
                mapView.style.display = 'block';
                this.classList.add('active');
                this.innerHTML = '<i class="fas fa-th-list me-1"></i> Grid View';
            } else {
                mapView.style.display = 'none';
                this.classList.remove('active');
                this.innerHTML = '<i class="fas fa-map-marked-alt"></i>';
            }
        });
    }

    // ===== Virtual Tour Button =====
    const tourBtn = document.getElementById('virtualTourBtn');
    if (tourBtn) {
        tourBtn.addEventListener('click', function (e) {
            const url = this.getAttribute('href');
            if (url && url !== '#') {
                window.open(url, '_blank');
                e.preventDefault();
            }
        });
    }

    // ===== Newsletter Form =====
    const newsletterForm = document.getElementById('newsletterForm');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            if (email) {
                showToast('Thank you for subscribing!', 'success');
                this.reset();
            }
        });
    }

    // ===== Sort Select Helper =====
    window.updateQueryString = function (key, value) {
        const url = new URL(window.location.href);
        url.searchParams.set(key, value);
        url.searchParams.delete('page');
        return url.toString();
    };

    // ===== Toast Notification =====
    function showToast(message, type) {
        const toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            const container = document.createElement('div');
            container.id = 'toastContainer';
            container.style.cssText = 'position:fixed;top:80px;right:20px;z-index:9999;max-width:350px;';
            document.body.appendChild(container);
        }

        const colors = {
            success: '#0d6e2d',
            danger: '#dc3545',
            warning: '#ffc107',
            info: '#0dcaf0'
        };

        const toast = document.createElement('div');
        toast.style.cssText = 'background:' + (colors[type] || '#333') + ';color:#fff;padding:12px 20px;border-radius:10px;margin-bottom:10px;box-shadow:0 4px 15px rgba(0,0,0,0.15);animation:slideInRight 0.3s ease;font-size:14px;';
        toast.textContent = message;

        const container = document.getElementById('toastContainer') || document.body;
        container.appendChild(toast);

        setTimeout(function () {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s';
            setTimeout(function () { toast.remove(); }, 300);
        }, 3500);
    }

    // Inject toast animation
    const style = document.createElement('style');
    style.textContent = '@keyframes slideInRight{from{transform:translateX(100px);opacity:0}to{transform:translateX(0);opacity:1}}';
    document.head.appendChild(style);

    // ===== AJAX Filtering (for filter sidebar) =====
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        const inputs = filterForm.querySelectorAll('input, select');
        inputs.forEach(function (input) {
            input.addEventListener('change', function () {
                // Auto-submit on change for select and checkbox
                if (input.tagName === 'SELECT') {
                    filterForm.submit();
                }
            });
        });
    }

    // ===== AJAX Search (for hero search) =====
    const heroSearchForm = document.querySelector('.hero-search');
    if (heroSearchForm) {
        heroSearchForm.addEventListener('submit', function (e) {
            // Normal form submission, no prevent
        });
    }

});
