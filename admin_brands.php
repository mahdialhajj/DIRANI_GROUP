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

// Handle brand addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_brand'])) {
    $brand_name = $_POST['brand_name'];
    $brand_description = $_POST['brand_description'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO company_brands (company_id, brand_name, brand_description) VALUES (?, ?, ?)");
        $stmt->execute([$company_id, $brand_name, $brand_description]);
        $success = "Brand added successfully!";
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle brand deletion
if (isset($_GET['delete_brand'])) {
    $brand_id = $_GET['delete_brand'];
    try {
        $stmt = $pdo->prepare("DELETE FROM company_brands WHERE id = ? AND company_id = ?");
        $stmt->execute([$brand_id, $company_id]);
        $success = "Brand deleted successfully!";
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch existing brands
try {
    $stmt = $pdo->prepare("SELECT * FROM company_brands WHERE company_id = ? ORDER BY brand_name ASC");
    $stmt->execute([$company_id]);
    $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $brands = [];
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
    <title>Manage Brands - <?php echo htmlspecialchars($company['name']); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Manage Brands - <?php echo htmlspecialchars($company['name']); ?></h1>
        
        <a href="company_profile.php?id=<?php echo $company_id; ?>" class="btn">← Back to Company</a>
        
        <?php if (isset($success)): ?>
            <div class="alert success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Add Brand Form -->
        <div class="admin-form">
            <h2>Add New Brand</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="brand_name">Brand Name:</label>
                    <input type="text" id="brand_name" name="brand_name" required>
                </div>
                
                <div class="form-group">
                    <label for="brand_description">Brand Description:</label>
                    <textarea id="brand_description" name="brand_description" rows="4"></textarea>
                </div>
                
                <button type="submit" name="add_brand" class="btn-primary">Add Brand</button>
            </form>
        </div>

        <!-- Existing Brands -->
        <div class="admin-list">
            <h2>Existing Brands</h2>
            
            <?php if (empty($brands)): ?>
                <p>No brands found. Add your first brand above.</p>
            <?php else: ?>
                <div class="brands-list">
                    <?php foreach ($brands as $brand): ?>
                    <div class="brand-item">
                        <div class="brand-info">
                            <h3><?php echo htmlspecialchars($brand['brand_name']); ?></h3>
                            <?php if (!empty($brand['brand_description'])): ?>
                                <p><?php echo htmlspecialchars($brand['brand_description']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="brand-actions">
                            <a href="admin_brands.php?company_id=<?php echo $company_id; ?>&delete_brand=<?php echo $brand['id']; ?>" 
                               class="btn-secondary" 
                               onclick="return confirm('Are you sure you want to delete this brand?')">Delete</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>