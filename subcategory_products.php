<?php
// subcategory_products.php
include 'config.php';

// Start session only if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get subcategory ID from URL
$subcategory_id = isset($_GET['subcategory_id']) ? intval($_GET['subcategory_id']) : 0;

if ($subcategory_id <= 0) {
    header("Location: all_categories.php?error=invalid_subcategory");
    exit();
}

try {
    // Get subcategory details
    $stmt = $pdo->prepare("
        SELECT sc.*, mc.name_ar as main_category_name, mc.id as main_category_id 
        FROM sub_categories sc
        LEFT JOIN main_categories mc ON sc.main_category_id = mc.id
        WHERE sc.id = ?
    ");
    $stmt->execute([$subcategory_id]);
    $subcategory = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$subcategory) {
        header("Location: all_categories.php?error=subcategory_not_found");
        exit();
    }
    
    // Get products for this subcategory with ALL packaging details
    $stmt = $pdo->prepare("
        SELECT 
            p.*,
            GROUP_CONCAT(DISTINCT 
                CASE 
                    WHEN pack.packaging_type IS NOT NULL AND pack.packaging_type != '' 
                    THEN CONCAT(pack.packaging_type, '|', COALESCE(pack.weight, ''), '|', COALESCE(pack.weight_unit, ''), '|', COALESCE(pack.quantity_per_pack, ''), '|', COALESCE(pack.master_pack, ''))
                    ELSE NULL
                END
                SEPARATOR ';'
            ) as packaging_details
        FROM products p
        LEFT JOIN packaging pack ON p.id = pack.product_id
        WHERE p.sub_category_id = ?
        GROUP BY p.id
        ORDER BY p.product_name_ar ASC
    ");
    $stmt->execute([$subcategory_id]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get product count
    $product_count = count($products);
    
} catch(PDOException $e) {
    die("خطأ في قاعدة البيانات: " . $e->getMessage());
}

// Function to format packaging display
function formatPackagingDisplay($product) {
    if (empty($product['packaging_details'])) {
        return null;
    }
    
    $packaging_items = explode(';', $product['packaging_details']);
    $formatted_packaging = [];
    
    foreach ($packaging_items as $item) {
        if (empty($item) || $item === 'NULL') {
            continue;
        }
        
        $parts = explode('|', $item);
        if (count($parts) >= 5) {
            $packaging_type = trim($parts[0]);
            $weight = trim($parts[1]);
            $weight_unit = trim($parts[2]);
            $quantity_per_pack = trim($parts[3]);
            $master_pack = trim($parts[4]);
            
            if (!empty($packaging_type)) {
                $formatted_item = [
                    'packaging_type' => $packaging_type,
                    'weight' => $weight,
                    'weight_unit' => $weight_unit,
                    'quantity_per_pack' => $quantity_per_pack,
                    'master_pack' => $master_pack
                ];
                $formatted_packaging[] = $formatted_item;
            }
        }
    }
    
    return !empty($formatted_packaging) ? $formatted_packaging : null;
}

// Function to check if product has packaging data
function hasPackagingData($product) {
    if (!empty($product['packaging_details']) && $product['packaging_details'] !== 'NULL') {
        $packaging_items = explode(';', $product['packaging_details']);
        foreach ($packaging_items as $item) {
            if (!empty($item) && $item !== 'NULL') {
                return true;
            }
        }
    }
    return false;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>منتجات <?php echo htmlspecialchars($subcategory['name_ar']); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Breadcrumb */
        .breadcrumb {
            background: #f8f9fa;
            padding: 1rem 1.5rem;
            border-radius: 0.75rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .breadcrumb a {
            color: #475569;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: color 0.3s ease;
        }
        
        .breadcrumb a:hover {
            color: #3b82f6;
        }
        
        .breadcrumb span {
            color: #1e293b;
            font-weight: 500;
        }
        
        .breadcrumb-separator {
            color: #94a3b8;
        }
        
        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, #10b981, #047857);
            color: white;
            padding: 2rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(30%, -30%);
        }
        
        .page-header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }
        
        .page-header .hero-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.1rem;
            position: relative;
            z-index: 1;
        }
        
        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        
        .product-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
            border-color: #3b82f6;
        }
        
        .product-image {
            height: 180px;
            overflow: hidden;
            border-radius: 0.75rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .product-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            transition: transform 0.3s ease;
        }
        
        .product-card:hover .product-image img {
            transform: scale(1.05);
        }
        
        .product-name {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 0.75rem;
            color: #1e293b;
            min-height: 60px;
        }
        
        .product-packaging {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
        }
        
        .packaging-title {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .packaging-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .packaging-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem;
            background: #f8fafc;
            border-radius: 0.5rem;
            border: 1px solid #e2e8f0;
        }
        
        .packaging-details {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        
        .packaging-type {
            font-weight: 500;
            color: #475569;
            margin-bottom: 3px;
        }
        
        .packaging-specs {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .packaging-spec {
            font-size: 0.8rem;
            color: #64748b;
            background: #e9ecef;
            padding: 2px 6px;
            border-radius: 4px;
        }
        
        .packaging-empty {
            text-align: center;
            padding: 1rem;
            color: #94a3b8;
            font-style: italic;
            background: #f8fafc;
            border-radius: 0.5rem;
            border: 1px dashed #e2e8f0;
        }
        
        .product-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
        }
        
        .order-btn {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            cursor: pointer;
        }
        
        .order-btn:hover {
            background: linear-gradient(135deg, #2563eb, #1e40af);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            border-radius: 1rem;
            border: 2px dashed #cbd5e1;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #94a3b8;
            margin-bottom: 1.5rem;
        }
        
        .empty-state h3 {
            color: #475569;
            margin-bottom: 1rem;
        }
        
        .empty-state p {
            color: #64748b;
            max-width: 400px;
            margin: 0 auto 2rem;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #64748b, #475569);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background: linear-gradient(135deg, #475569, #334155);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(71, 85, 105, 0.3);
        }
        
        /* Category Info Card */
        .category-info-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            border-left: 4px solid #10b981;
        }
        
        .category-info-card h3 {
            color: #1e293b;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            color: #64748b;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }
        
        .info-value {
            color: #1e293b;
            font-weight: 500;
        }
        
        /* Debug info for testing */
        .debug-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
            border-left: 4px solid #3498db;
            font-family: monospace;
            font-size: 0.9rem;
            color: #555;
            display: none; /* Hide by default, change to block for debugging */
        }
        
        @media (max-width: 768px) {
            .products-grid {
                grid-template-columns: 1fr;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .packaging-specs {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
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
                    <a href="index.php">الرئيسية</a>
                    <a href="all_categories.php">جميع الأصناف</a>
                    <a href="category_products.php?main_category_id=<?php echo $subcategory['main_category_id']; ?>" 
                       class="btn-secondary">
                        <i class="fas fa-arrow-right"></i> العودة للفئة
                    </a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="logout.php" class="btn-primary">تسجيل خروج</a>
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
                </a>
                <span class="breadcrumb-separator">/</span>
                <a href="all_categories.php">
                    <i class="fas fa-layer-group"></i>
                    جميع الأصناف
                </a>
                <span class="breadcrumb-separator">/</span>
                <a href="category_products.php?main_category_id=<?php echo $subcategory['main_category_id']; ?>">
                    <i class="fas fa-folder"></i>
                    <?php echo htmlspecialchars($subcategory['main_category_name']); ?>
                </a>
                <span class="breadcrumb-separator">/</span>
                <span>
                    <i class="fas fa-tag"></i>
                    <?php echo htmlspecialchars($subcategory['name_ar']); ?>
                </span>
            </div>

            <!-- Category Info Card -->
            <div class="category-info-card">
                <h3>
                    <i class="fas fa-info-circle"></i>
                    معلومات النوع الفرعي
                </h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">اسم النوع</span>
                        <span class="info-value"><?php echo htmlspecialchars($subcategory['name_ar']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">الفئة الرئيسية</span>
                        <span class="info-value"><?php echo htmlspecialchars($subcategory['main_category_name']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">عدد المنتجات</span>
                        <span class="info-value"><?php echo $product_count; ?> منتج</span>
                    </div>
                    <?php if (!empty($subcategory['description'])): ?>
                    <div class="info-item">
                        <span class="info-label">الوصف</span>
                        <span class="info-value"><?php echo htmlspecialchars($subcategory['description']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Page Header -->
            <div class="page-header">
                <h1>
                    <i class="fas fa-boxes"></i>
                    منتجات <?php echo htmlspecialchars($subcategory['name_ar']); ?>
                </h1>
                <p class="hero-subtitle">
                    <?php echo $product_count; ?> منتج متوفر
                </p>
            </div>

            <!-- Debug info (remove after testing) -->
            <div class="debug-info">
                <strong>Debug Info:</strong><br>
                Subcategory ID: <?php echo $subcategory_id; ?><br>
                Products Found: <?php echo $product_count; ?><br>
                <?php if ($product_count > 0): ?>
                    First Product Data:<br>
                    <?php 
                    echo "Product Name: " . htmlspecialchars($products[0]['product_name_ar']) . "<br>";
                    echo "Packaging Details: " . htmlspecialchars($products[0]['packaging_details'] ?? 'NULL') . "<br>";
                    echo "Has Packaging: " . (hasPackagingData($products[0]) ? 'YES' : 'NO');
                    ?>
                <?php endif; ?>
            </div>

            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <h3>لا توجد منتجات في هذا النوع</h3>
                    <p>لم يتم إضافة أي منتجات بعد. سيتم إضافتها قريباً.</p>
                    <a href="category_products.php?main_category_id=<?php echo $subcategory['main_category_id']; ?>" 
                       class="back-btn">
                        <i class="fas fa-arrow-right"></i>
                        العودة إلى الفئة
                    </a>
                </div>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): 
                        $packaging_data = formatPackagingDisplay($product);
                        $has_packaging = hasPackagingData($product);
                    ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if (!empty($product['product_image'])): ?>
                                <img src="<?php echo htmlspecialchars($product['product_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['product_name_ar']); ?>"
                                     onerror="this.onerror=null; this.src='https://via.placeholder.com/400x300?text=No+Image'">
                            <?php else: ?>
                                <i class="fas fa-box-open" style="font-size: 4rem; color: #94a3b8;"></i>
                            <?php endif; ?>
                        </div>
                        
                        <h3 class="product-name">
                            <?php echo htmlspecialchars($product['product_name_ar']); ?>
                        </h3>
                        
                        <div class="product-packaging">
                            <div class="packaging-title">
                                <i class="fas fa-weight-hanging"></i>
                                <span>أنواع التغليف المتوفرة</span>
                            </div>
                            
                            <?php if ($has_packaging && $packaging_data): ?>
                                <div class="packaging-list">
                                    <?php foreach ($packaging_data as $packaging): ?>
                                        <div class="packaging-item">
                                            <div class="packaging-details">
                                                <div class="packaging-type">
                                                    <?php echo htmlspecialchars($packaging['packaging_type']); ?>
                                                </div>
                                                <div class="packaging-specs">
                                                    <?php if (!empty($packaging['weight'])): ?>
                                                        <span class="packaging-spec">
                                                            <i class="fas fa-weight"></i>
                                                            <?php echo $packaging['weight']; ?>
                                                            <?php echo !empty($packaging['weight_unit']) ? $packaging['weight_unit'] : ''; ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($packaging['quantity_per_pack'])): ?>
                                                        <span class="packaging-spec">
                                                            <i class="fas fa-box"></i>
                                                            <?php echo $packaging['quantity_per_pack']; ?> قطعة
                                                        </span>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($packaging['master_pack'])): ?>
                                                        <span class="packaging-spec">
                                                            <i class="fas fa-pallet"></i>
                                                            <?php echo htmlspecialchars($packaging['master_pack']); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="packaging-empty">
                                    <i class="fas fa-info-circle"></i>
                                    <span>لم يتم إضافة تفاصيل التغليف بعد</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-actions">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <?php if ($has_packaging): ?>
                                    <a href="sales_order.php?company_id=<?php echo $product['company_id'] ?? 11; ?>&product_id=<?php echo $product['id']; ?>" 
                                       class="order-btn">
                                        <i class="fas fa-shopping-cart"></i>
                                        طلب المنتج
                                    </a>
                                <?php else: ?>
                                    <button class="order-btn" style="background: #95a5a6;" disabled>
                                        <i class="fas fa-info-circle"></i>
                                        انتظار التغليف
                                    </button>
                                <?php endif; ?>
                            <?php else: ?>
                                <button class="order-btn" onclick="showLoginAlert()">
                                    <i class="fas fa-sign-in-alt"></i>
                                    سجل دخول للطلب
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-section">
                <h3><i class="fas fa-building"></i> ديراني غروب</h3>
                <p style="color: #94a3b8;">جميع المنتجات معروضة مع تفاصيل التغليف والوزن</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 ديراني غروب. جميع الحقوق محفوظة.</p>
        </div>
    </footer>

    <script>
    function orderProduct(productName) {
        alert('طلب المنتج: ' + productName + '\n\nسيتم تحويلك إلى صفحة الطلب');
    }
    
    function showLoginAlert() {
        alert('يجب تسجيل الدخول لطلب المنتجات\n\nسيتم تحويلك إلى صفحة تسجيل الدخول');
        setTimeout(function() {
            window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.href);
        }, 2000);
    }
    
    // Add animations for product cards
    document.addEventListener('DOMContentLoaded', function() {
        const productCards = document.querySelectorAll('.product-card');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        
        productCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = `all 0.6s ease ${index * 0.1}s`;
            observer.observe(card);
        });
    });
    </script>
</body>
</html>