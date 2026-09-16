<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get profile
$stmt = $conn->prepare("SELECT * FROM runner_profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

if (!$profile) {
    header('Location: profile.php');
    exit;
}

$terrain = $profile['terrain_preference'];
$cushion = $profile['cushioning_preference'];
$pronation = $profile['pronation_type'];
$mileage = $profile['weekly_mileage'];
$goals = $profile['race_goals'];

// Fetch ALL active shoes (not just exact match)
$result = $conn->query("SELECT * FROM shoes WHERE is_active = 1");

$scored = [];
while ($shoe = $result->fetch_assoc()) {
    $score = 0;
    $match_reasons = [];
    
    // Terrain match: exact = 50 points, partial (mixed) = 25, else 0
    if ($shoe['terrain_suitability'] === $terrain) {
        $score += 50;
        $match_reasons[] = "Terrain: " . ucfirst($terrain) . " (exact match)";
    } elseif ($shoe['terrain_suitability'] === 'mixed' || $terrain === 'mixed') {
        $score += 25;
        $match_reasons[] = "Terrain: " . ucfirst($shoe['terrain_suitability']) . " (good for mixed)";
    } else {
        $match_reasons[] = "Terrain: " . ucfirst($shoe['terrain_suitability']) . " (not your primary terrain)";
    }
    
    // Cushioning match: exact = 40, close (moderate vs max/min) = 20
    if ($shoe['cushioning_level'] === $cushion) {
        $score += 40;
        $match_reasons[] = "Cushioning: " . ucfirst($cushion) . " (exact match)";
    } elseif (($cushion === 'moderate' && in_array($shoe['cushioning_level'], ['minimal','maximum'])) ||
              ($cushion === 'minimal' && $shoe['cushioning_level'] === 'moderate') ||
              ($cushion === 'maximum' && $shoe['cushioning_level'] === 'moderate')) {
        $score += 20;
        $match_reasons[] = "Cushioning: " . ucfirst($shoe['cushioning_level']) . " (close match)";
    } else {
        $match_reasons[] = "Cushioning: " . ucfirst($shoe['cushioning_level']) . " (different preference)";
    }
    
    // Pronation bonus
    if ($pronation === 'overpronation' && $shoe['stability_rating'] >= 4) {
        $score += 10;
        $match_reasons[] = "Stability rating " . $shoe['stability_rating'] . "/5 (good for overpronation)";
    }
    
    // Mileage bonus (lightweight for high mileage)
    if ($mileage === '40_plus' && $shoe['weight_grams'] < 230) {
        $score += 10;
        $match_reasons[] = "Weight: " . $shoe['weight_grams'] . "g (lightweight for high mileage)";
    }
    
    // Race goals bonus
    if (in_array($goals, ['half_marathon','full_marathon','ultra']) && $shoe['flexibility_rating'] >= 4) {
        $score += 10;
        $match_reasons[] = "Flexibility: " . $shoe['flexibility_rating'] . "/5 (good for race distance)";
    }
    
    // Add a small random tie-breaker
    $score += mt_rand(1, 5) / 100;
    
    $scored[] = [
        'shoe' => $shoe,
        'score' => $score,
        'reasons' => $match_reasons
    ];
}

// Sort by score descending
usort($scored, function($a, $b) {
    return $b['score'] <=> $a['score'];
});

// Take top 3 (or fewer if less than 3)
$matches = array_slice($scored, 0, 3);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Shoe Recommendations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body style="background: linear-gradient(135deg, #f5f7fa, #c3cfe2); min-height:100vh;">

<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container">
        <a class="navbar-brand" href="index.php">🏃 <span>Enda</span> Platform</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="text-center animate-fade-in">
        <h2 class="section-title">👟 Your <span>Perfect Match</span></h2>
        <p class="text-muted">Based on your running profile, here are your top recommendations.</p>
        
        <!-- Show profile summary -->
        <div class="card-modern d-inline-block text-start" style="max-width:500px; margin:0 auto;">
            <h6>📊 Your Profile</h6>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge bg-primary"><?= ucfirst($terrain) ?> Terrain</span>
                <span class="badge bg-success"><?= ucfirst($cushion) ?> Cushioning</span>
                <span class="badge bg-warning text-dark"><?= ucfirst($pronation) ?> Pronation</span>
                <span class="badge bg-info text-dark"><?= str_replace('_', '-', $mileage) ?> Mileage</span>
                <span class="badge bg-secondary"><?= str_replace('_', ' ', $goals) ?></span>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-3">
        <?php foreach ($matches as $index => $match): 
            $shoe = $match['shoe'];
            $score = round($match['score'], 0);
            $reasons = $match['reasons'];
            $colors = ['#ff6b6b', '#f39c12', '#2ecc71'];
            $bgColors = ['#ffe6e6', '#fff5e6', '#e6ffe6'];
            $delay = 'delay-' . ($index + 1);
        ?>
            <div class="col-md-4 animate-fade-in <?= $delay ?>">
                <div class="card-modern" style="border: 3px solid <?= $colors[$index] ?>; position:relative; overflow:hidden;">
                    <div style="position:absolute; top:-30px; right:-30px; width:100px; height:100px; background:<?= $colors[$index] ?>; opacity:0.1; border-radius:50%;"></div>
                    <div style="position:absolute; bottom:-20px; left:-20px; width:80px; height:80px; background:<?= $colors[$index] ?>; opacity:0.08; border-radius:50%;"></div>
                    
                    <div style="position:absolute; top:10px; right:10px;">
                        <span class="badge" style="background:<?= $colors[$index] ?>; color:#fff; font-size:0.9rem; padding:0.4rem 0.8rem;">
                            #<?= $index + 1 ?> Match
                        </span>
                    </div>
                    
                    <div class="mt-2">
                        <h3 class="card-title" style="color:<?= $colors[$index] ?>; font-weight:800; font-size:1.8rem;">
                            <?= htmlspecialchars($shoe['name']) ?>
                        </h3>
                        
                        <div class="progress mb-3" style="height:10px; border-radius:10px;">
                            <div class="progress-bar" role="progressbar" style="width: <?= $score ?>%; background:<?= $colors[$index] ?>; border-radius:10px;" 
                                 aria-valuenow="<?= $score ?>" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">Match Score</small>
                            <strong style="color:<?= $colors[$index] ?>;"><?= $score ?>%</strong>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <div class="d-flex gap-3 mb-2">
                            <span class="badge bg-light text-dark"><?= ucfirst($shoe['terrain_suitability']) ?></span>
                            <span class="badge bg-light text-dark"><?= ucfirst($shoe['cushioning_level']) ?></span>
                        </div>
                        <div class="d-flex gap-3">
                            <div><small class="text-muted">Stability</small><br><strong><?= $shoe['stability_rating'] ?>/5</strong></div>
                            <div><small class="text-muted">Flexibility</small><br><strong><?= $shoe['flexibility_rating'] ?>/5</strong></div>
                            <div><small class="text-muted">Weight</small><br><strong><?= $shoe['weight_grams'] ?>g</strong></div>
                        </div>
                    </div>
                    
                    <div class="mt-3 p-2" style="background:<?= $bgColors[$index] ?>; border-radius:8px;">
                        <small><strong>✅ Why this shoe:</strong></small><br>
                        <?php foreach ($reasons as $reason): ?>
                            <small style="color:#555;">• <?= $reason ?></small><br>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if ($shoe['enda_url']): ?>
                        <a href="<?= htmlspecialchars($shoe['enda_url']) ?>" target="_blank" class="btn btn-gradient w-100 mt-3">
                            👟 View on Enda.co →
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="text-center mt-4">
        <a href="profile.php" class="btn btn-outline-gradient">Update Profile</a>
        <a href="dashboard.php" class="btn btn-gradient">Back to Dashboard</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>