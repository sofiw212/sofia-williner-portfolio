<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM runner_profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mileage = $_POST['weekly_mileage'];
    $experience = $_POST['experience_level'];
    $terrain = $_POST['terrain_preference'];
    $goals = $_POST['race_goals'];
    $injury = $_POST['injury_history'];
    $pronation = $_POST['pronation_type'];
    $cushion = $_POST['cushioning_preference'];

    if ($profile) {
        $stmt = $conn->prepare("UPDATE runner_profiles SET weekly_mileage=?, experience_level=?, terrain_preference=?, race_goals=?, injury_history=?, pronation_type=?, cushioning_preference=? WHERE user_id=?");
        $stmt->bind_param("sssssssi", $mileage, $experience, $terrain, $goals, $injury, $pronation, $cushion, $user_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO runner_profiles (user_id, weekly_mileage, experience_level, terrain_preference, race_goals, injury_history, pronation_type, cushioning_preference) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssss", $user_id, $mileage, $experience, $terrain, $goals, $injury, $pronation, $cushion);
    }
    $stmt->execute();
    header('Location: recommendations.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Runner Profile</title>
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
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="form-modern animate-fade-in">
                <div class="text-center mb-4">
                    <h2 class="section-title" style="font-size:2rem;">🏃 Your <span>Runner Profile</span></h2>
                    <p class="text-muted">Tell us about yourself so we can recommend the perfect shoe.</p>
                </div>
                <form method="post">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Weekly Mileage</label>
                            <select name="weekly_mileage" class="form-select" required>
                                <option value="under_10" <?= ($profile['weekly_mileage']??'')=='under_10'?'selected':'' ?>>Under 10 mi</option>
                                <option value="10_to_20" <?= ($profile['weekly_mileage']??'')=='10_to_20'?'selected':'' ?>>10-20 mi</option>
                                <option value="20_to_40" <?= ($profile['weekly_mileage']??'')=='20_to_40'?'selected':'' ?>>20-40 mi</option>
                                <option value="40_plus" <?= ($profile['weekly_mileage']??'')=='40_plus'?'selected':'' ?>>40+ mi</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Experience Level</label>
                            <select name="experience_level" class="form-select" required>
                                <option value="beginner" <?= ($profile['experience_level']??'')=='beginner'?'selected':'' ?>>Beginner</option>
                                <option value="intermediate" <?= ($profile['experience_level']??'')=='intermediate'?'selected':'' ?>>Intermediate</option>
                                <option value="advanced" <?= ($profile['experience_level']??'')=='advanced'?'selected':'' ?>>Advanced</option>
                                <option value="elite" <?= ($profile['experience_level']??'')=='elite'?'selected':'' ?>>Elite</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Preferred Terrain</label>
                            <select name="terrain_preference" class="form-select" required>
                                <option value="road" <?= ($profile['terrain_preference']??'')=='road'?'selected':'' ?>>Road</option>
                                <option value="trail" <?= ($profile['terrain_preference']??'')=='trail'?'selected':'' ?>>Trail</option>
                                <option value="track" <?= ($profile['terrain_preference']??'')=='track'?'selected':'' ?>>Track</option>
                                <option value="mixed" <?= ($profile['terrain_preference']??'')=='mixed'?'selected':'' ?>>Mixed</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Race Goals</label>
                            <select name="race_goals" class="form-select" required>
                                <option value="casual" <?= ($profile['race_goals']??'')=='casual'?'selected':'' ?>>No races / casual</option>
                                <option value="5k_10k" <?= ($profile['race_goals']??'')=='5k_10k'?'selected':'' ?>>5K/10K</option>
                                <option value="half_marathon" <?= ($profile['race_goals']??'')=='half_marathon'?'selected':'' ?>>Half marathon</option>
                                <option value="full_marathon" <?= ($profile['race_goals']??'')=='full_marathon'?'selected':'' ?>>Full marathon</option>
                                <option value="ultra" <?= ($profile['race_goals']??'')=='ultra'?'selected':'' ?>>Ultra</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Pronation Type</label>
                            <select name="pronation_type" class="form-select" required>
                                <option value="neutral" <?= ($profile['pronation_type']??'')=='neutral'?'selected':'' ?>>Neutral</option>
                                <option value="overpronation" <?= ($profile['pronation_type']??'')=='overpronation'?'selected':'' ?>>Overpronation</option>
                                <option value="supination" <?= ($profile['pronation_type']??'')=='supination'?'selected':'' ?>>Supination</option>
                                <option value="unknown" <?= ($profile['pronation_type']??'')=='unknown'?'selected':'' ?>>Not sure</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Cushioning Preference</label>
                            <select name="cushioning_preference" class="form-select" required>
                                <option value="minimal" <?= ($profile['cushioning_preference']??'')=='minimal'?'selected':'' ?>>Minimal / Lightweight</option>
                                <option value="moderate" <?= ($profile['cushioning_preference']??'')=='moderate'?'selected':'' ?>>Moderate</option>
                                <option value="maximum" <?= ($profile['cushioning_preference']??'')=='maximum'?'selected':'' ?>>Maximum / Plush</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Injury History (optional)</label>
                        <textarea name="injury_history" class="form-control" rows="2" placeholder="e.g., plantar fasciitis, IT band issues"><?= htmlspecialchars($profile['injury_history']??'') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-gradient w-100">Save Profile & See My Shoes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>