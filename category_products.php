<?php
// category_products.php - Show products by category
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config.php';

// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get parameters
$main_category_id = $_GET['main_category_id'] ?? null;
$company_id = $_GET['company_id'] ?? null;

// If no company_id in URL, try to get from session or default
if (!$company_id) {
    $company_id = 11; // Your company ID
}

if (!$main_category_id || !$company_id) {
    header("Location: index.php");
    exit;
}

// Fetch category details
$stmt = $pdo->prepare("SELECT * FROM main_categories WHERE id = ?");
$stmt->execute([$main_category_id]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    header("Location: index.php");
    exit;
}

// Fetch company details
$stmt = $pdo->prepare("SELECT * FROM companies WHERE id = ?");
$stmt->execute([$company_id]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

// Function to get brands for a product
function getProductBrands($pdo, $product_id, $company_id) {
    try {
        // Check if product_brands table exists first
        $stmt = $pdo->prepare("SHOW TABLES LIKE 'product_brands'");
        $stmt->execute();
        if ($stmt->rowCount() == 0) {
            return [];
        }
        
        $stmt = $pdo->prepare("
            SELECT cb.*, cb.brand_logo as brand_logo
            FROM company_brands cb
            INNER JOIN product_brands pb ON cb.id = pb.brand_id
            WHERE pb.product_id = ? AND cb.company_id = ?
            ORDER BY cb.brand_name ASC
        ");
        $stmt->execute([$product_id, $company_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

// SIMPLIFIED Function to get packaging for a product
function getProductPackaging($pdo, $product_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM packaging 
            WHERE product_id = ? 
            ORDER BY 
                CASE 
                    WHEN packaging_type LIKE '%صندوق%' THEN 1
                    WHEN packaging_type LIKE '%سطل%' THEN 2
                    WHEN packaging_type LIKE '%تنك%' THEN 3
                    WHEN packaging_type LIKE '%مرطبان%' THEN 4
                    WHEN packaging_type LIKE '%غالون%' THEN 5
                    ELSE 6
                END,
                weight ASC
        ");
        $stmt->execute([$product_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

// Function to get base price from packaging
function getProductBasePrice($pdo, $product_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT MIN(price) as min_price, MAX(price) as max_price
            FROM packaging 
            WHERE product_id = ? AND price > 0
        ");
        $stmt->execute([$product_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return null;
    }
}

// Fetch all categories for navigation
$categories_stmt = $pdo->prepare("SELECT id, name_ar, name_en FROM main_categories ORDER BY name_ar");
$categories_stmt->execute();
$all_categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch products for this category
$stmt = $pdo->prepare("
    SELECT p.*, sc.name_ar as subcategory_name
    FROM products p
    LEFT JOIN sub_categories sc ON p.sub_category_id = sc.id
    WHERE sc.main_category_id = ? AND p.company_id = ?
    ORDER BY sc.name_ar, p.product_name_ar
");
$stmt->execute([$main_category_id, $company_id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user info if logged in
$user_name = $_SESSION['username'] ?? null;
$user_role = $_SESSION['role'] ?? null;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category['name_ar'] ?? 'المنتجات'); ?> - <?php echo htmlspecialchars($company['name'] ?? 'الشركة'); ?></title>
    
    <link rel="stylesheet" href="CSS/category_products.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
</head>
<body>
    <!-- Professional Navigation Bar -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="company_profile.php?id=<?php echo $company_id; ?>" class="navbar-brand">
                <div class="brand-logo">
                    <img src="uploads\campanylogo\logo.jpg" alt="Logo" class="building-logo" width="48" height="48">
                </div>
                <div class="brand-text">
                    <h1><?php echo htmlspecialchars($company['name'] ?? 'متجر'); ?></h1>
                    <p>جودة وتميز في كل منتج</p>
                </div>
            </a>
            
            <div class="navbar-links">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-home"></i>
                    الرئيسية
                </a>

                <a href="#" class="nav-link active">
                    <i class="fas fa-boxes"></i>
                    المنتجات
                </a>
                <?php if ($user_name): ?>
                    <a href="sales_order.php?company_id=<?php echo $company_id; ?>" class="nav-link btn-secondary">
                        <i class="fas fa-shopping-cart"></i>
                        طلب سريع
                    </a>
                <?php else: ?>
                    <a href="login.php?company_id=<?php echo $company_id; ?>" class="nav-link btn-secondary">
                        <i class="fas fa-sign-in-alt"></i>
                        تسجيل الدخول للطلب
                    </a>
                <?php endif; ?>
            </div>
            
            <?php if ($user_name): ?>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                    </div>
                    <span class="user-name"><?php echo htmlspecialchars($user_name); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Category Navigation -->
    <div class="filter-section">
        <div class="filter-grid">
            <div class="filter-group">
                <label class="filter-label">
                    <i class="fas fa-filter"></i>
                    تصفية حسب الفئة
                </label>
                <select class="filter-select" id="categoryFilter" onchange="filterProducts(this.value)">
                    <option value="">جميع الفئات</option>
                    <?php foreach ($all_categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" 
                                <?php echo ($cat['id'] == $main_category_id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name_ar']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">
                    <i class="fas fa-sort-amount-down"></i>
                    ترتيب حسب
                </label>
                <select class="filter-select" id="sortFilter" onchange="sortProducts(this.value)">
                    <option value="name_asc">الإسم (أ-ي)</option>
                    <option value="name_desc">الإسم (ي-أ)</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Brand Full View Modal -->
    <div class="brand-full-modal" id="brandFullModal">
        <div class="brand-full-content">
            <button class="brand-full-close" onclick="closeBrandView()">&times;</button>
            <div class="brand-full-image-container" id="brandFullImageContainer">
                <!-- Brand image will be inserted here -->
            </div>
            <div class="brand-full-info">
                <h2 class="brand-full-name" id="brandFullName"></h2>
                <p class="brand-full-description" id="brandFullDescription">
                    هذه العلامة التجارية متوفرة في منتجاتنا بجودة عالية وأسعار منافسة.
                </p>
               
                    <button class="btn btn-outline" onclick="closeBrandView()">
                        <i class="fas fa-times"></i>
                        إغلاق
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick View Modal for Products -->
    <div class="quick-view-modal" id="quickViewModal">
        <div class="quick-view-content">
            <button class="quick-view-close" onclick="closeQuickView()">&times;</button>
            <div class="quick-view-image-container" id="quickViewImageContainer">
                <!-- Image will be inserted here by JavaScript -->
            </div>
            <div class="quick-view-info">
                <h3 id="quickViewProductName"></h3>
            </div>
        </div>
    </div>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title"><?php echo htmlspecialchars($category['name_ar']); ?></h1>
            <?php if (!empty($category['name_en'])): ?>
                <p class="page-subtitle"><?php echo htmlspecialchars($category['name_en']); ?></p>
            <?php endif; ?>
            <p class="page-subtitle">اكتشف مجموعتنا المميزة من المنتجات في هذه الفئة</p>
            
            <div class="header-actions">
                <a href="company_profile.php?id=<?php echo $company_id; ?>" class="btn btn-outline">
                    <i class="fas fa-arrow-right"></i>
                    العودة للشركة
                </a>
                <?php if ($user_name): ?>
                    <a href="sales_order.php?company_id=<?php echo $company_id; ?>" class="btn btn-primary">
                        <i class="fas fa-bolt"></i>
                        طلب سريع
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (empty($products)): ?>
            <div class="no-products">
                <i class="fas fa-box-open"></i>
                <h3 style="margin: 20px 0 10px; color: var(--dark-color);">لا توجد منتجات</h3>
                <p style="color: var(--gray-color); margin-bottom: 30px;">لم يتم العثور على منتجات في هذه الفئة بعد.</p>
                <a href="company_profile.php?id=<?php echo $company_id; ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-right"></i>
                    العودة للشركة
                </a>
            </div>
        <?php else: ?>
            <div class="products-grid" id="productsGrid">
                <?php foreach ($products as $product): ?>
                    <?php 
                    $brands = getProductBrands($pdo, $product['id'], $company_id);
                    $packaging = getProductPackaging($pdo, $product['id']);
                    $priceInfo = getProductBasePrice($pdo, $product['id']);
                    
                    // Get product image and name
                    $productImage = !empty($product['product_image']) ? $product['product_image'] : '';
                    $productName = htmlspecialchars($product['product_name_ar']);
                    
                    // Filter packaging to show only those with data
                    $validPackaging = array_filter($packaging, function($pack) {
                        return !empty($pack['packaging_type']) || 
                               !empty($pack['quantity_per_pack']) || 
                               !empty($pack['weight']) || 
                               !empty($pack['master_pack']) || 
                               (!empty($pack['price']) && $pack['price'] > 0);
                    });
                    ?>
                    <div class="product-card" data-category="<?php echo $main_category_id; ?>" 
                         data-price="<?php echo $priceInfo ? ($priceInfo['min_price'] ?? 0) : 0; ?>"
                         data-product-id="<?php echo $product['id']; ?>">
                        <?php if ($priceInfo && $priceInfo['min_price'] > 0): ?>
                            <span class="product-badge">
                                <i class="fas fa-tag"></i>
                                <?php if ($priceInfo['min_price'] == $priceInfo['max_price']): ?>
                                    <?php echo number_format($priceInfo['min_price'], 2); ?> ر.س
                                <?php else: ?>
                                    <?php echo number_format($priceInfo['min_price'], 2); ?> - <?php echo number_format($priceInfo['max_price'], 2); ?> ر.س
                                <?php endif; ?>
                            </span>
                        <?php endif; ?>
                        
                        <div class="product-image">
                            <?php if (!empty($productImage)): ?>
                                <img src="<?php echo htmlspecialchars($productImage); ?>" 
                                     alt="<?php echo $productName; ?>"
                                     style="width: 100%; height: 100%; object-fit: cover;"
                                     class="product-main-image"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                            <?php endif; ?>
                            
                            <div style="display: <?php echo empty($productImage) ? 'flex' : 'none'; ?>; align-items: center; justify-content: center; width: 100%; height: 100%; flex-direction: column; color: white;">
                                <i class="fas fa-box-open" style="font-size: 4rem; opacity: 0.7;"></i>
                                <p style="margin-top: 10px; font-size: 0.9rem;"><?php echo $productName; ?></p>
                            </div>
                            
                            <div class="product-overlay">
                                <button class="quick-view-btn" 
                                        onclick="openQuickView(
                                            <?php echo $product['id']; ?>, 
                                            '<?php echo addslashes($productImage); ?>', 
                                            '<?php echo addslashes($productName); ?>'
                                        )">
                                    <i class="fas fa-eye"></i>
                                    عرض سريع
                                </button>
                            </div>
                        </div>
                        
                        <div class="product-info">
                            <div class="product-header">
                                <div>
                                    <h3 class="product-name"><?php echo $productName; ?></h3>
                                    <?php if (!empty($product['subcategory_name'])): ?>
                                        <span class="product-category">
                                            <i class="fas fa-tag"></i>
                                            <?php echo htmlspecialchars($product['subcategory_name']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($priceInfo && $priceInfo['min_price'] > 0): ?>
                                    <div class="product-price">
                                        <?php if ($priceInfo['min_price'] == $priceInfo['max_price']): ?>
                                            <?php echo number_format($priceInfo['min_price'], 2); ?> ر.س
                                        <?php else: ?>
                                            من <?php echo number_format($priceInfo['min_price'], 2); ?> ر.س
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Brands Section with Images -->
                            <div class="info-section">
                                <div class="section-header">
                                    <i class="fas fa-crown"></i>
                                    <h4>العلامات التجارية</h4>
                                </div>
                                <?php if (!empty($brands)): ?>
                                    <div class="brands-container">
                                        <?php foreach ($brands as $brand): ?>
                                            <div class="brand-image-item" 
                                               onclick="showBrandFullView('<?php echo addslashes($brand['brand_logo']); ?>', '<?php echo addslashes($brand['brand_name']); ?>', '<?php echo $brand['id']; ?>')"
                                               title="انقر لعرض صورة أكبر - <?php echo htmlspecialchars($brand['brand_name']); ?>">
                                                <div class="brand-image-wrapper">
                                                    <?php if (!empty($brand['brand_logo'])): ?>
                                                        <img src="<?php echo htmlspecialchars($brand['brand_logo']); ?>" 
                                                             alt="<?php echo htmlspecialchars($brand['brand_name']); ?>"
                                                             class="brand-image"
                                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                                                    <?php endif; ?>
                                                    <div style="display: <?php echo empty($brand['brand_logo']) ? 'flex' : 'none'; ?>; align-items: center; justify-content: center; width: 100%; height: 100%;">
                                                        <i class="fas fa-crown" style="font-size: 2rem; color: var(--primary-color);"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p style="color: var(--gray-color); text-align: center; font-style: italic;">
                                        <i class="fas fa-info-circle"></i>
                                        لا توجد علامات تجارية
                                    </p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- SIMPLE PACKAGING SECTION - NO SLIDER COMPLEXITY -->
                            <div class="info-section">
                                <div class="section-header">
                                    <i class="fas fa-box"></i>
                                    <h4>خيارات التعبئة</h4>
                                </div>
                                
                                <?php if (!empty($validPackaging)): ?>
                                    <div class="packaging-container" id="packagingContainer-<?php echo $product['id']; ?>">
                                        <?php foreach ($validPackaging as $index => $pack): ?>
                                            <div class="packaging-item" style="margin-bottom: 10px; display: <?php echo $index === 0 ? 'block' : 'none'; ?>;" 
                                                 data-index="<?php echo $index; ?>">
                                                <div class="packaging-type">
                                                    <i class="fas fa-box"></i>
                                                    <?php echo !empty($pack['packaging_type']) ? htmlspecialchars($pack['packaging_type']) : 'نوع التعبئة'; ?>
                                                </div>
                                                <div class="packaging-details">
                                                    <?php if (!empty($pack['quantity_per_pack'])): ?>
                                                        <span class="detail-badge detail-quantity">
                                                            <i class="fas fa-layer-group"></i>
                                                            <?php echo $pack['quantity_per_pack']; ?> قطعة
                                                        </span>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($pack['weight']) && !empty($pack['weight_unit'])): ?>
                                                        <span class="detail-badge detail-weight">
                                                            <i class="fas fa-weight-hanging"></i>
                                                            <?php echo $pack['weight']; ?> <?php echo $pack['weight_unit']; ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($pack['master_pack'])): ?>
                                                        <span class="detail-badge detail-master">
                                                            <i class="fas fa-boxes"></i>
                                                            <?php echo htmlspecialchars($pack['master_pack']); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($pack['price']) && $pack['price'] > 0): ?>
                                                        <span class="detail-badge detail-price">
                                                            <i class="fas fa-dollar-sign"></i>
                                                            <?php echo number_format($pack['price'], 2); ?> ر.س
                                                        </span>
                                                    <?php endif; ?>
                                                    
                                                    <?php 
                                                    $hasVisibleDetails = (!empty($pack['quantity_per_pack']) || 
                                                                         (!empty($pack['weight']) && !empty($pack['weight_unit'])) || 
                                                                         !empty($pack['master_pack']) || 
                                                                         (!empty($pack['price']) && $pack['price'] > 0));
                                                    ?>
                                                    
                                                    <?php if (!$hasVisibleDetails && !empty($pack['packaging_type'])): ?>
                                                        <div class="empty-packaging-message">
                                                            <i class="fas fa-info-circle"></i>
                                                            <p><strong><?php echo htmlspecialchars($pack['packaging_type']); ?></strong></p>
                                                            <p><small>التفاصيل غير متوفرة حالياً</small></p>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <?php if (count($validPackaging) > 1): ?>
                                        <div class="packaging-nav" style="display: flex; justify-content: center; align-items: center; gap: 15px; margin-top: 10px;">
                                            <button class="slider-btn prev-btn" onclick="changePackaging(<?php echo $product['id']; ?>, -1)">
                                                <i class="fas fa-chevron-right"></i>
                                            </button>
                                            <div class="slider-counter">
                                                <i class="fas fa-box"></i>
                                                <span id="packagingCounter-<?php echo $product['id']; ?>">1/<?php echo count($validPackaging); ?></span>
                                            </div>
                                            <button class="slider-btn next-btn" onclick="changePackaging(<?php echo $product['id']; ?>, 1)">
                                                <i class="fas fa-chevron-left"></i>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p style="color: var(--gray-color); text-align: center; font-style: italic; padding: 20px; background: #f5f5f5; border-radius: 10px;">
                                        <i class="fas fa-info-circle"></i>
                                        لا توجد خيارات تعبئة متاحة لهذا المنتج
                                    </p>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($user_name): ?>
                                <a href="sales_order.php?company_id=<?php echo $company_id; ?>&product_id=<?php echo $product['id']; ?>" 
                                   class="btn btn-primary" style="width: 100%; margin-top: 15px;">
                                    <i class="fas fa-cart-plus"></i>
                                    طلب المنتج
                                </a>
                            <?php else: ?>
                                <a href="login.php?company_id=<?php echo $company_id; ?>" class="btn btn-secondary" style="width: 100%; margin-top: 15px;">
                                    <i class="fas fa-sign-in-alt"></i>
                                    سجل الدخول للطلب
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Professional Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <h3><?php echo htmlspecialchars($company['name'] ?? 'الشركة'); ?></h3>
                <p style="color: #bdc3c7; line-height: 1.8; margin-bottom: 20px;">
                    نقدم لكم أفضل المنتجات بجودة عالية وأسعار منافسة. نحن نؤمن بالتميز والابتكار في كل منتج نقدمه.
                </p>
                <div class="social-links">
                    <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
            
            <div class="footer-section">
                <h3>روابط سريعة</h3>
                <ul class="footer-links">
                    <li><a href="index.php"><i class="fas fa-arrow-left"></i> الرئيسية</a></li>
                    <li><a href="company_profile.php?id=<?php echo $company_id; ?>"><i class="fas fa-building"></i> الملف التعريفي</a></li>
                    <li><a href="#"><i class="fas fa-boxes"></i> جميع المنتجات</a></li>
                    <li><a href="#"><i class="fas fa-question-circle"></i> المساعدة</a></li>
                    <li><a href="#"><i class="fas fa-phone"></i> اتصل بنا</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>الفئات</h3>
                <ul class="footer-links">
                    <?php foreach ($all_categories as $cat): ?>
                        <li>
                            <a href="category_products.php?company_id=<?php echo $company_id; ?>&main_category_id=<?php echo $cat['id']; ?>">
                                <i class="fas fa-folder"></i>
                                <?php echo htmlspecialchars($cat['name_ar']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>معلومات الاتصال</h3>
                <div class="contact-info">
                    <?php if (!empty($company['address'])): ?>
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo htmlspecialchars($company['address']); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($company['phone'])): ?>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span><?php echo htmlspecialchars($company['phone']); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($company['email'])): ?>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span><?php echo htmlspecialchars($company['email']); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="contact-item">
                        <i class="fas fa-clock"></i>
                        <span>الأحد - الخميس: ٨ صباحاً - ٥ مساءً</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($company['name'] ?? 'الشركة'); ?>. جميع الحقوق محفوظة.</p>
            <p style="margin-top: 10px; font-size: 0.8rem;">
                تصميم وتطوير <span style="color: var(--primary-color); font-weight: 600;">فريق التقنية</span>
            </p>
        </div>
    </footer>

    <script>
        // Store current packaging index for each product
        const packagingStates = {};
        
        // Simple packaging navigation function
        function changePackaging(productId, direction) {
            if (!packagingStates[productId]) {
                packagingStates[productId] = { currentIndex: 0 };
            }
            
            const container = document.getElementById(`packagingContainer-${productId}`);
            const items = container.querySelectorAll('.packaging-item');
            const totalItems = items.length;
            const counter = document.getElementById(`packagingCounter-${productId}`);
            
            // Hide current item
            items[packagingStates[productId].currentIndex].style.display = 'none';
            
            // Calculate new index
            packagingStates[productId].currentIndex += direction;
            
            // Handle boundaries
            if (packagingStates[productId].currentIndex < 0) {
                packagingStates[productId].currentIndex = totalItems - 1;
            } else if (packagingStates[productId].currentIndex >= totalItems) {
                packagingStates[productId].currentIndex = 0;
            }
            
            // Show new item
            items[packagingStates[productId].currentIndex].style.display = 'block';
            
            // Update counter
            if (counter) {
                counter.textContent = `${packagingStates[productId].currentIndex + 1}/${totalItems}`;
            }
        }
        
        // Initialize packaging states
        function initPackagingStates() {
            document.querySelectorAll('[id^="packagingContainer-"]').forEach(container => {
                const idParts = container.id.split('-');
                if (idParts.length >= 2) {
                    const productId = idParts[1];
                    packagingStates[productId] = { currentIndex: 0 };
                }
            });
        }
        
        // Filter products by category
        function filterProducts(categoryId) {
            if (categoryId) {
                window.location.href = `category_products.php?company_id=<?php echo $company_id; ?>&main_category_id=${categoryId}`;
            }
        }
        
        // Sort products
        function sortProducts(sortBy) {
            const productsGrid = document.getElementById('productsGrid');
            const products = Array.from(productsGrid.getElementsByClassName('product-card'));
            
            products.sort((a, b) => {
                const nameA = a.querySelector('.product-name').textContent.toLowerCase();
                const nameB = b.querySelector('.product-name').textContent.toLowerCase();
                
                switch(sortBy) {
                    case 'name_asc':
                        return nameA.localeCompare(nameB);
                    case 'name_desc':
                        return nameB.localeCompare(nameA);
                    default:
                        return 0;
                }
            });
            
            // Reorder products in grid
            products.forEach(product => {
                productsGrid.appendChild(product);
            });
        }
        
        // Show brand full view
        function showBrandFullView(brandLogo, brandName, brandId) {
            const modal = document.getElementById('brandFullModal');
            const imageContainer = document.getElementById('brandFullImageContainer');
            const brandNameElement = document.getElementById('brandFullName');
            
            brandNameElement.textContent = brandName;
            
            imageContainer.innerHTML = '';
            
            if (brandLogo && brandLogo.trim() !== '') {
                let imageSrc = brandLogo;
                if (!imageSrc.startsWith('http') && !imageSrc.startsWith('/') && !imageSrc.startsWith('uploads')) {
                    imageSrc = 'uploads/' + imageSrc;
                }
                
                const img = document.createElement('img');
                img.src = imageSrc;
                img.alt = brandName;
                img.className = 'brand-full-image';
                img.onerror = function() {
                    this.style.display = 'none';
                    showBrandFallback(brandName);
                };
                imageContainer.appendChild(img);
            } else {
                showBrandFallback(brandName);
            }
            
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        
        function showBrandFallback(brandName) {
            const imageContainer = document.getElementById('brandFullImageContainer');
            imageContainer.innerHTML = `
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #666; padding: 20px;">
                    <i class="fas fa-crown" style="font-size: 5rem; margin-bottom: 20px; color: var(--primary-color); opacity: 0.7;"></i>
                    <h3 style="color: var(--dark-color); margin-bottom: 10px;">${brandName}</h3>
                    <p style="color: var(--gray-color);">لا توجد صورة للعلامة التجارية</p>
                </div>
            `;
        }
        
        function closeBrandView() {
            const modal = document.getElementById('brandFullModal');
            modal.classList.remove('show');
            document.body.style.overflow = 'auto';
        }
        
        // Quick view function for products
        function openQuickView(productId, productImage, productName) {
            const modal = document.getElementById('quickViewModal');
            const imageContainer = document.getElementById('quickViewImageContainer');
            const productNameElement = document.getElementById('quickViewProductName');
            
            productNameElement.textContent = productName;
            
            imageContainer.innerHTML = '';
            
            if (productImage && productImage.trim() !== '') {
                let imageSrc = productImage;
                if (!imageSrc.startsWith('http') && !imageSrc.startsWith('/') && !imageSrc.startsWith('uploads')) {
                    imageSrc = 'uploads/' + imageSrc;
                }
                
                const img = document.createElement('img');
                img.src = imageSrc;
                img.alt = productName;
                img.className = 'quick-view-image';
                img.onerror = function() {
                    this.style.display = 'none';
                    showImageFallback(productName);
                };
                imageContainer.appendChild(img);
            } else {
                showImageFallback(productName);
            }
            
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        
        function closeQuickView() {
            const modal = document.getElementById('quickViewModal');
            modal.classList.remove('show');
            document.body.style.overflow = 'auto';
        }
        
        // Close modals when clicking on background
        document.getElementById('brandFullModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeBrandView();
            }
        });
        
        document.getElementById('quickViewModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeQuickView();
            }
        });
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            initPackagingStates();
        });
    </script>
</body>
</html>