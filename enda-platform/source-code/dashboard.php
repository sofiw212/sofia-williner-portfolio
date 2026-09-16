<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$name = $_SESSION['name'];

// Get the user's top shoe match
$stmt = $conn->prepare("
    SELECT s.name, s.enda_url 
    FROM runner_profiles rp
    JOIN shoes s ON s.terrain_suitability = rp.terrain_preference 
        AND s.cushioning_level = rp.cushioning_preference
    WHERE rp.user_id = ? AND s.is_active = 1
    LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$topShoe = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container">
        <a class="navbar-brand" href="index.php">🏃 <span>Enda</span> Platform</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="row">
        <div class="col-12 animate-fade-in">
            <h1 class="section-title">Welcome back, <span><?= htmlspecialchars($name) ?></span> 👋</h1>
            <p class="text-muted">Your running community dashboard.</p>
        </div>
    </div>

    <!-- Top Shoe Match -->
    <div class="row mb-4">
        <div class="col-md-6 animate-fade-in delay-1">
            <div class="card-modern" style="background: linear-gradient(135deg, #fff5e6, #fff0d6);">
                <h5>🏆 Your Top Shoe Match</h5>
                <?php if ($topShoe): ?>
                    <h3 class="mt-2"><?= htmlspecialchars($topShoe['name']) ?></h3>
                    <a href="<?= htmlspecialchars($topShoe['enda_url'] ?? '#') ?>" target="_blank" class="btn btn-gradient mt-2">View on Enda.co</a>
                <?php else: ?>
                    <p class="text-muted">Complete your profile to get shoe recommendations.</p>
                    <a href="profile.php" class="btn btn-gradient">Complete Profile</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-6 animate-fade-in delay-2">
            <div class="card-modern text-center" style="background: linear-gradient(135deg, #e6f0ff, #d6e8ff);">
                <h5>📊 Your Stats</h5>
                <div class="row mt-3">
                    <div class="col-4">
                        <h3>0</h3>
                        <small class="text-muted">Events</small>
                    </div>
                    <div class="col-4">
                        <h3>0</h3>
                        <small class="text-muted">Feedback</small>
                    </div>
                    <div class="col-4">
                        <h3>0</h3>
                        <small class="text-muted">RSVPs</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action Grid -->
    <div class="dashboard-grid">
        <a href="profile.php" class="dashboard-card orange animate-fade-in delay-1">
            <div class="icon">👤</div>
            <h6>My Profile</h6>
        </a>
        <a href="recommendations.php" class="dashboard-card blue animate-fade-in delay-2">
            <div class="icon">👟</div>
            <h6>Shoe Matches</h6>
        </a>
        <a href="events.php" class="dashboard-card green animate-fade-in delay-3">
            <div class="icon">📅</div>
            <h6>Events</h6>
        </a>
        <a href="feedback.php" class="dashboard-card purple animate-fade-in delay-4">
            <div class="icon">⭐</div>
            <h6>Feedback</h6>
        </a>
    </div>

    <!-- Leader Tools -->
    <?php if ($role === 'leader' || $role === 'admin'): ?>
        <div class="mt-5">
            <h4 class="section-title" style="font-size:1.5rem;">🔧 <span>Leader Tools</span></h4>
            <div class="dashboard-grid">
                <a href="create_event.php" class="dashboard-card red animate-fade-in delay-1">
                    <div class="icon">📝</div>
                    <h6>Create Event</h6>
                </a>
                <a href="event_roster.php" class="dashboard-card blue animate-fade-in delay-2">
                    <div class="icon">📋</div>
                    <h6>View Rosters</h6>
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Admin Tools -->
    <?php if ($role === 'admin'): ?>
        <div class="mt-5">
            <h4 class="section-title" style="font-size:1.5rem;">⚙️ <span>Admin Tools</span></h4>
            <div class="dashboard-grid">
                <a href="product_management.php" class="dashboard-card dark animate-fade-in delay-1">
                    <div class="icon">👟</div>
                    <h6>Manage Shoes</h6>
                </a>
                <a href="reports.php" class="dashboard-card dark animate-fade-in delay-2">
                    <div class="icon">📊</div>
                    <h6>Summary Reports</h6>
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>