<?php
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$company_id = $_GET['company_id'] ?? null;

if (!$company_id) {
    header("Location: index.php");
    exit;
}

// Handle photo addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_photo'])) {
    $caption = $_POST['caption'];
    $display_order = $_POST['display_order'];
    
    // Handle file upload
    $photo_path = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $file = $_FILES['photo'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_extension, $allowed_types)) {
            $filename = uniqid() . '_' . time() . '.' . $file_extension;
            $photo_path = 'uploads/' . $filename;
            
            if (!move_uploaded_file($file['tmp_name'], $photo_path)) {
                $error = "Failed to upload photo.";
            }
        } else {
            $error = "Invalid file type. Allowed: JPG, PNG, GIF";
        }
    }
    
    if (!isset($error)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO company_photos (company_id, photo_path, caption, display_order) VALUES (?, ?, ?, ?)");
            $stmt->execute([$company_id, $photo_path, $caption, $display_order]);
            $success = "Photo added successfully!";
        } catch(PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Handle photo deletion
if (isset($_GET['delete_photo'])) {
    $photo_id = $_GET['delete_photo'];
    try {
        $stmt = $pdo->prepare("DELETE FROM company_photos WHERE id = ? AND company_id = ?");
        $stmt->execute([$photo_id, $company_id]);
        $success = "Photo deleted successfully!";
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch existing photos
try {
    $stmt = $pdo->prepare("SELECT * FROM company_photos WHERE company_id = ? ORDER BY display_order ASC");
    $stmt->execute([$company_id]);
    $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $photos = [];
}

// Get company name
try {
    $stmt = $pdo->prepare("SELECT name FROM companies WHERE id = ?");
    $stmt->execute([$company_id]);
    $company = $stmt->fetch();
} catch(PDOException $e) {
    $company = ['name' => 'Unknown Company'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Photos - <?php echo htmlspecialchars($company['name']); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Manage Photos - <?php echo htmlspecialchars($company['name']); ?></h1>
        
        <a href="company_profile.php?id=<?php echo $company_id; ?>" class="btn">← Back to Company</a>
        
        <?php if (isset($success)): ?>
            <div class="alert success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Add Photo Form -->
        <div class="admin-form">
            <h2>Add New Photo</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="photo">Photo:</label>
                    <input type="file" id="photo" name="photo" accept="image/*" required>
                </div>
                
                <div class="form-group">
                    <label for="caption">Caption:</label>
                    <input type="text" id="caption" name="caption">
                </div>
                
                <div class="form-group">
                    <label for="display_order">Display Order:</label>
                    <input type="number" id="display_order" name="display_order" value="1" min="1">
                </div>
                
                <button type="submit" name="add_photo" class="btn-primary">Add Photo</button>
            </form>
        </div>

        <!-- Existing Photos -->
        <div class="admin-list">
            <h2>Existing Photos</h2>
            
            <?php if (empty($photos)): ?>
                <p>No photos found. Add your first photo above.</p>
            <?php else: ?>
                <div class="photos-grid">
                    <?php foreach ($photos as $photo): ?>
                    <div class="photo-item">
                        <div class="photo-preview">
                            <?php if (!empty($photo['photo_path']) && file_exists($photo['photo_path'])): ?>
                                <img src="<?php echo $photo['photo_path']; ?>" alt="<?php echo htmlspecialchars($photo['caption']); ?>" style="width: 100%; height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div style="width: 100%; height: 200px; background: #eee; display: flex; align-items: center; justify-content: center;">
                                    <span>No Image</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="photo-info">
                            <p><strong>Caption:</strong> <?php echo htmlspecialchars($photo['caption'] ?? 'No caption'); ?></p>
                            <p><strong>Order:</strong> <?php echo $photo['display_order']; ?></p>
                        </div>
                        <div class="photo-actions">
                            <a href="admin_photos.php?company_id=<?php echo $company_id; ?>&delete_photo=<?php echo $photo['id']; ?>" 
                               class="btn-secondary" 
                               onclick="return confirm('Are you sure you want to delete this photo?')">Delete</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>