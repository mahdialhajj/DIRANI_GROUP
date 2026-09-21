<?php
include 'config.php';

// Get company ID from URL
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: index.php");
    exit;
}

try {
    $sql = "SELECT * FROM companies WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$company) {
        header("Location: index.php");
        exit;
    }
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($company['name']); ?> - Details</title>
    <link rel="stylesheet" href="css\style.css">
</head>
<body>
    <div class="container">
        <a href="index.php" class="btn back-btn">← Back to Companies</a>
        
        <div class="company-details">
            <div class="details-header">
                <div class="details-image">
                    <?php if (!empty($company['photo']) && file_exists($company['photo'])): ?>
                        <img src="<?php echo $company['photo']; ?>" alt="<?php echo htmlspecialchars($company['name']); ?>">
                    <?php else: ?>
                        <div class="placeholder-image large">
                            <span>No Photo Available</span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="details-info">
                    <h1><?php echo htmlspecialchars($company['name']); ?></h1>
                    <div class="detail-item">
                        <strong>Founded:</strong> <?php echo $company['year_created']; ?>
                    </div>
                    <div class="detail-item">
                        <strong>Manager:</strong> <?php echo htmlspecialchars($company['manager']); ?>
                    </div>
                    <div class="detail-item">
                        <strong>Added on:</strong> <?php echo date('F j, Y', strtotime($company['created_at'])); ?>
                    </div>
                    <div class="detail-item">
                        <strong>Company ID:</strong> #<?php echo $company['id']; ?>
                    </div>
                </div>
            </div>
            
            <div class="details-content">
                <h2>About <?php echo htmlspecialchars($company['name']); ?></h2>
                <p>Company founded in <?php echo $company['year_created']; ?> and currently managed by <?php echo htmlspecialchars($company['manager']); ?>.</p>
                
                <div class="company-stats">
                    <div class="stat">
                        <span class="stat-number"><?php echo (int)date('Y') - $company['year_created']; ?></span>
                        <span class="stat-label">Years in Business</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">#<?php echo $company['id']; ?></span>
                        <span class="stat-label">Company ID</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>