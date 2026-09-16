<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$role = $_SESSION['role'];
if ($role !== 'leader' && $role !== 'admin') {
    die("Access denied. You must be a leader or admin.");
}

$user_id = $_SESSION['user_id'];

// Get list of clubs (only one for now)
$clubs = $conn->query("SELECT club_id, name FROM run_clubs WHERE is_active = 1");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $club_id = (int)$_POST['club_id'];
    $event_name = $_POST['event_name'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'] ?: null;
    $location = $_POST['location'];
    $notes = $_POST['notes'] ?: null;

    $stmt = $conn->prepare("INSERT INTO events (club_id, created_by, event_name, event_date, event_time, location, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisssss", $club_id, $user_id, $event_name, $event_date, $event_time, $location, $notes);
    if ($stmt->execute()) {
        $success = "Event created successfully!";
    } else {
        $error = "Error creating event: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Create Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5" style="max-width:600px;">
    <h2>📝 Create New Run Club Event</h2>
    <?php if (isset($success)): ?>
        <div class="alert alert-success">✅ <?= $success ?> <a href="dashboard.php">Go to Dashboard</a></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label>Club</label>
            <select name="club_id" class="form-select" required>
                <?php while ($club = $clubs->fetch_assoc()): ?>
                    <option value="<?= $club['club_id'] ?>"><?= htmlspecialchars($club['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label>Event Name</label>
            <input type="text" name="event_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Event Date</label>
            <input type="date" name="event_date" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Event Time (optional)</label>
            <input type="time" name="event_time" class="form-control">
        </div>
        <div class="mb-3">
            <label>Location</label>
            <input type="text" name="location" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Notes (optional)</label>
            <textarea name="notes" class="form-control" rows="3"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Create Event</button>
        <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
    <script src="script.js"></script>
</body>
</html>