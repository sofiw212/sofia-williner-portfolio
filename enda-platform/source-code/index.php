<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enda Community Runner Intelligence</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container">
        <a class="navbar-brand" href="index.php">🏃 <span>Enda</span> Platform</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7 animate-fade-in">
                <h1>Find Your Perfect <span>Running Shoe</span></h1>
                <p>Enda's community-driven platform matches you with the ideal shoe based on your running style, terrain, and goals.</p>
                <div class="d-flex gap-3 flex-wrap">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="register.php" class="btn btn-primary">🚀 Get Started</a>
                        <a href="login.php" class="btn btn-outline-light">Log In</a>
                    <?php else: ?>
                        <a href="dashboard.php" class="btn btn-primary">📊 Go to Dashboard</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-5 text-center animate-fade-in delay-2">
                <img src="https://enda.co/cdn/shop/files/Enda_Logo_Black.png?height=200&v=1614762009" alt="Enda" class="img-fluid" style="max-height:200px; filter: brightness(0) invert(1); opacity:0.9;">
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-12">
                <h2 class="section-title">Why <span>Enda</span>?</h2>
                <p class="text-muted">The smartest way to find your perfect running shoe.</p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-md-4 animate-fade-in delay-1">
                <div class="card-modern text-center">
                    <div style="font-size:3rem;">👟</div>
                    <h5 class="card-title mt-3">Smart Matching</h5>
                    <p class="card-text">Our algorithm matches you with shoes based on your running profile.</p>
                </div>
            </div>
            <div class="col-md-4 animate-fade-in delay-2">
                <div class="card-modern text-center">
                    <div style="font-size:3rem;">📅</div>
                    <h5 class="card-title mt-3">Run Club Events</h5>
                    <p class="card-text">RSVP to SCTY Run Club events and connect with the community.</p>
                </div>
            </div>
            <div class="col-md-4 animate-fade-in delay-3">
                <div class="card-modern text-center">
                    <div style="font-size:3rem;">⭐</div>
                    <h5 class="card-title mt-3">Feedback & Insights</h5>
                    <p class="card-text">Share your experience and help others find their perfect shoe.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5>🏃 Enda Platform</h5>
                <p>Community Runner Intelligence &amp; Shoe Match Platform</p>
            </div>
            <div class="col-md-3">
                <h5>Quick Links</h5>
                <a href="register.php">Register</a><br>
                <a href="login.php">Login</a>
            </div>
            <div class="col-md-3">
                <h5>Contact</h5>
                <a href="#">SCTY Run Club</a><br>
                <a href="#">Nairobi, Kenya</a>
            </div>
        </div>
        <hr class="border-light opacity-25">
        <div class="text-center">
            <small>&copy; 2026 Enda Platform. All rights reserved.</small>
        </div>
    </div>
</footer>
<script src="script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>