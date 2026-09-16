<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle RSVP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id']) && isset($_POST['action'])) {
    $event_id = (int)$_POST['event_id'];
    if ($_POST['action'] === 'rsvp') {
        $stmt = $conn->prepare("INSERT IGNORE INTO rsvps (event_id, user_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $event_id, $user_id);
        $stmt->execute();
    } elseif ($_POST['action'] === 'cancel') {
        $stmt = $conn->prepare("DELETE FROM rsvps WHERE event_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $event_id, $user_id);
        $stmt->execute();
    }
    header('Location: events.php');
    exit;
}

// Fetch upcoming events
$upcoming = $conn->query("
    SELECT e.*, 
        (SELECT COUNT(*) FROM rsvps WHERE event_id = e.event_id) AS attendee_count,
        (SELECT COUNT(*) FROM rsvps WHERE event_id = e.event_id AND user_id = $user_id) AS user_rsvp
    FROM events e 
    WHERE e.event_date >= CURDATE() 
    ORDER BY e.event_date
");

// Fetch past events
$past = $conn->query("
    SELECT e.*, 
        (SELECT COUNT(*) FROM rsvps WHERE event_id = e.event_id) AS attendee_count,
        (SELECT COUNT(*) FROM rsvps WHERE event_id = e.event_id AND user_id = $user_id) AS user_rsvp
    FROM events e 
    WHERE e.event_date < CURDATE() 
    ORDER BY e.event_date DESC
");
?>
<!DOCTYPE html>
<html>
<head><title>Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📅 Run Club Events</h2>
        <a href="dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
    </div>

    <h3>Upcoming Events</h3>
    <?php if ($upcoming->num_rows == 0): ?>
        <div class="alert alert-info">No upcoming events. Check back later!</div>
    <?php else: while ($row = $upcoming->fetch_assoc()): ?>
        <div class="card mb-3">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h5><?= htmlspecialchars($row['event_name']) ?></h5>
                    <p class="mb-1">
                        <strong>📅</strong> <?= $row['event_date'] ?> 
                        <?php if ($row['event_time']): ?> at <?= $row['event_time'] ?><?php endif; ?>
                    </p>
                    <p class="mb-1"><strong>📍</strong> <?= htmlspecialchars($row['location']) ?></p>
                    <span class="badge bg-secondary"><?= $row['attendee_count'] ?> attending</span>
                </div>
                <div>
                    <?php if ($row['user_rsvp'] > 0): ?>
                        <span class="badge bg-success mb-2 d-block">✅ You're Going</span>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="event_id" value="<?= $row['event_id'] ?>">
                            <input type="hidden" name="action" value="cancel">
                            <button class="btn btn-sm btn-outline-danger">Cancel RSVP</button>
                        </form>
                    <?php else: ?>
                        <form method="post" class="d-inline">
                            <input type="hidden" name="event_id" value="<?= $row['event_id'] ?>">
                            <input type="hidden" name="action" value="rsvp">
                            <button class="btn btn-sm btn-primary">RSVP</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endwhile; endif; ?>

    <h3 class="mt-4">Past Events</h3>
    <?php if ($past->num_rows == 0): ?>
        <div class="alert alert-secondary">No past events yet.</div>
    <?php else: while ($row = $past->fetch_assoc()): ?>
        <div class="card mb-2 bg-light">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h6><?= htmlspecialchars($row['event_name']) ?></h6>
                    <p class="mb-0 small"><?= $row['event_date'] ?> - <?= htmlspecialchars($row['location']) ?></p>
                </div>
                <div>
                    <?php if ($row['user_rsvp'] > 0): ?>
                        <a href="feedback.php?event_id=<?= $row['event_id'] ?>" class="btn btn-sm btn-outline-primary">⭐ Leave Feedback</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endwhile; endif; ?>
</div>
    <script src="script.js"></script>
</body>
</html>