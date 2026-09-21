<?php
// all_categories.php
include 'config.php';

// Start session only if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get company ID from URL
$company_id = isset($_GET['company_id']) ? intval($_GET['company_id']) : 0;

// Initialize variables to avoid undefined errors
$company = null;
$total_categories = 0;
$total_subcategories = 0;
$total_products = 0;
$all_categories = [];

try {
    // Fetch company details
    if ($company_id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM companies WHERE id = ?");
        $stmt->execute([$company_id]);
        $company = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // If company not found, show error
    if (!$company && $company_id > 0) {
        die("Company not found. Please select a valid company.");
    }
    
    // Get total main categories count
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM main_categories");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_categories = $result ? $result['count'] : 0;
    
    // Get total subcategories count
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM sub_categories");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_subcategories = $result ? $result['count'] : 0;
    
    // Get total products count from products table
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_products = $result ? $result['count'] : 0;
    
    // Get all main categories with their subcategories count
    $stmt = $pdo->prepare("
        SELECT 
            mc.*,
            COUNT(DISTINCT sc.id) as subcategory_count
        FROM main_categories mc
        LEFT JOIN sub_categories sc ON mc.id = sc.main_category_id
        GROUP BY mc.id
        ORDER BY mc.display_order ASC, mc.name_ar ASC
    ");
    $stmt->execute();
    $all_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // For each main category, get the products count through subcategories
    foreach ($all_categories as &$category) {
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT p.id) as product_count 
            FROM products p
            INNER JOIN sub_categories sc ON p.sub_category_id = sc.id
            WHERE sc.main_category_id = ?
        ");
        $stmt->execute([$category['id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $category['product_count'] = $result ? $result['product_count'] : 0;
    }
    unset($category); // Unset reference
    
} catch(PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>جميع الأصناف والمنتجات</title>
    <link rel="stylesheet" href="CSS/all_categories.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
      
    </style>
</head>
<body>
    <!-- Professional Navigation Bar -->
    <header class="main-header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <a href="index.php">
                        <i class="fas fa-building"></i>
                        ديراني غروب
                    </a>
                </div>
                <div class="nav-menu">
                    <a href="index.php">
                        <i class="fas fa-home"></i>
                        الرئيسية
                    </a>
                    <a href="#" class="active">
                        <i class="fas fa-th-large"></i>
                        جميع الأصناف
                    </a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="logout.php" class="btn-secondary">
                            <i class="fas fa-sign-out-alt"></i>
                            تسجيل خروج
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn-primary">
                            <i class="fas fa-sign-in-alt"></i>
                            تسجيل دخول
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <div class="container">
            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <a href="index.php">
                    <i class="fas fa-home"></i>
                    الرئيسية
                </a> /
                <?php if ($company): ?>
                    <a href="company_profile.php?id=<?php echo $company_id; ?>">
                        <i class="fas fa-building"></i>
                        <?php echo htmlspecialchars($company['name']); ?>
                    </a> /
                <?php endif; ?>
                <span>
                    <i class="fas fa-th-large"></i>
                    جميع الأصناف
                </span>
            </div>

            <!-- Page Header -->
            <div class="page-header">
                <h1>جميع الأصناف والمنتجات</h1>
                <?php if ($company): ?>
                    <p class="hero-subtitle"><?php echo htmlspecialchars($company['name']); ?> - اكتشف مجموعتنا الكاملة من المنتجات</p>
                <?php else: ?>
                    <p class="hero-subtitle">نظرة عامة على منتجاتنا</p>
                <?php endif; ?>
                
                <?php if ($company): ?>
                    <a href="company_profile.php?id=<?php echo $company_id; ?>" class="back-btn">
                        <i class="fas fa-arrow-right"></i>
                        العودة إلى صفحة الشركة
                    </a>
                <?php endif; ?>
            </div>

            <!-- Search Bar -->
            <div class="search-container">
                <input type="text" class="search-input" placeholder="ابحث عن صنف معين..." id="searchInput">
                <button class="search-button">
                    <i class="fas fa-search"></i>
                </button>
            </div>

            <!-- Stats Cards -->
            <div class="stats-cards">
                <div class="stat-card">
                    <i class="fas fa-layer-group"></i>
                    <span class="stat-number"><?php echo $total_categories; ?></span>
                    <span class="stat-label">صنف رئيسي</span>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-sitemap"></i>
                    <span class="stat-number"><?php echo $total_subcategories; ?></span>
                    <span class="stat-label">نوع فرعي</span>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-box"></i>
                    <span class="stat-number"><?php echo $total_products; ?></span>
                    <span class="stat-label">منتج مختلف</span>
                </div>
            </div>

            <!-- Categories Section -->
            <div class="categories-section">
                <h2>الأصناف الرئيسية</h2>
                <p>تصفح جميع أصناف المنتجات المتوفرة</p>
                
                <?php if (empty($all_categories)): ?>
                    <div class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <h3>لا توجد أصناف متاحة</h3>
                        <p>لم يتم إضافة أي أصناف بعد. يرجى التحقق لاحقاً.</p>
                        <?php if ($company): ?>
                            <a href="company_profile.php?id=<?php echo $company_id; ?>" class="btn-primary" style="display: inline-block; padding: 12px 30px;">
                                <i class="fas fa-arrow-right"></i>
                                العودة إلى الشركة
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="categories-grid" id="categoriesGrid">
                        <?php foreach ($all_categories as $index => $category): ?>
                        <div class="category-card" 
                             data-name-ar="<?php echo htmlspecialchars(strtolower($category['name_ar'])); ?>"
                             data-name-en="<?php echo htmlspecialchars(strtolower($category['name_en'] ?? '')); ?>">
                            <?php if ($category['product_count'] > 0): ?>
                                <span class="category-badge">
                                    <i class="fas fa-box"></i>
                                    <?php echo $category['product_count']; ?> منتج
                                </span>
                            <?php endif; ?>
                            
                            <div class="category-image">
                                <?php if (!empty($category['image_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($category['image_path']); ?>" 
                                         alt="<?php echo htmlspecialchars($category['name_ar']); ?>"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                                    <div style="display: none; align-items: center; justify-content: center; width: 100%; height: 100%; color: white;">
                                        <i class="fas fa-layer-group" style="font-size: 3rem;"></i>
                                    </div>
                                <?php else: ?>
                                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: white;">
                                        <i class="fas fa-layer-group" style="font-size: 3rem;"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="category-content">
                                <div class="dual-language">
                                    <h3 class="name-ar"><?php echo htmlspecialchars($category['name_ar']); ?></h3>
                                    <?php if (!empty($category['name_en'])): ?>
                                        <span class="name-en"><?php echo htmlspecialchars($category['name_en']); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="category-meta">
                                    <span>
                                        <i class="fas fa-sitemap"></i>
                                        <?php echo $category['subcategory_count']; ?> نوع فرعي
                                    </span>
                                    <span>
                                        <i class="fas fa-box"></i>
                                        <?php echo $category['product_count']; ?> منتج
                                    </span>
                                </div>
                                
                                <?php if ($company_id > 0): ?>
                                    <a href="category_products.php?main_category_id=<?php echo $category['id']; ?>&company_id=<?php echo $company_id; ?>" 
                                       class="view-products-btn">
                                        <i class="fas fa-eye"></i>
                                        عرض المنتجات
                                    </a>
                                <?php else: ?>
                                    <button class="view-products-btn" onclick="showCompanySelection(<?php echo $category['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                        عرض المنتجات
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Enhanced Professional Footer -->
    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-section">
                <h3>
                    <i class="fas fa-building"></i>
                    ديراني غروب
                </h3>
                <p>منصة رائدة في عرض وتسويق المنتجات من الشركات الرائدة في السوق. نقدم حلولاً متكاملة لتسهيل الوصول إلى المنتجات والخدمات.</p>
                <div class="footer-social">
                    <a href="https://www.facebook.com/DiraniCompany/" class="social-link" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://www.instagram.com/diranigroup/?hl=ar" class="social-link" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://twitter.com/" class="social-link" title="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://linkedin.com/" class="social-link" title="LinkedIn">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                </div>
            </div>
            
            <div class="footer-section">
                <h4>
                    <i class="fas fa-link"></i>
                    روابط سريعة
                </h4>
                <div class="quick-links">
                    <a href="index.php">
                        <i class="fas fa-home"></i>
                        الرئيسية
                    </a>
                    <a href="all_categories.php">
                        <i class="fas fa-th-large"></i>
                        جميع الأصناف
                    </a>
                    <a href="about.php">
                        <i class="fas fa-info-circle"></i>
                        عن الشركة
                    </a>
                    <a href="contact.php">
                        <i class="fas fa-envelope"></i>
                        اتصل بنا
                    </a>
                    <a href="privacy.php">
                        <i class="fas fa-shield-alt"></i>
                        سياسة الخصوصية
                    </a>
                </div>
            </div>
            
            <div class="footer-section">
                <h4>
                    <i class="fas fa-address-card"></i>
                    معلومات الاتصال
                </h4>
                <div class="contact-info">
                    <p>
                        <i class="fas fa-envelope"></i>
                        <span>info@diranigroup.com</span>
                    </p>
                    <p>
                        <i class="fas fa-phone"></i>
                        <span>+961 1 123 456</span>
                    </p>
                    <p>
                        <i class="fas fa-mobile-alt"></i>
                        <span>+961 70 123 456</span>
                    </p>
                    <p>
                        <i class="fas fa-map-marker-alt"></i>
                        <span>بيروت، شارع الحمراء، لبنان</span>
                    </p>
                    <p>
                        <i class="fas fa-clock"></i>
                        <span>الأحد - الخميس: 8:00 صباحاً - 5:00 مساءً</span>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2025 ديراني غروب. جميع الحقوق محفوظة.</p>
            <p>تم التطوير بواسطة المهندس مهدي الحاج حسن</p>
            <div class="copyright-links">
                <a href="terms.php">شروط الاستخدام</a>
                <a href="privacy.php">سياسة الخصوصية</a>
                <a href="sitemap.php">خريطة الموقع</a>
                <a href="contact.php">الدعم الفني</a>
            </div>
        </div>
    </footer>

    <script>
    function openAndroidApp() {
        console.log('Opening Android app...');
        
        // Try to open app directly
        window.location.href = 'companyapp://login';
        
        // Fallback to web login
        setTimeout(function() {
            console.log('App not opened, redirecting to web login...');
            window.location.href = 'login.php';
        }, 3000);
    }
    </script>
</body>
</html>