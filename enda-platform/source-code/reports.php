<?php
session_start();
require_once 'config/db.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

// Most recommended shoes
$mostRecommended = $conn->query("
    SELECT s.name, COUNT(f.shoe_id) AS cnt 
    FROM feedback f 
    JOIN shoes s ON f.shoe_id = s.shoe_id 
    WHERE f.shoe_id IS NOT NULL
    GROUP BY s.shoe_id 
    ORDER BY cnt DESC 
    LIMIT 5
");

// Average ratings by shoe
$avgRatings = $conn->query("
    SELECT s.name, 
           AVG(f.comfort_rating) AS comfort,
           AVG(f.performance_rating) AS perf,
           AVG(f.durability_rating) AS dura
    FROM feedback f 
    JOIN shoes s ON f.shoe_id = s.shoe_id 
    WHERE f.shoe_id IS NOT NULL
    GROUP BY s.shoe_id
");

// Terrain distribution
$terrainDist = $conn->query("
    SELECT terrain_preference, COUNT(*) AS cnt 
    FROM runner_profiles 
    GROUP BY terrain_preference
");

// Summary stats
$stats = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM users WHERE role = 'runner') AS runners,
        (SELECT COUNT(*) FROM events) AS events,
        (SELECT COUNT(*) FROM feedback) AS feedbacks,
        (SELECT COUNT(*) FROM shoes WHERE is_active = 1) AS active_shoes
")->fetch_assoc();

// Prepare data for charts
$rec_labels = [];
$rec_data = [];
while ($row = $mostRecommended->fetch_assoc()) {
    $rec_labels[] = $row['name'];
    $rec_data[] = $row['cnt'];
}

$terrain_labels = [];
$terrain_data = [];
while ($row = $terrainDist->fetch_assoc()) {
    $terrain_labels[] = ucfirst($row['terrain_preference']);
    $terrain_data[] = $row['cnt'];
}

$rating_labels = [];
$rating_comfort = [];
$rating_perf = [];
$rating_dura = [];
while ($row = $avgRatings->fetch_assoc()) {
    $rating_labels[] = $row['name'];
    $rating_comfort[] = round($row['comfort'], 1);
    $rating_perf[] = round($row['perf'], 1);
    $rating_dura[] = round($row['dura'], 1);
}
?>
<!DOCTYPE html>
<html>
<head><title>Summary Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📊 Summary Reports</h2>
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center bg-primary text-white">
                <div class="card-body">
                    <h5>Total Runners</h5>
                    <h2><?= $stats['runners'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center bg-success text-white">
                <div class="card-body">
                    <h5>Total Events</h5>
                    <h2><?= $stats['events'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center bg-warning text-white">
                <div class="card-body">
                    <h5>Total Feedback</h5>
                    <h2><?= $stats['feedbacks'] ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center bg-info text-white">
                <div class="card-body">
                    <h5>Active Shoes</h5>
                    <h2><?= $stats['active_shoes'] ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5>Most Recommended Shoes</h5>
                </div>
                <div class="card-body">
                    <canvas id="mostRecommended" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5>Runner Terrain Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="terrainChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Average Ratings by Shoe</h5>
                </div>
                <div class="card-body">
                    <canvas id="avgRatingsChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Most Recommended Shoes
new Chart(document.getElementById('mostRecommended'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($rec_labels) ?>,
        datasets: [{
            label: 'Recommendations',
            data: <?= json_encode($rec_data) ?>,
            backgroundColor: '#36a2eb'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        }
    }
});

// Terrain Distribution
new Chart(document.getElementById('terrainChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($terrain_labels) ?>,
        datasets: [{
            data: <?= json_encode($terrain_data) ?>,
            backgroundColor: ['#ff6384', '#36a2eb', '#ffce56', '#4bc0c0']
        }]
    }
});

// Average Ratings by Shoe
new Chart(document.getElementById('avgRatingsChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($rating_labels) ?>,
        datasets: [
            {
                label: 'Comfort',
                data: <?= json_encode($rating_comfort) ?>,
                backgroundColor: '#ff6384'
            },
            {
                label: 'Performance',
                data: <?= json_encode($rating_perf) ?>,
                backgroundColor: '#36a2eb'
            },
            {
                label: 'Durability',
                data: <?= json_encode($rating_dura) ?>,
                backgroundColor: '#ffce56'
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' }
        },
        scales: {
            y: {
                min: 0,
                max: 5
            }
        }
    }
});
    <script src="script.js"></script>
</script>
</body>
</html>