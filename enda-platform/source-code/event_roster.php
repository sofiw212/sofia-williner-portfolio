<?php
session_start();
require_once 'config/db.php';

// Check if user is logged in and is a leader or admin
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$role = $_SESSION['role'];
if ($role !== 'leader' && $role !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

// Handle attendance status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_attendance'])) {
    $event_id = (int)$_POST['event_id'];
    foreach ($_POST['attendance'] as $rsvp_id => $status) {
        $stmt = $conn->prepare("UPDATE rsvps SET attendance_status = ? WHERE rsvp_id = ?");
        $stmt->bind_param("si", $status, $rsvp_id);
        $stmt->execute();
    }
    $success = "Attendance updated successfully!";
}

// Get all events for dropdown
$events = $conn->query("
    SELECT event_id, event_name, event_date 
    FROM events 
    ORDER BY event_date DESC
");

// Selected event (default to most recent)
$selected_event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
if ($selected_event_id == 0 && $events->num_rows > 0) {
    $first = $events->fetch_assoc();
    $selected_event_id = $first['event_id'];
    $events->data_seek(0); // reset pointer
}

// Fetch roster for selected event
$roster = [];
$event_name = '';
if ($selected_event_id > 0) {
    $stmt = $conn->prepare("
        SELECT rsvps.*, users.name, users.email 
        FROM rsvps 
        JOIN users ON rsvps.user_id = users.user_id 
        WHERE rsvps.event_id = ? 
        ORDER BY rsvps.rsvp_date
    ");
    $stmt->bind_param("i", $selected_event_id);
    $stmt->execute();
    $roster = $stmt->get_result();

    $stmt2 = $conn->prepare("SELECT event_name FROM events WHERE event_id = ?");
    $stmt2->bind_param("i", $selected_event_id);
    $stmt2->execute();
    $event = $stmt2->get_result()->fetch_assoc();
    $event_name = $event['event_name'] ?? '';
}
?>
<!DOCTYPE html>
<html>
<head><title>Event Roster</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📋 Event Roster</h2>
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
    </div>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="get" class="mb-4">
        <div class="row">
            <div class="col-md-8">
                <select name="event_id" class="form-select" onchange="this.form.submit()">
                    <?php while ($e = $events->fetch_assoc()): ?>
                        <option value="<?= $e['event_id'] ?>" <?= $e['event_id'] == $selected_event_id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($e['event_name']) ?> (<?= $e['event_date'] ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-secondary w-100">View Roster</button>
            </div>
        </div>
    </form>

    <?php if ($selected_event_id > 0): ?>
        <h4><?= htmlspecialchars($event_name) ?></h4>
        
        <?php if ($roster->num_rows == 0): ?>
            <div class="alert alert-info">No RSVPs yet for this event.</div>
        <?php else: ?>
            <form method="post">
                <input type="hidden" name="event_id" value="<?= $selected_event_id ?>">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Runner Name</th>
                            <th>Email</th>
                            <th>RSVP Date</th>
                            <th>Attendance Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $roster->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= $row['rsvp_date'] ?></td>
                                <td>
                                    <select name="attendance[<?= $row['rsvp_id'] ?>]" class="form-select form-select-sm">
                                        <option value="rsvp" <?= $row['attendance_status'] == 'rsvp' ? 'selected' : '' ?>>RSVPed</option>
                                        <option value="attended" <?= $row['attendance_status'] == 'attended' ? 'selected' : '' ?>>Attended</option>
                                        <option value="absent" <?= $row['attendance_status'] == 'absent' ? 'selected' : '' ?>>Absent</option>
                                    </select>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <button type="submit" name="update_attendance" class="btn btn-primary">Save Attendance</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</div>
    <script src="script.js"></script>
</body>
</html>