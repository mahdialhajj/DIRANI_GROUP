<?php
include 'config.php';
// brand-details.php

// Get brand ID from URL
$brand_id = isset($_GET['brand_id']) ? intval($_GET['brand_id']) : 0;

// Fetch brand details from database
try {
    $stmt = $pdo->prepare("
        SELECT * 
        FROM company_brands 
        WHERE id = ?  -- Only need brand_id for the query
    ");
    
    // Execute with only brand_id parameter
    $stmt->execute([$brand_id]);
    $brand = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$brand) {
        die("Brand not found");
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Extract data
$brand_name = htmlspecialchars($brand['brand_name']);
$brand_description = htmlspecialchars($brand['brand_description'] ?: 'Premium Quality Brand');
$brand_logo = !empty($brand['brand_logo']) ? str_replace('\\', '/', $brand['brand_logo']) : '';
$detailed_description = htmlspecialchars($brand['detailed_description'] ?? 'No detailed description available.');
$mission_statement = htmlspecialchars($brand['mission_statement'] ?? '');
$brand_values = htmlspecialchars($brand['brand_values'] ?? '');

// Social media links
$facebook_url = $brand['facebook_url'] ?? '';
$instagram_url = $brand['instagram_url'] ?? '';
$twitter_url = $brand['twitter_url'] ?? '';
$linkedin_url = $brand['linkedin_url'] ?? '';

// Additional info
$founded_year = htmlspecialchars($brand['founded_year'] ?? 'Not specified');
$headquarters = htmlspecialchars($brand['headquarters'] ?? 'Not specified');
$industry = htmlspecialchars($brand['industry'] ?? 'Food & Beverage');
$website_url = $brand['website_url'] ?? '';
$contact_email = $brand['contact_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $brand_name; ?> - Brand Details</title>
    <link rel="stylesheet" href="CSS/brand-details.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    </style>
</head>
<body>
    <div class="container">
        <div class="brand-header">
            <div class="brand-logo">
                <?php if (!empty($brand_logo)): ?>
                    <img src="<?php echo $brand_logo; ?>" alt="<?php echo $brand_name; ?>">
                <?php else: ?>
                    <i class="fas fa-crown" style="font-size: 4rem; color: #764ba2;"></i>
                <?php endif; ?>
            </div>
            <div class="brand-info">
                <h1><?php echo $brand_name; ?></h1>
                <div class="brand-tagline"><?php echo $brand_description; ?></div>
                
                <?php if ($facebook_url || $instagram_url || $twitter_url || $linkedin_url): ?>
                <div class="social-links">
                    <?php if (!empty($facebook_url)): ?>
                        <a href="<?php echo htmlspecialchars($facebook_url); ?>" target="_blank" class="social-link" title="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php if (!empty($instagram_url)): ?>
                        <a href="<?php echo htmlspecialchars($instagram_url); ?>" target="_blank" class="social-link" title="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php if (!empty($twitter_url)): ?>
                        <a href="<?php echo htmlspecialchars($twitter_url); ?>" target="_blank" class="social-link" title="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php if (!empty($linkedin_url)): ?>
                        <a href="<?php echo htmlspecialchars($linkedin_url); ?>" target="_blank" class="social-link" title="LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="content-wrapper">
            <div class="brand-description">
                <h2>About <?php echo $brand_name; ?></h2>
                <p><?php echo nl2br($detailed_description); ?></p>
                
                <?php if (!empty($mission_statement)): ?>
                    <h3>Our Mission</h3>
                    <p><?php echo nl2br($mission_statement); ?></p>
                <?php endif; ?>
                
                <?php if (!empty($brand_values)): ?>
                    <h3>Our Values</h3>
                    <p><?php echo nl2br($brand_values); ?></p>
                <?php endif; ?>
                
                
            </div>
            
            <div class="brand-details">
                <h3>Brand Information</h3>
                
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-hashtag"></i>
                        <span>Brand ID</span>
                    </div>
                    <div class="detail-value">#<?php echo $brand['id']; ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-building"></i>
                        <span>Company ID</span>
                    </div>
                    <div class="detail-value">#<?php echo $brand['company_id']; ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Founded</span>
                    </div>
                    <div class="detail-value"><?php echo $founded_year; ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Headquarters</span>
                    </div>
                    <div class="detail-value"><?php echo $headquarters; ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-industry"></i>
                        <span>Industry</span>
                    </div>
                    <div class="detail-value"><?php echo $industry; ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-globe"></i>
                        <span>Website</span>
                    </div>
                    <div class="detail-value">
                        <?php if (!empty($website_url)): ?>
                            <a href="<?php echo htmlspecialchars($website_url); ?>" target="_blank">
                                <i class="fas fa-external-link-alt"></i> Visit Website
                            </a>
                        <?php else: ?>
                            <span class="empty-message">Not available</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">
                        <i class="fas fa-envelope"></i>
                        <span>Contact Email</span>
                    </div>
                    <div class="detail-value">
                        <?php if (!empty($contact_email)): ?>
                            <a href="mailto:<?php echo htmlspecialchars($contact_email); ?>">
                                <i class="fas fa-paper-plane"></i> <?php echo htmlspecialchars($contact_email); ?>
                            </a>
                        <?php else: ?>
                            <span class="empty-message">Not available</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Add fade-in animation
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.container > *');
            elements.forEach((el, index) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, index * 200);
            });
        });
    </script>
</body>
</html>