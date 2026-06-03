<?php
session_start();
include 'config/db.php';
include 'includes/header.php';
?>

<div class="container mt-5 mb-5">
    <div class="text-center mb-5 py-4">
        <span class="badge bg-success-subtle text-success mb-2 px-3 py-2 text-uppercase font-monospace" style="font-size: 0.7rem;">Verified Exchange Locations</span>
        <h1 class="fw-bold text-dark">Safe Meet-up Zones</h1>
        <p class="text-muted">For your physical protection, all handovers must happen at these high-visibility campus landmarks.</p>
    </div>

    <div class="row g-4">
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100 rounded-3">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <span class="p-2 bg-danger-subtle text-danger rounded-3 me-3"><i class="bi bi-geo-alt-fill h4 mb-0"></i></span>
                        <div>
                            <h5 class="fw-bold mb-0">Loftus / Hatfield Area</h5>
                            <small class="text-muted">Main Campus Walkway</small>
                        </div>
                    </div>
                    <p class="small text-muted mb-0">High-traffic public corridor covered thoroughly by campus surveillance cameras. Ideal for daytime textbook inspects.</p>
                    <hr class="text-muted opacity-20">
                    <span class="badge bg-light text-success border border-success-subtle"><i class="bi bi-camera-video me-1"></i> CCTV Monitored</span>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100 rounded-3">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <span class="p-2 bg-danger-subtle text-danger rounded-3 me-3"><i class="bi bi-geo-alt-fill h4 mb-0"></i></span>
                        <div>
                            <h5 class="fw-bold mb-0">Eduvos Pretoria</h5>
                            <small class="text-muted">Student Plaza Fountain</small>
                        </div>
                    </div>
                    <p class="small text-muted mb-0">The open plaza right outside the central blocks. Highly populated during lunch hours and change of classes.</p>
                    <hr class="text-muted opacity-20">
                    <span class="badge bg-light text-success border border-success-subtle"><i class="bi bi-brightness-high me-1"></i> Highly Lit Zone</span>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100 rounded-3">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <span class="p-2 bg-danger-subtle text-danger rounded-3 me-3"><i class="bi bi-geo-alt-fill h4 mb-0"></i></span>
                        <div>
                            <h5 class="fw-bold mb-0">Witbank Hub</h5>
                            <small class="text-muted">Library Square Benches</small>
                        </div>
                    </div>
                    <p class="small text-muted mb-0">The wide courtyard directly adjacent to the library gates. Security personnel are actively stationed nearby.</p>
                    <hr class="text-muted opacity-20">
                    <span class="badge bg-light text-success border border-success-subtle"><i class="bi bi-shield-fill-check me-1"></i> Security Nearby</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm p-4 bg-light rounded-3 mt-5">
        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-exclamation-circle-fill text-purple me-2" style="color: #6f42c1;"></i>Trading Rules for Students:</h6>
        <ul class="small text-muted ps-3 mb-0">
            <li class="mb-2">Never agree to meet a trader late at night or in empty parking lots.</li>
            <li class="mb-2">Bring a friend along with you to the Safe Meet-up Zone if you can.</li>
            <li>Do not hand over your item until the buyer completes the verification step on their mobile web panel.</li>
        </ul>
    </div>
</div>

</body>
</html>