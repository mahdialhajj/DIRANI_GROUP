<?php
// brand_products.php
include 'config.php';
session_start();

// Get brand ID
$brand_id = $_GET['brand_id'] ?? null;
$company_id = $_GET['company_id'] ?? null;

if (!$brand_id || !$company_id) {
    header("Location: index.php");
    exit;
}

// Fetch brand details
$stmt = $pdo->prepare("SELECT * FROM company_brands WHERE id = ? AND company_id = ?");
$stmt->execute([$brand_id, $company_id]);
$brand = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$brand) {
    header("Location: index.php");
    exit;
}

// Fetch products for this brand
$stmt = $pdo->prepare("
    SELECT p.*, sc.name_ar as subcategory_name, mc.name_ar as main_category_name
    FROM products p
    INNER JOIN product_brands pb ON p.id = pb.product_id
    LEFT JOIN sub_categories sc ON p.sub_category_id = sc.id
    LEFT JOIN main_categories mc ON sc.main_category_id = mc.id
    WHERE pb.brand_id = ? AND p.company_id = ?
    ORDER BY mc.name_ar, sc.name_ar, p.product_name_ar
");
$stmt->execute([$brand_id, $company_id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group products by category
$products_by_category = [];
foreach ($products as $product) {
    $cat_name = $product['main_category_name'] ?: 'Uncategorized';
    $products_by_category[$cat_name][] = $product;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($brand['brand_name']); ?> - Products</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .brand-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 20px;
            text-align: center;
        }
        
        .brand-logo-large {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
            border: 5px solid white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .brand-title {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .brand-description {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto 30px;
        }
        
        .products-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .category-section {
            margin-bottom: 40px;
        }
        
        .category-title {
            font-size: 1.8rem;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        
        .product-item {
            background: white;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .product-item:hover {
            transform: translateY(-5px);
        }
        
        .product-name {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .product-category {
            font-size: 0.9rem;
            color: #7f8c8d;
        }
        
        .back-link {
            display: inline-block;
            color: #667eea;
            text-decoration: none;
            margin-top: 20px;
            font-weight: 500;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            
            .brand-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <a href="company_profile.php?id=<?php echo $company_id; ?>">
                        <i class="fas fa-arrow-left"></i> Back to Company
                    </a>
                </div>
                <div class="nav-menu">
                    <a href="index.php">Home</a>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <div class="brand-header">
            <?php if (!empty($brand['brand_logo'])): ?>
                <img src="<?php echo htmlspecialchars($brand['brand_logo']); ?>" 
                     alt="<?php echo htmlspecialchars($brand['brand_name']); ?>"
                     class="brand-logo-large"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block'">
                <div style="display:none; font-size: 3rem; margin-bottom: 20px;">
                    <i class="fas fa-crown"></i>
                </div>
            <?php else: ?>
                <div style="font-size: 3rem; margin-bottom: 20px;">
                    <i class="fas fa-crown"></i>
                </div>
            <?php endif; ?>
            
            <h1 class="brand-title"><?php echo htmlspecialchars($brand['brand_name']); ?></h1>
            
            <?php if (!empty($brand['brand_description'])): ?>
                <p class="brand-description"><?php echo htmlspecialchars($brand['brand_description']); ?></p>
            <?php endif; ?>
            
            <a href="company_profile.php?id=<?php echo $company_id; ?>" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Company Profile
            </a>
        </div>
        
        <div class="products-container">
            <?php if (!empty($products_by_category)): ?>
                <?php foreach ($products_by_category as $category_name => $category_products): ?>
                    <div class="category-section">
                        <h2 class="category-title"><?php echo htmlspecialchars($category_name); ?></h2>
                        <div class="products-grid">
                            <?php foreach ($category_products as $product): ?>
                                <div class="product-item">
                                    <div class="product-name"><?php echo htmlspecialchars($product['product_name_ar']); ?></div>
                                    <?php if (!empty($product['subcategory_name'])): ?>
                                        <div class="product-category">
                                            <i class="fas fa-tag"></i> <?php echo htmlspecialchars($product['subcategory_name']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 50px;">
                    <i class="fas fa-box-open" style="font-size: 4rem; color: #bdc3c7; margin-bottom: 20px;"></i>
                    <h3 style="color: #2c3e50; margin-bottom: 10px;">No Products Found</h3>
                    <p style="color: #7f8c8d;">There are no products associated with this brand yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="footer-container">
            <p>&copy; <?php echo date('Y'); ?> All rights reserved.</p>
        </div>
    </footer>
</body>
</html>