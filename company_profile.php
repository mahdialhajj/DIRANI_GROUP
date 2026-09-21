<?php
include 'config.php';

// Get company ID from URL
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: index.php");
    exit;
}

// Fetch company details
try {
    $stmt = $pdo->prepare("SELECT * FROM companies WHERE id = ?");
    $stmt->execute([$id]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$company) {
        header("Location: index.php");
        exit;
    }
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Fetch company photos
try {
    $stmt = $pdo->prepare("SELECT * FROM company_photos WHERE company_id = ? ORDER BY display_order ASC");
    $stmt->execute([$id]);
    $company_photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $company_photos = [];
}

// Fetch company brands
try {
    $stmt = $pdo->prepare("SELECT * FROM company_brands WHERE company_id = ? ORDER BY brand_name ASC");
    $stmt->execute([$id]);
    $company_brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $company_brands = [];
}

// Fetch main categories for products specific to this company
try {
    $stmt = $pdo->prepare("
        SELECT DISTINCT mc.*, 
               COUNT(DISTINCT sc.id) as subcategory_count,
               COUNT(DISTINCT p.id) as product_count
        FROM main_categories mc
        LEFT JOIN sub_categories sc ON mc.id = sc.main_category_id
        LEFT JOIN products p ON sc.id = p.sub_category_id
        WHERE p.company_id = ?  -- Filter by company
        GROUP BY mc.id
        HAVING product_count > 0  -- Only show categories with products
        ORDER BY mc.display_order, mc.name_ar
        LIMIT 4
    ");
    $stmt->execute([$id]);  // Pass company ID
    $main_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $main_categories = [];
    error_log("Database error: " . $e->getMessage());
}

// Fetch ALL team members
try {
    $stmt = $pdo->prepare("SELECT * FROM company_team WHERE company_id = ? ORDER BY position ASC");
    $stmt->execute([$id]);
    $team_members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get only first 3 members for initial display
    $team_members_initial = array_slice($team_members, 0, 3);
} catch(PDOException $e) {
    $team_members = [];
    $team_members_initial = [];
}

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_feedback'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];
    $rating = $_POST['rating'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO company_feedback (company_id, name, email, message, rating) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$id, $name, $email, $message, $rating]);
        $feedback_success = "Thank you for your feedback! Your review has been submitted successfully.";
        
        // Clear form values
        $_POST = array();
    } catch(PDOException $e) {
        $feedback_error = "Sorry, there was an error submitting your feedback. Please try again later.";
        error_log("Feedback error: " . $e->getMessage());
    }
}

// Fetch recent feedback
try {
    $stmt = $pdo->prepare("SELECT * FROM company_feedback WHERE company_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$id]);
    $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $feedbacks = [];
}

// Check if brand_id is provided
$brand_id = $_GET['brand_id'] ?? null;
if ($brand_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM company_brands WHERE id = ?");
        $stmt->execute([$brand_id]);
        $brand = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$brand) {
            header("Location: index.php");
            exit;
        }
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

// Enhanced image validation and quality handling
function getOptimizedImagePath($path, $width = null, $height = null) {
    if (empty($path)) return null;
    
    // Check if image exists
    if (!file_exists($path)) {
        // Try to find in different directories
        $possible_paths = [
            $path,
            'uploads/' . basename($path),
            '../uploads/' . basename($path),
            'images/' . basename($path),
            'img/' . basename($path)
        ];
        
        foreach ($possible_paths as $possible_path) {
            if (file_exists($possible_path)) {
                $path = $possible_path;
                break;
            }
        }
    }
    
    return $path;
}

// Enhanced image display with quality attributes
function displayHighQualityImage($path, $alt, $class = '', $lazy = true) {
    $optimized_path = getOptimizedImagePath($path);
    
    if ($optimized_path && file_exists($optimized_path)) {
        $loading = $lazy ? 'loading="lazy"' : '';
        $class_attr = $class ? 'class="' . $class . '"' : '';
        
        return '<img src="' . htmlspecialchars($optimized_path) . '" 
                     alt="' . htmlspecialchars($alt) . '" 
                     ' . $class_attr . ' 
                     ' . $loading . '
                     onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\'">';
    }
    
    return null;
}

// Helper function to check if image is valid
function isValidImage($path) {
    if (empty($path)) return false;
    $optimized_path = getOptimizedImagePath($path);
    return $optimized_path && file_exists($optimized_path);
}

// Get category icon
function getCategoryIcon($categoryName) {
    $icons = [
        'مربى' => 'fas fa-jar',
        'مخللات' => 'fas fa-wine-bottle',
        'زيتون' => 'fas fa-seedling',
        'تمور' => 'fas fa-apple-alt',
        'طحينة' => 'fas fa-blender',
        'ماء الورد والزهر' => 'fas fa-tint',
        'زعتر' => 'fas fa-leaf',
        'بهارات' => 'fas fa-pepper-hot',
        'خل' => 'fas fa-flask',
        'حلويات' => 'fas fa-cookie-bite',
        'أعشاب' => 'fas fa-seedling'
    ];
    return $icons[$categoryName] ?? 'fas fa-box';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($company['name']); ?> - Premium Business Profile</title>
    <meta name="description" content="Discover <?php echo htmlspecialchars($company['name']); ?> - A leading business established in <?php echo $company['year_created']; ?> and managed by <?php echo htmlspecialchars($company['manager']); ?>.">
    <link rel="stylesheet" href="CSS/company_profile.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Add styles for hidden team members */
        .team-member.hidden {
            display: none !important;
        }
        
        .team-member {
            transition: all 0.3s ease;
        }
        
        /* View All Button Styles */
        .view-all-container {
            text-align: center;
            margin-top: 40px;
        }
        
        .view-all-btn {
            background: linear-gradient(135deg, #3498db, #2c3e50);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 50px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }
        
        .view-all-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
            background: linear-gradient(135deg, #2980b9, #1a252f);
        }
        
        .view-all-btn:active {
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <!-- Minimal Toast for Feedback Only -->
    <div class="minimal-toast" id="feedbackToast">
        <div class="minimal-toast-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="minimal-toast-content">
            <div class="minimal-toast-title" id="toastTitle">Success!</div>
            <div class="minimal-toast-message" id="toastMessage">Your feedback has been submitted successfully.</div>
        </div>
        <button class="minimal-toast-close" onclick="hideToast()">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- Professional Navigation Bar -->
    <header class="main-header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <a href="index.php">
                        <img src="uploads\campanylogo\logo.jpg" alt="Logo" class="building-logo">
                        <?php echo htmlspecialchars($company['name']); ?>
                    </a>
                </div>
                <div class="nav-menu">
                    <a href="index.php" class="nav-link">
                        <i class="fas fa-home"></i>
                        Home
                    </a>
                    <a href="#story" class="nav-link">
                        <i class="fas fa-book-open"></i>
                        Our Story
                    </a>
                    <a href="#team" class="nav-link">
                        <i class="fas fa-users"></i>
                        Our Team
                    </a>
                    <a href="#products" class="nav-link">
                        <i class="fas fa-boxes"></i>
                        Products
                    </a>
                    <a href="#feedback" class="nav-link">
                        <i class="fas fa-comment-dots"></i>
                        Feedback
                    </a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="sales_order.php?company_id=<?php echo $id; ?>" class="btn-primary">
                            <i class="fas fa-shopping-cart"></i> Sales Order
                        </a>
                        <a href="logout.php" class="btn-secondary">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    <?php else: ?>
                        <div class="login-button-container">
                            <?php
                            // Check if user is on mobile
                            $isMobile = preg_match("/(android|iphone|ipad|mobile)/i", $_SERVER['HTTP_USER_AGENT']);
                            $isAndroid = stripos($_SERVER['HTTP_USER_AGENT'], 'android') !== false;
                            
                            if ($isAndroid): ?>
                                <!-- Android users - try app first -->
                                <button onclick="openAndroidAppWithFallback(<?php echo $id; ?>)" class="btn-android-login">
                                    <i class="fab fa-android"></i> Open in App
                                </button>
                                <div class="fallback-link" style="display:none; margin-top:8px;">
                                    <a href="login.php?company_id=<?php echo $id; ?>&web=true" class="btn-web-login">
                                        <i class="fas fa-globe"></i> Use Web Login
                                    </a>
                                </div>
                            <?php else: ?>
                                <!-- Non-Android or desktop -->
                                <a href="login.php?company_id=<?php echo $id; ?>" class="btn-primary">
                                    <i class="fas fa-sign-in-alt"></i> Login
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <div class="company-profile">
          <!-- Slideshow Section with Video Support -->
<section class="slideshow-section">
    <?php if (!empty($company_photos)): ?>
        <div class="slideshow-container">
            <?php foreach ($company_photos as $index => $media): ?>
                <div class="slide" style="display: <?php echo $index === 0 ? 'block' : 'none'; ?>">
                    
                    <?php 
                    // Check if this is a video
                    $is_video = isset($media['media_type']) && $media['media_type'] == 'video';
                    $video_url = $media['video_url'] ?? '';
                    
                    if ($is_video && !empty($video_url)):
                        // Extract YouTube video ID
                        $youtube_id = '';
                        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $video_url, $matches)) {
                            $youtube_id = $matches[1];
                        }
                    ?>
                        <!-- Video Slide -->
                        <div class="video-slide">
                            <?php if ($youtube_id): ?>
                                <!-- YouTube Video - Full Width Fix -->
                                <iframe 
                                    src="https://www.youtube.com/embed/<?php echo $youtube_id; ?>?autoplay=0&controls=1&rel=0&modestbranding=1&showinfo=0&fs=1"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen>
                                </iframe>
                            <?php else: ?>
                                <!-- Direct MP4 Video -->
                                <video controls>
                                    <source src="<?php echo htmlspecialchars($video_url); ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <!-- Image Slide -->
                        <?php if (!empty($media['photo_path']) && file_exists($media['photo_path'])): ?>
                            <img src="<?php echo htmlspecialchars($media['photo_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($media['caption'] ?? 'Company Photo'); ?>"
                                 class="slide-image">
                        <?php else: ?>
                            <div class="slide-placeholder">
                                <i class="fas fa-image"></i>
                                <p><?php echo htmlspecialchars($media['caption'] ?? 'No Image'); ?></p>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <!-- Caption -->
                    <?php if (!empty($media['caption'])): ?>
                        <div class="slide-caption">
                            <h3><?php echo htmlspecialchars($media['caption']); ?></h3>
                            <?php if (!empty($media['description'])): ?>
                                <p><?php echo htmlspecialchars($media['description']); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Navigation Buttons -->
        <button class="prev-btn" onclick="changeSlide(-1)">❮</button>
        <button class="next-btn" onclick="changeSlide(1)">❯</button>
        
        <!-- Dots -->
        <div class="dots-container">
            <?php foreach ($company_photos as $index => $media): ?>
                <span class="dot" onclick="currentSlide(<?php echo $index; ?>)"></span>
            <?php endforeach; ?>
        </div>
        
    <?php else: ?>
        <div class="no-slides">
            <i class="fas fa-camera"></i>
            <h2>No Media Yet</h2>
            <p>Add images or videos to your slideshow</p>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="admin_photos.php?company_id=<?php echo $id; ?>" class="btn-primary">
                    <i class="fas fa-plus"></i> Upload Media
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>

            <!-- Hero Stats Section -->
            <section class="hero-section">
                <div class="hero-content">
                    <h1><?php echo htmlspecialchars($company['name']); ?></h1>
                    <p class="hero-subtitle">Excellence Since <?php echo $company['year_created']; ?></p>
                    <p class="hero-manager">
                        <i class="fas fa-user-tie"></i>
                        Led by <?php echo htmlspecialchars($company['manager']); ?>
                    </p>
                    <div class="hero-stats">
                        <div class="stat">
                            <div class="stat-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <span class="stat-number"><?php echo (int)date('Y') - $company['year_created']; ?>+</span>
                            <span class="stat-label">Years Excellence</span>
                        </div>
                        <div class="stat">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <span class="stat-number"><?php echo count($team_members); ?>+</span>
                            <span class="stat-label">Team Members</span>
                        </div>
                        <div class="stat">
                            <div class="stat-icon">
                                <i class="fas fa-crown"></i>
                            </div>
                            <span class="stat-number"><?php echo count($company_brands); ?>+</span>
                            <span class="stat-label">Premium Brands</span>
                        </div>
                        <div class="stat">
                            <div class="stat-icon">
                                <i class="fas fa-cube"></i>
                            </div>
                            <span class="stat-number"><?php echo count($main_categories); ?>+</span>
                            <span class="stat-label">Product Categories</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Our Story Section -->
            <section id="story" class="section section-highlight">
                <div class="container">
                    <div class="section-header">
                        <h2 class="section-title">Our Journey & Vision</h2>
                        <div class="section-divider"></div>
                        <p class="section-subtitle">Discover the story behind our success and our vision for the future</p>
                    </div>
                    <div class="story-content">
                        <?php if (!empty($company['story'])): ?>
                            <div class="story-text">
                                <?php echo nl2br(htmlspecialchars($company['story'])); ?>
                            </div>
                        <?php else: ?>
                            <div class="story-text">
                                <p><strong><?php echo htmlspecialchars($company['name']); ?></strong> was founded in <strong><?php echo $company['year_created']; ?></strong> with a clear vision: to deliver exceptional quality and unparalleled service in our industry. From our humble beginnings, we've grown into a trusted and respected name, known for our commitment to excellence and innovation.</p>
                                
                                <p>Under the strategic leadership of <strong><?php echo htmlspecialchars($company['manager']); ?></strong>, we've consistently pushed boundaries and set new standards. Our journey has been marked by continuous improvement, adaptation to market changes, and an unwavering focus on customer satisfaction.</p>
                                
                                <p>What sets us apart is our dedication to quality in every aspect of our operations. We believe that great businesses are built on strong relationships, innovative solutions, and a commitment to making a positive impact in our community and industry.</p>
                                
                                <p>As we look to the future, we remain committed to our core values while embracing new technologies and opportunities. Our mission is to continue delivering exceptional value to our clients while maintaining the highest standards of integrity and professionalism.</p>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Achievements -->
                        <div class="achievements-grid">
                            <div class="achievement-item">
                                <i class="fas fa-trophy"></i>
                                <h3>Industry Leader</h3>
                                <p>Recognized for innovation and excellence</p>
                            </div>
                            <div class="achievement-item">
                                <i class="fas fa-heart"></i>
                                <h3>Customer First</h3>
                                <p>98% client satisfaction rate</p>
                            </div>
                            <div class="achievement-item">
                                <i class="fas fa-chart-line"></i>
                                <h3>Sustainable Growth</h3>
                                <p>Consistent year-over-year expansion</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Enhanced Professional Team Section - SHOWING ONLY 3 MEMBERS INITIALLY -->
            <section id="team" class="team-section section-highlight">
                <div class="bg-pattern"></div>
                <div class="floating-element"></div>
                <div class="floating-element"></div>
                
                <div class="team-container">
                    <div class="section-header">
                        <h2 class="section-title">Meet Our Leadership Team</h2>
                        <p class="section-subtitle">Discover the talented professionals driving innovation and excellence in our company</p>
                    </div>
                    
                    <!-- Team Statistics -->
                    <div class="team-stats">
                        <div class="stat-item">
                            <div class="stat-number" id="totalMembers"><?php echo count($team_members); ?></div>
                            <div class="stat-label">Total Team Members</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number" id="avgExperience">8.5</div>
                            <div class="stat-label">Avg. Experience</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number" id="projectsCompleted">350+</div>
                            <div class="stat-label">Projects Completed</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number" id="clientSatisfaction">98%</div>
                            <div class="stat-label">Client Satisfaction</div>
                        </div>
                    </div>
                    
                    <!-- Team Grid - Initially showing only 3 members -->
                    <div class="team-grid" id="teamGrid">
                        <?php if (!empty($team_members_initial)): ?>
                            <?php foreach ($team_members_initial as $index => $member): ?>
                                <div class="team-member" data-index="<?php echo $index; ?>">
                                    <div class="team-photo">
                                        <?php 
                                        $team_image_html = displayHighQualityImage(
                                            $member['photo'] ?? '',
                                            $member['name'],
                                            '',
                                            false // Don't lazy load team photos
                                        );
                                        
                                        if ($team_image_html): 
                                            echo $team_image_html;
                                        else: ?>
                                            <div class="team-photo-placeholder">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="team-overlay">
                                            <div class="team-social">
                                                <?php if (!empty($member['email'])): ?>
                                                    <a href="mailto:<?php echo htmlspecialchars($member['email']); ?>" class="social-link" title="Email <?php echo htmlspecialchars($member['name']); ?>">
                                                        <i class="fas fa-envelope"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (!empty($member['phone'])): ?>
                                                    <a href="tel:<?php echo htmlspecialchars($member['phone']); ?>" class="social-link" title="Call <?php echo htmlspecialchars($member['name']); ?>">
                                                        <i class="fas fa-phone"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (!empty($member['linkedin'])): ?>
                                                    <a href="<?php echo htmlspecialchars($member['linkedin']); ?>" class="social-link" title="LinkedIn Profile" target="_blank">
                                                        <i class="fab fa-linkedin"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="team-info">
                                        <h3 class="team-member-name"><?php echo htmlspecialchars($member['name']); ?></h3>
                                        <div class="team-position"><?php echo htmlspecialchars($member['position']); ?></div>
                                        <?php if (!empty($member['bio'])): ?>
                                            <div class="team-bio"><?php echo nl2br(htmlspecialchars($member['bio'])); ?></div>
                                        <?php else: ?>
                                            <div class="team-bio"><?php echo htmlspecialchars($member['name']); ?> brings extensive experience and expertise to their role as <?php echo htmlspecialchars($member['position']); ?>, contributing significantly to our team's success and client satisfaction.</div>
                                        <?php endif; ?>
                                        <div class="team-contact">
                                            <?php if (!empty($member['email'])): ?>
                                                <a href="mailto:<?php echo htmlspecialchars($member['email']); ?>" title="Email" class="team-contact-link">
                                                    <i class="fas fa-envelope"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!empty($member['phone'])): ?>
                                                <a href="tel:<?php echo htmlspecialchars($member['phone']); ?>" title="Phone" class="team-contact-link">
                                                    <i class="fas fa-phone"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!empty($member['linkedin'])): ?>
                                                <a href="<?php echo htmlspecialchars($member['linkedin']); ?>" title="LinkedIn" class="team-contact-link" target="_blank">
                                                    <i class="fab fa-linkedin"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <!-- Hidden team members (will be shown when "View All" is clicked) -->
                            <?php if (count($team_members) > 3): ?>
                                <?php foreach (array_slice($team_members, 3) as $index => $member): ?>
                                    <div class="team-member hidden" data-index="<?php echo $index + 3; ?>">
                                        <div class="team-photo">
                                            <?php 
                                            $team_image_html = displayHighQualityImage(
                                                $member['photo'] ?? '',
                                                $member['name'],
                                                '',
                                                false
                                            );
                                            
                                            if ($team_image_html): 
                                                echo $team_image_html;
                                            else: ?>
                                                <div class="team-photo-placeholder">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="team-overlay">
                                                <div class="team-social">
                                                    <?php if (!empty($member['email'])): ?>
                                                        <a href="mailto:<?php echo htmlspecialchars($member['email']); ?>" class="social-link" title="Email <?php echo htmlspecialchars($member['name']); ?>">
                                                            <i class="fas fa-envelope"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if (!empty($member['phone'])): ?>
                                                        <a href="tel:<?php echo htmlspecialchars($member['phone']); ?>" class="social-link" title="Call <?php echo htmlspecialchars($member['name']); ?>">
                                                            <i class="fas fa-phone"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if (!empty($member['linkedin'])): ?>
                                                        <a href="<?php echo htmlspecialchars($member['linkedin']); ?>" class="social-link" title="LinkedIn Profile" target="_blank">
                                                            <i class="fab fa-linkedin"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="team-info">
                                            <h3 class="team-member-name"><?php echo htmlspecialchars($member['name']); ?></h3>
                                            <div class="team-position"><?php echo htmlspecialchars($member['position']); ?></div>
                                            <?php if (!empty($member['bio'])): ?>
                                                <div class="team-bio"><?php echo nl2br(htmlspecialchars($member['bio'])); ?></div>
                                            <?php else: ?>
                                                <div class="team-bio"><?php echo htmlspecialchars($member['name']); ?> brings extensive experience and expertise to their role as <?php echo htmlspecialchars($member['position']); ?>, contributing significantly to our team's success and client satisfaction.</div>
                                            <?php endif; ?>
                                            <div class="team-contact">
                                                <?php if (!empty($member['email'])): ?>
                                                    <a href="mailto:<?php echo htmlspecialchars($member['email']); ?>" title="Email" class="team-contact-link">
                                                        <i class="fas fa-envelope"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (!empty($member['phone'])): ?>
                                                    <a href="tel:<?php echo htmlspecialchars($member['phone']); ?>" title="Phone" class="team-contact-link">
                                                        <i class="fas fa-phone"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (!empty($member['linkedin'])): ?>
                                                    <a href="<?php echo htmlspecialchars($member['linkedin']); ?>" title="LinkedIn" class="team-contact-link" target="_blank">
                                                        <i class="fab fa-linkedin"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="no-team-members">
                                <div class="empty-team">
                                    <i class="fas fa-users"></i>
                                    <h3>Building Our Dream Team</h3>
                                    <p>We're assembling a team of exceptional professionals dedicated to delivering outstanding results for our clients.</p>
                                    <p>Our team will combine industry expertise with innovative thinking to drive success.</p>
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <a href="admin_team.php?company_id=<?php echo $id; ?>" class="btn-primary">
                                            <i class="fas fa-plus"></i> Add Team Members
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- View All Button (only shown if there are more than 3 team members) -->
                    <?php if (count($team_members) > 3): ?>
                        <div class="view-all-container">
                            <button class="view-all-btn" id="viewAllBtn">
                                <i class="fas fa-users"></i> View All Team Members
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Product Categories Section -->
            <section id="products" class="product-categories-section section-highlight">
                <div class="categories-container">
                    <div class="categories-header">
                        <h2 class="categories-title">Our Product Categories</h2>
                        <p class="categories-subtitle">Browse our extensive collection of premium products organized by category</p>
                        <a href="all_categories.php?company_id=<?php echo $id; ?>" class="view-all-categories">
                            <i class="fas fa-th-list"></i> View All Categories
                        </a>
                    </div>
                    
                    <div class="categories-grid">
                        <?php if (!empty($main_categories)): ?>
                            <?php foreach ($main_categories as $category): ?>
                                <a href="category_products.php?main_category_id=<?php echo $category['id']; ?>&company_id=<?php echo $id; ?>" class="category-card">
                                    <div class="category-image">
                                        <?php if (!empty($category['image_path']) && isValidImage($category['image_path'])): ?>
                                            <?php echo displayHighQualityImage($category['image_path'], $category['name_ar'], 'category-img'); ?>
                                        <?php else: ?>
                                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: white;">
                                                <i class="<?php echo getCategoryIcon($category['name_ar']); ?>" style="font-size: 3rem;"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="category-info">
                                        <h3 class="category-name"><?php echo htmlspecialchars($category['name_ar']); ?></h3>
                                        <?php if (!empty($category['name_en'])): ?>
                                            <p style="color: #7f8c8d; margin-bottom: 10px;"><?php echo htmlspecialchars($category['name_en']); ?></p>
                                        <?php endif; ?>
                                        <div class="category-stats">
                                            <div class="stat-item">
                                                <span class="stat-number"><?php echo $category['subcategory_count']; ?></span>
                                                <span class="stat-label">Types</span>
                                            </div>
                                            <div class="stat-item">
                                                <span class="stat-number"><?php echo $category['product_count']; ?></span>
                                                <span class="stat-label">Products</span>
                                            </div>
                                        </div>
                                        <div class="view-products-btn">
                                            <i class="fas fa-arrow-right"></i> View Products
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="grid-column: 1 / -1; text-align: center; padding: 50px;">
                                <i class="fas fa-box-open" style="font-size: 4rem; color: #bdc3c7; margin-bottom: 20px;"></i>
                                <h3 style="color: #2c3e50; margin-bottom: 10px;">Categories Coming Soon</h3>
                                <p style="color: #7f8c8d;">Our product categories will be available soon</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <!-- Feedback Section -->
            <section id="feedback" class="section feedback-section section-highlight">
                <div class="container">
                    <div class="section-header">
                        <h2 class="section-title">Client Testimonials</h2>
                        <p class="section-subtitle">Hear what our valued clients say about their experience with us</p>
                        <div class="section-divider"></div>
                    </div>
                    <div class="feedback-container">
                        <!-- Feedback Form -->
                        <div class="feedback-form">
                            <div class="form-header">
                                <h3><i class="fas fa-comment-dots"></i> Share Your Experience</h3>
                                <p>We value your feedback and continuously strive to improve our services</p>
                            </div>
                            <div class="feedback-form-content">
                                <?php if (isset($feedback_success)): ?>
                                    <div class="feedback-alert success">
                                        <div class="feedback-alert-icon">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <div class="feedback-alert-content">
                                            <div class="feedback-alert-title">
                                                <i class="fas fa-check-circle"></i>
                                                Success!
                                            </div>
                                            <div class="feedback-alert-message">
                                                <?php echo $feedback_success; ?>
                                            </div>
                                        </div>
                                        <button class="feedback-alert-close" onclick="this.parentElement.style.display='none'">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                <?php endif; ?>
                                <?php if (isset($feedback_error)): ?>
                                    <div class="feedback-alert error">
                                        <div class="feedback-alert-icon">
                                            <i class="fas fa-exclamation-circle"></i>
                                        </div>
                                        <div class="feedback-alert-content">
                                            <div class="feedback-alert-title">
                                                <i class="fas fa-exclamation-circle"></i>
                                                Error
                                            </div>
                                            <div class="feedback-alert-message">
                                                <?php echo $feedback_error; ?>
                                            </div>
                                        </div>
                                        <button class="feedback-alert-close" onclick="this.parentElement.style.display='none'">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                <?php endif; ?>
                                
                                <form method="POST" class="feedback-form-content" id="feedbackForm" onsubmit="return validateFeedbackForm()">
                                    <div class="form-group">
                                        <label for="name">
                                            <i class="fas fa-user"></i>
                                            Your Full Name
                                        </label>
                                        <input type="text" id="name" name="name" required 
                                               placeholder="Enter your full name"
                                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                                               minlength="2"
                                               maxlength="100">
                                    </div>
                                    <div class="form-group">
                                        <label for="email">
                                            <i class="fas fa-envelope"></i>
                                            Your Email Address
                                        </label>
                                        <input type="email" id="email" name="email" required 
                                               placeholder="Enter your email address"
                                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="rating">
                                            <i class="fas fa-star"></i>
                                            Your Overall Rating
                                        </label>
                                        <div class="rating-input">
                                            <select id="rating" name="rating" required>
                                                <option value="">Select your rating</option>
                                                <option value="5" <?php echo ($_POST['rating'] ?? '') == '5' ? 'selected' : ''; ?>>★★★★★ Excellent - Exceeded Expectations</option>
                                                <option value="4" <?php echo ($_POST['rating'] ?? '') == '4' ? 'selected' : ''; ?>>★★★★ Very Good - Great Experience</option>
                                                <option value="3" <?php echo ($_POST['rating'] ?? '') == '3' ? 'selected' : ''; ?>>★★★ Good - Met Expectations</option>
                                                <option value="2" <?php echo ($_POST['rating'] ?? '') == '2' ? 'selected' : ''; ?>>★★ Fair - Room for Improvement</option>
                                                <option value="1" <?php echo ($_POST['rating'] ?? '') == '1' ? 'selected' : ''; ?>>★ Poor - Needs Significant Improvement</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="message">
                                            <i class="fas fa-edit"></i>
                                            Your Detailed Feedback
                                        </label>
                                        <textarea id="message" name="message" rows="5" required 
                                                  placeholder="Tell us about your experience with our company, products, or services..."
                                                  minlength="10"
                                                  maxlength="1000"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                                        <small style="color: #7f8c8d; font-size: 0.8rem;">Minimum 10 characters, maximum 1000 characters</small>
                                    </div>
                                    <button type="submit" name="submit_feedback" class="btn-primary btn-submit">
                                        <i class="fas fa-paper-plane"></i>
                                        Submit Your Feedback
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Recent Feedback -->
                        <div class="recent-feedback">
                            <div class="feedback-header">
                                <h3><i class="fas fa-comments"></i> What Our Clients Say</h3>
                                <p>Real feedback from our valued customers and partners</p>
                            </div>
                            <?php if (empty($feedbacks)): ?>
                                <div class="no-feedback">
                                    <i class="fas fa-comment-slash"></i>
                                    <h4>No Feedback Yet</h4>
                                    <p>Be the first to share your experience with our company!</p>
                                    <p>Your feedback helps us improve and serve you better.</p>
                                </div>
                            <?php else: ?>
                                <div class="feedback-list">
                                    <?php foreach ($feedbacks as $feedback): ?>
                                    <div class="feedback-item">
                                        <div class="feedback-header-info">
                                            <div class="feedback-user">
                                                <div class="user-avatar">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div class="user-info">
                                                    <strong><?php echo htmlspecialchars($feedback['name']); ?></strong>
                                                    <div class="rating">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <span class="star <?php echo $i <= $feedback['rating'] ? 'filled' : ''; ?>">★</span>
                                                        <?php endfor; ?>
                                                        <span class="rating-text">
                                                            <?php 
                                                            $rating_text = [
                                                                1 => 'Poor',
                                                                2 => 'Fair', 
                                                                3 => 'Good',
                                                                4 => 'Very Good',
                                                                5 => 'Excellent'
                                                            ];
                                                            echo $rating_text[$feedback['rating']] ?? 'Good';
                                                            ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <small class="feedback-date">
                                                <i class="fas fa-calendar"></i>
                                                <?php echo date('M j, Y', strtotime($feedback['created_at'])); ?>
                                            </small>
                                        </div>
                                        <p class="feedback-message">"<?php echo htmlspecialchars($feedback['message']); ?>"</p>
                                        <?php if (!empty($feedback['email'])): ?>
                                            <div class="feedback-email">
                                                <i class="fas fa-envelope"></i>
                                                <?php echo htmlspecialchars($feedback['email']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Fixed Bottom Brand Ads Strip -->
    <?php if (!empty($company_brands)): ?>
    <section class="brand-ads-strip">
        <div class="brands-track">
            <?php 
            // Create multiple copies for seamless scrolling
            $brandCopies = array_merge($company_brands, $company_brands, $company_brands);
            foreach ($brandCopies as $brand): 
                // Create URL for brand details page using brand ID
                $brandUrl = 'brand-details.php?brand_id=' . urlencode($brand['id']);
            ?>
                <a href="<?php echo htmlspecialchars($brandUrl); ?>" class="brand-item" target="_blank">
                    <div class="brand-logo">
                        <?php 
                        $brand_image_html = displayHighQualityImage(
                            $brand['brand_logo'] ?? '',
                            $brand['brand_name'],
                            '',
                            false
                        );
                        
                        if ($brand_image_html): 
                            echo $brand_image_html;
                        else: ?>
                            <i class="fas fa-crown"></i>
                        <?php endif; ?>
                    </div>
                    <div class="brand-info">
                        <div class="brand-name"><?php echo htmlspecialchars($brand['brand_name']); ?></div>
                        <div class="brand-tagline"><?php echo htmlspecialchars($brand['brand_description'] ?: 'Premium Quality Brand'); ?></div>
                    </div>
                    <div class="brand-badge">
                        <i class="fas fa-star"></i>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Professional Footer -->
    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-section">
                <h3>
                    <i class="fas fa-building"></i>
                    <?php echo htmlspecialchars($company['name']); ?>
                </h3>
                <p>Your trusted partner for business connections and professional networking opportunities. We deliver excellence through innovation and dedication.</p>
                <div class="footer-social">
                    <a href="https://www.facebook.com/DiraniCompany/" class="social-link" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://www.instagram.com/diranigroup/?hl=ar" class="social-link" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="social-link" title="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="social-link" title="LinkedIn">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                </div>
            </div>
            
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="#story"><i class="fas fa-book-open"></i> Our Story</a></li>
                    <li><a href="#team"><i class="fas fa-users"></i> Our Team</a></li>
                    <li><a href="#products"><i class="fas fa-boxes"></i> Products</a></li>
                    <li><a href="#feedback"><i class="fas fa-comment-dots"></i> Feedback</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="sales_order.php?company_id=<?php echo $id; ?>"><i class="fas fa-shopping-cart"></i> Place Order</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Contact Information</h4>
                <div class="contact-info">
                    <p><i class="fas fa-envelope"></i> Diranigroup@gmail.com</p>
                    <p><i class="fas fa-phone"></i> +9618911400</p>
                    <p><i class="fas fa-map-marker-alt"></i> Beqaa Qsarnaba Liban</p>
                    <p><i class="fas fa-clock"></i> Mon - Fri: 8:00 AM - 6:00 PM</p>
                </div>
            </div>
            
            <div class="footer-section">
                <h4>Quick Actions</h4>
                <div class="quick-actions">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <?php if (stripos($_SERVER['HTTP_USER_AGENT'], 'android') !== false): ?>
                            <button onclick="openAndroidAppWithFallback(<?php echo $id; ?>)" class="btn-android-login" style="width: 100%; margin-bottom: 10px;">
                                <i class="fab fa-android"></i> Open in App
                            </button>
                        <?php else: ?>
                            <a href="login.php?company_id=<?php echo $id; ?>" class="btn-primary" style="width: 100%; margin-bottom: 10px; display: block; text-align: center;">
                                <i class="fas fa-sign-in-alt"></i> Login to Order
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                    <a href="#feedback" class="btn-secondary" style="width: 100%; display: block; text-align: center;">
                        <i class="fas fa-comment"></i> Leave Feedback
                    </a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($company['name']); ?>. All rights reserved. | Created by Engineer Mahdi Alhajj Hassan</p>
        </div>
    </footer>

    <script>
        // Enhanced Team Section JavaScript with View All functionality
        let showAllMembers = false;
        let viewAllBtn = document.getElementById('viewAllBtn');
        
        // Toggle between showing 3 members and all members
        function toggleViewAllMembers() {
            const teamMembers = document.querySelectorAll('.team-member');
            
            if (!showAllMembers) {
                // Show all hidden members
                teamMembers.forEach((member, index) => {
                    if (index >= 3) {
                        member.style.display = 'flex';
                        member.classList.remove('hidden');
                        setTimeout(() => {
                            member.style.opacity = '1';
                            member.style.transform = 'translateY(0)';
                        }, index * 50);
                    }
                });
                
                // Update button text
                if (viewAllBtn) {
                    viewAllBtn.innerHTML = '<i class="fas fa-user-friends"></i> Show Less';
                }
                showAllMembers = true;
            } else {
                // Hide members beyond the first 3
                teamMembers.forEach((member, index) => {
                    if (index >= 3) {
                        member.style.opacity = '0';
                        member.style.transform = 'translateY(20px)';
                        
                        setTimeout(() => {
                            member.style.display = 'none';
                            member.classList.add('hidden');
                        }, 300);
                    }
                });
                
                // Update button text
                if (viewAllBtn) {
                    viewAllBtn.innerHTML = '<i class="fas fa-users"></i> View All Team Members';
                }
                showAllMembers = false;
            }
        }
        
        // Animate statistics counters
        function animateStatistics() {
            const counters = document.querySelectorAll('.team-stats .stat-number');
            const speed = 200; // The lower the slower
            
            counters.forEach(counter => {
                const target = +counter.innerText.replace('+', '');
                const increment = target / speed;
                let current = 0;
                
                // Check if the counter has a plus sign
                const hasPlus = counter.innerText.includes('+');
                const isPercent = counter.innerText.includes('%');
                
                const updateCount = () => {
                    if (current < target) {
                        current += increment;
                        
                        if (isPercent) {
                            counter.innerText = Math.ceil(current) + '%';
                        } else if (hasPlus) {
                            counter.innerText = Math.ceil(current) + '+';
                        } else {
                            counter.innerText = Math.ceil(current);
                        }
                        
                        setTimeout(updateCount, 1);
                    } else {
                        if (isPercent) {
                            counter.innerText = target + '%';
                        } else if (hasPlus) {
                            counter.innerText = target + '+';
                        } else {
                            counter.innerText = target;
                        }
                    }
                };
                
                updateCount();
            });
        }
        
        // Initialize team section on load
        document.addEventListener('DOMContentLoaded', function() {
            // Animate team statistics
            setTimeout(() => {
                animateStatistics();
            }, 1000);
            
            // Add View All button event listener
            if (viewAllBtn) {
                viewAllBtn.addEventListener('click', toggleViewAllMembers);
            }
            
            // Add click effect to team members on mobile
            if (window.innerWidth <= 768) {
                const teamMembers = document.querySelectorAll('.team-member');
                teamMembers.forEach(member => {
                    member.addEventListener('click', function() {
                        this.classList.toggle('expanded');
                        
                        // Close other expanded members
                        teamMembers.forEach(otherMember => {
                            if (otherMember !== member && otherMember.classList.contains('expanded')) {
                                otherMember.classList.remove('expanded');
                            }
                        });
                    });
                });
                
                // Close expanded member when clicking outside
                document.addEventListener('click', function(e) {
                    if (!e.target.closest('.team-member')) {
                        teamMembers.forEach(member => {
                            member.classList.remove('expanded');
                        });
                    }
                });
            }
        });
        
        // SMOOTH SCROLL INDICATOR SYSTEM (REPLACES POPUPS)
        const scrollIndicator = document.getElementById('scrollIndicator');
        const indicatorItems = document.querySelectorAll('.scroll-indicator-item');
        const sections = document.querySelectorAll('.section-highlight');
        const header = document.querySelector('.main-header');
        const navLinks = document.querySelectorAll('.nav-link');
        
        // Initialize scroll indicator
        function initScrollIndicator() {
            // Hide on mobile
            if (window.innerWidth <= 768) {
                if (scrollIndicator) scrollIndicator.style.display = 'none';
                return;
            }
            
            // Show after 2 seconds
            setTimeout(() => {
                if (scrollIndicator) {
                    scrollIndicator.style.opacity = '0';
                    scrollIndicator.style.display = 'flex';
                    setTimeout(() => {
                        scrollIndicator.style.opacity = '1';
                    }, 100);
                }
            }, 2000);
        }
        
        // Scroll event handler
        window.addEventListener('scroll', () => {
            // Header scroll effect
            if (window.scrollY > 100) {
                header.classList.add('scrolled');
                if (scrollIndicator) scrollIndicator.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
                if (scrollIndicator) scrollIndicator.classList.remove('scrolled');
            }
            
            // Update active section in scroll indicator
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop - 150;
                const sectionHeight = section.clientHeight;
                if (scrollY >= sectionTop && scrollY < sectionTop + sectionHeight) {
                    current = section.getAttribute('id');
                    
                    // Add active class to section
                    sections.forEach(s => s.classList.remove('active'));
                    section.classList.add('active');
                }
            });
            
            // Update scroll indicator items
            indicatorItems.forEach(item => {
                item.classList.remove('active');
                if (item.getAttribute('href') === `#${current}`) {
                    item.classList.add('active');
                }
            });
            
            // Update navigation links
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${current}`) {
                    link.classList.add('active');
                }
            });
        });
        
        // Scroll indicator click handler
        indicatorItems.forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const targetId = item.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 100,
                        behavior: 'smooth'
                    });
                    
                    // Add subtle visual feedback
                    indicatorItems.forEach(i => i.classList.remove('active'));
                    item.classList.add('active');
                    item.style.transform = 'scale(1.2)';
                    setTimeout(() => {
                        item.style.transform = 'scale(1.1)';
                    }, 300);
                }
            });
        });
        
        // MINIMAL TOAST FUNCTIONS
        function showToast(type, title, message, duration = 5000) {
            const toast = document.getElementById('feedbackToast');
            if (!toast) return;
            
            const toastIcon = toast.querySelector('.minimal-toast-icon i');
            const toastTitle = document.getElementById('toastTitle');
            const toastMessage = document.getElementById('toastMessage');
            
            // Set toast content
            toastTitle.textContent = title;
            toastMessage.textContent = message;
            
            // Set toast type
            toast.className = 'minimal-toast';
            if (type === 'error') {
                toast.classList.add('error');
                toastIcon.className = 'fas fa-exclamation-circle';
            } else {
                toastIcon.className = 'fas fa-check-circle';
            }
            
            // Show toast
            setTimeout(() => {
                toast.classList.add('show');
            }, 100);
            
            // Auto-hide after duration
            if (duration > 0) {
                setTimeout(() => {
                    hideToast();
                }, duration);
            }
        }
        
        function hideToast() {
            const toast = document.getElementById('feedbackToast');
            if (toast) toast.classList.remove('show');
        }
        
        // Show feedback toast if success/error exists
        <?php if (isset($feedback_success)): ?>
            setTimeout(() => {
                showToast('success', 'Success!', '<?php echo addslashes($feedback_success); ?>', 5000);
            }, 1000);
        <?php endif; ?>
        
        <?php if (isset($feedback_error)): ?>
            setTimeout(() => {
                showToast('error', 'Error', '<?php echo addslashes($feedback_error); ?>', 5000);
            }, 1000);
        <?php endif; ?>
        
        // Brand ads controls
        let brandsAnimation = null;
        let isBrandsPaused = false;
        
        function initBrandsAnimation() {
            const brandsTrack = document.querySelector('.brands-track');
            if (!brandsTrack) return;
            
            brandsAnimation = brandsTrack.style.animationPlayState;
        }
        
        // Android app login function
        function openAndroidAppWithFallback(companyId) {
            // Try to open Android app
            window.location.href = 'companyapp://login?company_id=' + companyId;
            
            // Show fallback option after 1 second
            setTimeout(function() {
                const fallback = document.querySelector('.fallback-link');
                if (fallback) {
                    fallback.style.display = 'block';
                }
            }, 1000);
            
            // Auto-redirect to web after 3 seconds
            setTimeout(function() {
                window.location.href = 'login.php?company_id=' + companyId + '&web=true';
            }, 3000);
        }
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if(targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if(targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 100,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Add scroll animation to elements
        document.addEventListener('DOMContentLoaded', function() {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);
            
            // Observe elements for animation
            const elementsToAnimate = document.querySelectorAll('.category-card, .team-member, .achievement-item');
            elementsToAnimate.forEach(element => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                element.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                observer.observe(element);
            });
        });
        
        // Feedback form validation
        function validateFeedbackForm() {
            const form = document.getElementById('feedbackForm');
            if (!form) return true;
            
            const name = form.querySelector('#name');
            const email = form.querySelector('#email');
            const message = form.querySelector('#message');
            
            let isValid = true;
            
            // Reset previous error states
            [name, email, message].forEach(field => {
                if (!field) return;
                field.style.borderColor = '';
                const errorMsg = field.nextElementSibling;
                if (errorMsg && errorMsg.classList.contains('error-message')) {
                    errorMsg.remove();
                }
            });
            
            // Validate name
            if (name && name.value.trim().length < 2) {
                showFieldError(name, 'Name must be at least 2 characters long');
                isValid = false;
            }
            
            // Validate email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email && !emailRegex.test(email.value.trim())) {
                showFieldError(email, 'Please enter a valid email address');
                isValid = false;
            }
            
            // Validate message
            if (message && message.value.trim().length < 10) {
                showFieldError(message, 'Message must be at least 10 characters long');
                isValid = false;
            }
            
            if (message && message.value.trim().length > 1000) {
                showFieldError(message, 'Message must not exceed 1000 characters');
                isValid = false;
            }
            
            if (!isValid) {
                showToast('error', 'Validation Error', 'Please check the highlighted fields and try again.', 5000);
            }
            
            return isValid;
        }
        
        function showFieldError(field, message) {
            if (!field) return;
            
            field.style.borderColor = '#ef233c';
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.style.color = '#ef233c';
            errorDiv.style.fontSize = '0.85rem';
            errorDiv.style.marginTop = '5px';
            errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
            field.parentNode.appendChild(errorDiv);
        }
        
       // Slideshow JavaScript
let currentSlideIndex = 0;
let slideshowInterval;
const slides = document.querySelectorAll('.slide');
const dots = document.querySelectorAll('.dot');

function showSlides(index) {
    if (!slides.length) return;
    
    // Handle index bounds
    if (index >= slides.length) currentSlideIndex = 0;
    if (index < 0) currentSlideIndex = slides.length - 1;
    
    // Hide all slides
    slides.forEach((slide, i) => {
        slide.style.display = 'none';
        
        // Pause any videos in hidden slides
        const iframe = slide.querySelector('iframe');
        if (iframe) {
            // Stop YouTube video by resetting src
            const src = iframe.src;
            iframe.src = '';
            iframe.src = src;
        }
        
        const video = slide.querySelector('video');
        if (video) {
            video.pause();
        }
    });
    
    // Show current slide
    slides[currentSlideIndex].style.display = 'block';
    
    // Update dots
    dots.forEach((dot, i) => {
        dot.classList.remove('active');
        if (i === currentSlideIndex) {
            dot.classList.add('active');
        }
    });
}

function changeSlide(direction) {
    currentSlideIndex += direction;
    showSlides(currentSlideIndex);
    resetSlideshowTimer();
}

function currentSlide(index) {
    currentSlideIndex = index;
    showSlides(currentSlideIndex);
    resetSlideshowTimer();
}

function resetSlideshowTimer() {
    if (slideshowInterval) {
        clearInterval(slideshowInterval);
    }
    slideshowInterval = setInterval(() => {
        changeSlide(1);
    }, 5000);
}

// Initialize slideshow when page loads
document.addEventListener('DOMContentLoaded', function() {
    if (slides.length > 0) {
        showSlides(0);
        resetSlideshowTimer();
        
        // Pause slideshow on hover
        const slideshowContainer = document.querySelector('.slideshow-container');
        if (slideshowContainer) {
            slideshowContainer.addEventListener('mouseenter', function() {
                if (slideshowInterval) {
                    clearInterval(slideshowInterval);
                }
            });
            
            slideshowContainer.addEventListener('mouseleave', function() {
                resetSlideshowTimer();
            });
        }
    }
});
    </script>
</body>
</html>