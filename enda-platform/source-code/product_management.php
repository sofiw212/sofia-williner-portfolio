<?php
session_start();
require_once 'config/db.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';
$edit_shoe = null;

// Handle add/edit/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add or update shoe
    if (isset($_POST['save_shoe'])) {
        $shoe_id = isset($_POST['shoe_id']) ? (int)$_POST['shoe_id'] : 0;
        $name = trim($_POST['name']);
        $cushioning = $_POST['cushioning_level'];
        $stability = (int)$_POST['stability_rating'];
        $flexibility = (int)$_POST['flexibility_rating'];
        $weight = (int)$_POST['weight_grams'];
        $terrain = $_POST['terrain_suitability'];
        $description = trim($_POST['description']);
        $enda_url = trim($_POST['enda_url']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (empty($name)) {
            $error = "Shoe name is required.";
        } else {
            if ($shoe_id > 0) {
                // Update existing shoe
                $stmt = $conn->prepare("UPDATE shoes SET name=?, cushioning_level=?, stability_rating=?, flexibility_rating=?, weight_grams=?, terrain_suitability=?, description=?, enda_url=?, is_active=? WHERE shoe_id=?");
                $stmt->bind_param("ssiissssii", $name, $cushioning, $stability, $flexibility, $weight, $terrain, $description, $enda_url, $is_active, $shoe_id);
            } else {
                // Insert new shoe
                $stmt = $conn->prepare("INSERT INTO shoes (name, cushioning_level, stability_rating, flexibility_rating, weight_grams, terrain_suitability, description, enda_url, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssiissssi", $name, $cushioning, $stability, $flexibility, $weight, $terrain, $description, $enda_url, $is_active);
            }
            if ($stmt->execute()) {
                $success = "Shoe saved successfully!";
            } else {
                $error = "Error saving shoe: " . $conn->error;
            }
        }
    }

    // Delete shoe (soft delete - set is_active=0)
    if (isset($_POST['delete_shoe'])) {
        $shoe_id = (int)$_POST['shoe_id'];
        $stmt = $conn->prepare("UPDATE shoes SET is_active = 0 WHERE shoe_id = ?");
        $stmt->bind_param("i", $shoe_id);
        if ($stmt->execute()) {
            $success = "Shoe deactivated successfully.";
        }
    }

    // Reactivate shoe
    if (isset($_POST['reactivate_shoe'])) {
        $shoe_id = (int)$_POST['shoe_id'];
        $stmt = $conn->prepare("UPDATE shoes SET is_active = 1 WHERE shoe_id = ?");
        $stmt->bind_param("i", $shoe_id);
        if ($stmt->execute()) {
            $success = "Shoe reactivated successfully.";
        }
    }
}

// If editing, load shoe data
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM shoes WHERE shoe_id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_shoe = $stmt->get_result()->fetch_assoc();
}

// Fetch all shoes (including inactive)
$shoes = $conn->query("SELECT * FROM shoes ORDER BY is_active DESC, name");
?>
<!DOCTYPE html>
<html>
<head><title>Product Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>👟 Manage Shoes</h2>
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- Add/Edit Form -->
    <div class="card mb-4">
        <div class="card-header">
            <h5><?= $edit_shoe ? 'Edit Shoe' : 'Add New Shoe' ?></h5>
        </div>
        <div class="card-body">
            <form method="post">
                <?php if ($edit_shoe): ?>
                    <input type="hidden" name="shoe_id" value="<?= $edit_shoe['shoe_id'] ?>">
                <?php endif; ?>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Shoe Name *</label>
                        <input type="text" name="name" class="form-control" required 
                               value="<?= htmlspecialchars($edit_shoe['name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Cushioning Level</label>
                        <select name="cushioning_level" class="form-select" required>
                            <option value="minimal" <?= ($edit_shoe['cushioning_level'] ?? '') == 'minimal' ? 'selected' : '' ?>>Minimal</option>
                            <option value="moderate" <?= ($edit_shoe['cushioning_level'] ?? '') == 'moderate' ? 'selected' : '' ?>>Moderate</option>
                            <option value="maximum" <?= ($edit_shoe['cushioning_level'] ?? '') == 'maximum' ? 'selected' : '' ?>>Maximum</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Stability (1-5)</label>
                        <input type="number" name="stability_rating" class="form-control" min="1" max="5" required
                               value="<?= $edit_shoe['stability_rating'] ?? 3 ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Flexibility (1-5)</label>
                        <input type="number" name="flexibility_rating" class="form-control" min="1" max="5" required
                               value="<?= $edit_shoe['flexibility_rating'] ?? 3 ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Weight (grams)</label>
                        <input type="number" name="weight_grams" class="form-control" required
                               value="<?= $edit_shoe['weight_grams'] ?? 250 ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Terrain Suitability</label>
                        <select name="terrain_suitability" class="form-select" required>
                            <option value="road" <?= ($edit_shoe['terrain_suitability'] ?? '') == 'road' ? 'selected' : '' ?>>Road</option>
                            <option value="trail" <?= ($edit_shoe['terrain_suitability'] ?? '') == 'trail' ? 'selected' : '' ?>>Trail</option>
                            <option value="track" <?= ($edit_shoe['terrain_suitability'] ?? '') == 'track' ? 'selected' : '' ?>>Track</option>
                            <option value="mixed" <?= ($edit_shoe['terrain_suitability'] ?? '') == 'mixed' ? 'selected' : '' ?>>Mixed</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($edit_shoe['description'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label>Enda URL (link to product page)</label>
                    <input type="text" name="enda_url" class="form-control" 
                           value="<?= htmlspecialchars($edit_shoe['enda_url'] ?? '') ?>">
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active" <?= ($edit_shoe['is_active'] ?? 1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_active">Active (available for matching)</label>
                </div>
                <button type="submit" name="save_shoe" class="btn btn-primary"><?= $edit_shoe ? 'Update Shoe' : 'Add Shoe' ?></button>
                <?php if ($edit_shoe): ?>
                    <a href="product_management.php" class="btn btn-secondary">Cancel Edit</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Shoe List -->
    <h5>Current Shoes</h5>
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Terrain</th>
                <th>Cushioning</th>
                <th>Stability</th>
                <th>Flexibility</th>
                <th>Weight (g)</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($shoe = $shoes->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($shoe['name']) ?></td>
                    <td><?= ucfirst($shoe['terrain_suitability']) ?></td>
                    <td><?= ucfirst($shoe['cushioning_level']) ?></td>
                    <td><?= $shoe['stability_rating'] ?></td>
                    <td><?= $shoe['flexibility_rating'] ?></td>
                    <td><?= $shoe['weight_grams'] ?></td>
                    <td>
                        <span class="badge <?= $shoe['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                            <?= $shoe['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                    <td>
                        <a href="product_management.php?edit=<?= $shoe['shoe_id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                        <?php if ($shoe['is_active']): ?>
                            <form method="post" style="display:inline;" onsubmit="return confirm('Deactivate this shoe? It will no longer appear in matches.');">
                                <input type="hidden" name="shoe_id" value="<?= $shoe['shoe_id'] ?>">
                                <button type="submit" name="delete_shoe" class="btn btn-sm btn-warning">Deactivate</button>
                            </form>
                        <?php else: ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="shoe_id" value="<?= $shoe['shoe_id'] ?>">
                                <button type="submit" name="reactivate_shoe" class="btn btn-sm btn-success">Reactivate</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
    <script src="script.js"></script>
</body>
</html>