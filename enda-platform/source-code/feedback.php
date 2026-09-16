<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

// If no event_id, show all feedback the user has submitted
if ($event_id === 0) {
    $feedback_list = $conn->query("
        SELECT f.*, e.event_name, s.name as shoe_name 
        FROM feedback f
        JOIN events e ON f.event_id = e.event_id
        LEFT JOIN shoes s ON f.shoe_id = s.shoe_id
        WHERE f.user_id = $user_id
        ORDER BY f.submitted_at DESC
    ");
    ?>
    <!DOCTYPE html>
    <html>
    <head><title>My Feedback</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
    <body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>⭐ My Feedback</h2>
            <a href="dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
        </div>
        <?php if ($feedback_list->num_rows == 0): ?>
            <div class="alert alert-info">You haven't submitted any feedback yet. Attend events and leave your feedback!</div>
        <?php else: while ($row = $feedback_list->fetch_assoc()): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5><?= htmlspecialchars($row['event_name']) ?></h5>
                    <p>
                        <strong>Shoe:</strong> <?= htmlspecialchars($row['shoe_name'] ?? 'Not specified') ?><br>
                        <strong>Comfort:</strong> <?= $row['comfort_rating'] ?>/5 &nbsp;
                        <strong>Performance:</strong> <?= $row['performance_rating'] ?>/5 &nbsp;
                        <strong>Durability:</strong> <?= $row['durability_rating'] ?>/5
                    </p>
                    <?php if ($row['comment']): ?>
                        <p class="mb-0"><em>"<?= htmlspecialchars($row['comment']) ?>"</em></p>
                    <?php endif; ?>
                    <small class="text-muted">Submitted: <?= $row['submitted_at'] ?></small>
                </div>
            </div>
        <?php endwhile; endif; ?>
        <a href="events.php" class="btn btn-primary">View Events</a>
    </div>
    </body>
    </html>
    <?php
    exit;
}

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shoe_id = !empty($_POST['shoe_id']) ? (int)$_POST['shoe_id'] : null;
    $comfort = (int)$_POST['comfort'];
    $performance = (int)$_POST['performance'];
    $durability = (int)$_POST['durability'];
    $comment = $_POST['comment'] ?? '';

    // Check if feedback already exists
    $check = $conn->prepare("SELECT feedback_id FROM feedback WHERE event_id = ? AND user_id = ?");
    $check->bind_param("ii", $event_id, $user_id);
    $check->execute();
    $existing = $check->get_result()->fetch_assoc();

    if ($existing) {
        // Update existing feedback
        $stmt = $conn->prepare("UPDATE feedback SET shoe_id=?, comfort_rating=?, performance_rating=?, durability_rating=?, comment=? WHERE event_id=? AND user_id=?");
        $stmt->bind_param("iiissii", $shoe_id, $comfort, $performance, $durability, $comment, $event_id, $user_id);
    } else {
        // Insert new feedback
        $stmt = $conn->prepare("INSERT INTO feedback (event_id, user_id, shoe_id, comfort_rating, performance_rating, durability_rating, comment) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiiiss", $event_id, $user_id, $shoe_id, $comfort, $performance, $durability, $comment);
    }
    $stmt->execute();
    header('Location: feedback.php?success=1');
    exit;
}

// Fetch event details
$stmt = $conn->prepare("SELECT * FROM events WHERE event_id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
if (!$event) {
    die("Event not found.");
}

// Check if user has already submitted feedback
$stmt = $conn->prepare("SELECT * FROM feedback WHERE event_id = ? AND user_id = ?");
$stmt->bind_param("ii", $event_id, $user_id);
$stmt->execute();
$existing_feedback = $stmt->get_result()->fetch_assoc();

// Fetch shoe list
$shoes = $conn->query("SELECT shoe_id, name FROM shoes WHERE is_active=1");
?>
<!DOCTYPE html>
<html>
<head><title>Feedback</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5" style="max-width:600px;">
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">✅ Feedback submitted successfully! Thank you.</div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>⭐ Feedback for <?= htmlspecialchars($event['event_name']) ?></h2>
        <a href="events.php" class="btn btn-outline-secondary btn-sm">Back to Events</a>
    </div>
    <p><strong>Date:</strong> <?= $event['event_date'] ?> at <?= $event['event_time'] ?: 'TBD' ?></p>
    <p><strong>Location:</strong> <?= htmlspecialchars($event['location']) ?></p>

    <?php if ($existing_feedback): ?>
        <div class="alert alert-info">
            <h5>You've already submitted feedback for this event.</h5>
            <p>Update your feedback below:</p>
        </div>
    <?php endif; ?>

    <form method="post" class="mt-4">
        <div class="mb-3">
            <label>Which shoe did you wear?</label>
            <select name="shoe_id" class="form-select">
                <option value="">Other / Non-Enda shoe</option>
                <?php while($s = $shoes->fetch_assoc()): ?>
                    <option value="<?= $s['shoe_id'] ?>" <?= ($existing_feedback['shoe_id'] ?? '') == $s['shoe_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label>Comfort Rating (1-5)</label>
            <input type="number" name="comfort" class="form-control" min="1" max="5" required 
                   value="<?= $existing_feedback['comfort_rating'] ?? '' ?>">
        </div>
        <div class="mb-3">
            <label>Performance Rating (1-5)</label>
            <input type="number" name="performance" class="form-control" min="1" max="5" required
                   value="<?= $existing_feedback['performance_rating'] ?? '' ?>">
        </div>
        <div class="mb-3">
            <label>Durability Rating (1-5)</label>
            <input type="number" name="durability" class="form-control" min="1" max="5" required
                   value="<?= $existing_feedback['durability_rating'] ?? '' ?>">
        </div>
        <div class="mb-3">
            <label>Comments (optional)</label>
            <textarea name="comment" class="form-control" rows="3"><?= htmlspecialchars($existing_feedback['comment'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary"><?= $existing_feedback ? 'Update Feedback' : 'Submit Feedback' ?></button>
        <a href="events.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
    <script src="script.js"></script>
</body>
</html>