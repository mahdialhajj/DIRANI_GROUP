<?php
// admin_products.php
session_start();
require_once 'config.php';
require_once 'session_check.php';

// التحقق من صلاحيات المستخدم
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$company_id = $_GET['company_id'] ?? null;

if (!$company_id) {
    header("Location: index.php");
    exit;
}

// جلب بيانات الشركة
try {
    $stmt = $pdo->prepare("SELECT * FROM companies WHERE id = ?");
    $stmt->execute([$company_id]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$company) {
        header("Location: index.php");
        exit;
    }
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// الرسائل
$message = '';
$message_type = ''; // success, error

// ===== معالجة إضافة/تعديل منتج جديد =====
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if (isset($_POST['add_product'])) {
        $product_name = trim($_POST['product_name']);
        $product_description = trim($_POST['product_description']);
        $product_category = trim($_POST['product_category']);
        $product_price = trim($_POST['product_price']);
        $weight = trim($_POST['weight']);
        $unit = $_POST['unit'];
        $packaging = trim($_POST['packaging']);
        $packaging_description = trim($_POST['packaging_description']);
        
        if (empty($product_name)) {
            $message = "Product name is required";
            $message_type = 'error';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO company_products 
                    (company_id, product_name, product_description, product_category, 
                     product_price, weight, unit, packaging, packaging_description) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $stmt->execute([
                    $company_id,
                    $product_name,
                    $product_description,
                    $product_category,
                    $product_price,
                    $weight,
                    $unit,
                    $packaging,
                    $packaging_description
                ]);
                
                $product_id = $pdo->lastInsertId();
                $message = "Product added successfully with weight and packaging!";
                $message_type = 'success';
                
            } catch(PDOException $e) {
                $message = "Error: " . $e->getMessage();
                $message_type = 'error';
            }
        }
    }
    
    // معالجة تعديل منتج
    if (isset($_POST['edit_product'])) {
        $product_id = $_POST['product_id'];
        $product_name = trim($_POST['product_name']);
        $product_description = trim($_POST['product_description']);
        $product_category = trim($_POST['product_category']);
        $product_price = trim($_POST['product_price']);
        $weight = trim($_POST['weight']);
        $unit = $_POST['unit'];
        $packaging = trim($_POST['packaging']);
        $packaging_description = trim($_POST['packaging_description']);
        
        try {
            $stmt = $pdo->prepare("UPDATE company_products SET
                product_name = ?,
                product_description = ?,
                product_category = ?,
                product_price = ?,
                weight = ?,
                unit = ?,
                packaging = ?,
                packaging_description = ?,
                updated_at = CURRENT_TIMESTAMP
                WHERE id = ? AND company_id = ?");
            
            $stmt->execute([
                $product_name,
                $product_description,
                $product_category,
                $product_price,
                $weight,
                $unit,
                $packaging,
                $packaging_description,
                $product_id,
                $company_id
            ]);
            
            $message = "Product updated successfully!";
            $message_type = 'success';
            
        } catch(PDOException $e) {
            $message = "Error: " . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// ===== معالجة حذف منتج =====
if (isset($_GET['delete_product'])) {
    $product_id = $_GET['delete_product'];
    
    try {
        $stmt = $pdo->prepare("UPDATE company_products SET is_active = FALSE WHERE id = ? AND company_id = ?");
        $stmt->execute([$product_id, $company_id]);
        
        $message = "Product deactivated successfully!";
        $message_type = 'success';
        
    } catch(PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = 'error';
    }
}

// ===== معالجة تنشيط منتج =====
if (isset($_GET['activate_product'])) {
    $product_id = $_GET['activate_product'];
    
    try {
        $stmt = $pdo->prepare("UPDATE company_products SET is_active = TRUE WHERE id = ? AND company_id = ?");
        $stmt->execute([$product_id, $company_id]);
        
        $message = "Product activated successfully!";
        $message_type = 'success';
        
    } catch(PDOException $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = 'error';
    }
}

// جلب المنتجات النشطة فقط
$show_inactive = isset($_GET['show_inactive']) ? true : false;
try {
    if ($show_inactive) {
        $stmt = $pdo->prepare("SELECT * FROM company_products WHERE company_id = ? ORDER BY product_name ASC");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM company_products WHERE company_id = ? AND is_active = TRUE ORDER BY product_name ASC");
    }
    $stmt->execute([$company_id]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $products = [];
}

// جلب منتج للتعديل
$edit_product = null;
if (isset($_GET['edit_product'])) {
    $product_id = $_GET['edit_product'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM company_products WHERE id = ? AND company_id = ?");
        $stmt->execute([$product_id, $company_id]);
        $edit_product = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        // تجاهل الخطأ
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - <?php echo htmlspecialchars($company['name']); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            color: #333;
        }
        
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #3498db;
            text-decoration: none;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .page-header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .header-content h1 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .header-content p {
            color: #7f8c8d;
            font-size: 1.1rem;
        }
        
        .admin-sections {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        @media (max-width: 992px) {
            .admin-sections {
                grid-template-columns: 1fr;
            }
        }
        
        .admin-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .section-title {
            color: #2c3e50;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #3498db;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-group label i {
            color: #3498db;
            width: 20px;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            outline: none;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: #2ecc71;
            color: white;
        }
        
        .btn-success:hover {
            background: #27ae60;
            transform: translateY(-2px);
        }
        
        .btn-warning {
            background: #f39c12;
            color: white;
        }
        
        .btn-warning:hover {
            background: #d68910;
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #7f8c8d;
            transform: translateY(-2px);
        }
        
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        .products-table th,
        .products-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
            vertical-align: top;
        }
        
        .products-table th {
            background: #f8f9fa;
            color: #2c3e50;
            font-weight: 600;
        }
        
        .products-table tr:hover {
            background: #f8f9fa;
        }
        
        .product-name {
            font-weight: bold;
            color: #2c3e50;
        }
        
        .product-category {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            margin-top: 5px;
        }
        
        .product-price {
            color: #2ecc71;
            font-weight: bold;
        }
        
        .product-weight {
            color: #9b59b6;
            font-weight: 600;
        }
        
        .product-packaging {
            color: #e74c3c;
            font-weight: 600;
        }
        
        .status-active {
            color: #2ecc71;
            font-weight: 600;
        }
        
        .status-inactive {
            color: #e74c3c;
            font-weight: 600;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert i {
            font-size: 1.2rem;
        }
        
        .empty-message {
            text-align: center;
            padding: 3rem;
            color: #7f8c8d;
        }
        
        .empty-message i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #bdc3c7;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        
        .toggle-inactive {
            margin-top: 1rem;
            text-align: center;
        }
        
        .toggle-inactive a {
            color: #3498db;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .toggle-inactive a:hover {
            text-decoration: underline;
        }
        
        .weight-packaging-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-top: 1rem;
            border: 2px dashed #e9ecef;
        }
        
        .weight-packaging-section h4 {
            color: #2c3e50;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- رابط العودة -->
        <a href="company_profile.php?id=<?php echo $company_id; ?>" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Back to Company Profile
        </a>
        
        <!-- عنوان الصفحة -->
        <div class="page-header">
            <div class="header-content">
                <h1>
                    <i class="fas fa-boxes"></i>
                    Manage Products - <?php echo htmlspecialchars($company['name']); ?>
                </h1>
                <p>Add and manage products with weight and packaging options</p>
            </div>
            
            <div>
                <?php if ($show_inactive): ?>
                    <a href="?company_id=<?php echo $company_id; ?>" class="btn btn-primary">
                        <i class="fas fa-eye"></i> Show Active Products
                    </a>
                <?php else: ?>
                    <a href="?company_id=<?php echo $company_id; ?>&show_inactive=true" class="btn btn-secondary">
                        <i class="fas fa-eye-slash"></i> Show Inactive Products
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- رسائل التنبيه -->
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="admin-sections">
            <!-- قسم إضافة/تعديل منتج -->
            <div class="admin-section">
                <h2 class="section-title">
                    <i class="fas fa-<?php echo $edit_product ? 'edit' : 'plus-circle'; ?>"></i>
                    <?php echo $edit_product ? 'Edit Product' : 'Add New Product'; ?>
                </h2>
                
                <form method="POST">
                    <?php if ($edit_product): ?>
                        <input type="hidden" name="product_id" value="<?php echo $edit_product['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="product_name"><i class="fas fa-tag"></i> Product Name *</label>
                        <input type="text" id="product_name" name="product_name" required 
                               value="<?php echo htmlspecialchars($edit_product['product_name'] ?? ''); ?>"
                               placeholder="Enter product name">
                    </div>
                    
                    <div class="form-group">
                        <label for="product_description"><i class="fas fa-align-left"></i> Product Description</label>
                        <textarea id="product_description" name="product_description" rows="3" 
                                  placeholder="Enter product description (optional)"><?php echo htmlspecialchars($edit_product['product_description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="product_category"><i class="fas fa-folder"></i> Category</label>
                            <input type="text" id="product_category" name="product_category" 
                                   value="<?php echo htmlspecialchars($edit_product['product_category'] ?? ''); ?>"
                                   placeholder="Example: Electronics, Food">
                        </div>
                        
                        <div class="form-group">
                            <label for="product_price"><i class="fas fa-dollar-sign"></i> Price</label>
                            <input type="text" id="product_price" name="product_price" 
                                   value="<?php echo htmlspecialchars($edit_product['product_price'] ?? ''); ?>"
                                   placeholder="Example: 100 SAR, $50, Contact for price">
                        </div>
                    </div>
                    
                    <!-- قسم الوزن والتعبئة -->
                    <div class="weight-packaging-section">
                        <h4><i class="fas fa-weight"></i> Weight & Packaging Details</h4>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="weight"><i class="fas fa-balance-scale"></i> Weight</label>
                                <input type="text" id="weight" name="weight" 
                                       value="<?php echo htmlspecialchars($edit_product['weight'] ?? ''); ?>"
                                       placeholder="Example: 1, 2.5, 10">
                            </div>
                            
                            <div class="form-group">
                                <label for="unit"><i class="fas fa-ruler"></i> Unit</label>
                                <select id="unit" name="unit">
                                    <option value="">-- Select Unit --</option>
                                    <option value="kg" <?php echo ($edit_product['unit'] ?? '') == 'kg' ? 'selected' : ''; ?>>Kilogram (kg)</option>
                                    <option value="g" <?php echo ($edit_product['unit'] ?? '') == 'g' ? 'selected' : ''; ?>>Gram (g)</option>
                                    <option value="lb" <?php echo ($edit_product['unit'] ?? '') == 'lb' ? 'selected' : ''; ?>>Pound (lb)</option>
                                    <option value="oz" <?php echo ($edit_product['unit'] ?? '') == 'oz' ? 'selected' : ''; ?>>Ounce (oz)</option>
                                    <option value="piece" <?php echo ($edit_product['unit'] ?? '') == 'piece' ? 'selected' : ''; ?>>Piece</option>
                                    <option value="pack" <?php echo ($edit_product['unit'] ?? '') == 'pack' ? 'selected' : ''; ?>>Pack</option>
                                    <option value="liter" <?php echo ($edit_product['unit'] ?? '') == 'liter' ? 'selected' : ''; ?>>Liter</option>
                                    <option value="ml" <?php echo ($edit_product['unit'] ?? '') == 'ml' ? 'selected' : ''; ?>>Milliliter (ml)</option>
                                    <option value="box" <?php echo ($edit_product['unit'] ?? '') == 'box' ? 'selected' : ''; ?>>Box</option>
                                    <option value="carton" <?php echo ($edit_product['unit'] ?? '') == 'carton' ? 'selected' : ''; ?>>Carton</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="packaging"><i class="fas fa-box"></i> Packaging Type</label>
                            <input type="text" id="packaging" name="packaging" 
                                   value="<?php echo htmlspecialchars($edit_product['packaging'] ?? ''); ?>"
                                   placeholder="Example: Cardboard Box, Plastic Bag, Wooden Crate">
                        </div>
                        
                        <div class="form-group">
                            <label for="packaging_description"><i class="fas fa-align-left"></i> Packaging Description</label>
                            <textarea id="packaging_description" name="packaging_description" rows="2" 
                                      placeholder="Example: Waterproof packaging, Eco-friendly, Food-grade material"><?php echo htmlspecialchars($edit_product['packaging_description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-top: 2rem;">
                        <?php if ($edit_product): ?>
                            <button type="submit" name="edit_product" class="btn btn-success">
                                <i class="fas fa-save"></i> Update Product
                            </button>
                            <a href="?company_id=<?php echo $company_id; ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        <?php else: ?>
                            <button type="submit" name="add_product" class="btn btn-primary">
                                <i class="fas fa-plus-circle"></i> Add Product
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <!-- قسم عرض المنتجات -->
            <div class="admin-section">
                <h2 class="section-title">
                    <i class="fas fa-list"></i>
                    Products List
                    <span style="font-size: 0.9rem; color: #7f8c8d; margin-left: 10px;">
                        (<?php echo count($products); ?> products)
                    </span>
                </h2>
                
                <?php if (empty($products)): ?>
                    <div class="empty-message">
                        <i class="fas fa-boxes"></i>
                        <h4>No Products Found</h4>
                        <p><?php echo $show_inactive ? 'No inactive products found.' : 'Start by adding your first product.'; ?></p>
                    </div>
                <?php else: ?>
                    <table class="products-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Weight & Packaging</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <div class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></div>
                                        <?php if ($product['product_description']): ?>
                                            <div style="color: #7f8c8d; font-size: 0.9rem; margin-top: 5px;">
                                                <?php echo substr(htmlspecialchars($product['product_description']), 0, 50); ?>...
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($product['product_category']): ?>
                                            <div class="product-category"><?php echo htmlspecialchars($product['product_category']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td>
                                        <?php if ($product['weight']): ?>
                                            <div class="product-weight">
                                                <i class="fas fa-weight"></i>
                                                <?php echo htmlspecialchars($product['weight']); ?>
                                                <?php if ($product['unit']): ?>
                                                    <span style="color: #95a5a6;"><?php echo htmlspecialchars($product['unit']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($product['packaging']): ?>
                                            <div class="product-packaging" style="margin-top: 8px;">
                                                <i class="fas fa-box"></i>
                                                <?php echo htmlspecialchars($product['packaging']); ?>
                                                <?php if ($product['packaging_description']): ?>
                                                    <div style="color: #7f8c8d; font-size: 0.85rem; margin-top: 3px;">
                                                        <?php echo substr(htmlspecialchars($product['packaging_description']), 0, 30); ?>...
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!$product['weight'] && !$product['packaging']): ?>
                                            <span style="color: #95a5a6; font-style: italic;">No details added</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td>
                                        <?php if ($product['product_price']): ?>
                                            <div class="product-price"><?php echo htmlspecialchars($product['product_price']); ?></div>
                                        <?php else: ?>
                                            <span style="color: #95a5a6;">Not specified</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td>
                                        <?php if ($product['is_active']): ?>
                                            <span class="status-active">Active</span>
                                        <?php else: ?>
                                            <span class="status-inactive">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td>
                                        <div class="action-buttons">
                                            <a href="?company_id=<?php echo $company_id; ?>&edit_product=<?php echo $product['id']; ?>" 
                                               class="btn btn-warning" style="padding: 8px 12px;">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            
                                            <?php if ($product['is_active']): ?>
                                                <a href="?company_id=<?php echo $company_id; ?>&delete_product=<?php echo $product['id']; ?>" 
                                                   class="btn btn-danger" style="padding: 8px 12px;"
                                                   onclick="return confirm('Are you sure you want to deactivate this product?')">
                                                    <i class="fas fa-times"></i> Deactivate
                                                </a>
                                            <?php else: ?>
                                                <a href="?company_id=<?php echo $company_id; ?>&activate_product=<?php echo $product['id']; ?>" 
                                                   class="btn btn-success" style="padding: 8px 12px;">
                                                    <i class="fas fa-check"></i> Activate
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <?php if (!$show_inactive): ?>
                        <div class="toggle-inactive">
                            <a href="?company_id=<?php echo $company_id; ?>&show_inactive=true">
                                <i class="fas fa-eye-slash"></i> Show deactivated products
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
    // وظائف JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        // إظهار/إخفاء تفاصيل الوزن والتعبئة بناءً على المدخلات
        const weightInput = document.getElementById('weight');
        const packagingInput = document.getElementById('packaging');
        const weightPackagingSection = document.querySelector('.weight-packaging-section');
        
        function updateSectionVisibility() {
            if (weightInput.value || packagingInput.value) {
                weightPackagingSection.style.display = 'block';
            } else {
                weightPackagingSection.style.display = 'none';
            }
        }
        
        weightInput.addEventListener('input', updateSectionVisibility);
        packagingInput.addEventListener('input', updateSectionVisibility);
        
        // تحديث الوحدة بناءً على الوزن المدخل
        weightInput.addEventListener('blur', function() {
            const value = this.value.toLowerCase();
            const unitSelect = document.getElementById('unit');
            
            // اقتراح الوحدة المناسبة بناءً على القيمة
            if (value.includes('kg') || parseFloat(value) >= 1) {
                unitSelect.value = 'kg';
            } else if (value.includes('g') || parseFloat(value) < 1) {
                unitSelect.value = 'g';
            } else if (value.includes('lb')) {
                unitSelect.value = 'lb';
            } else if (value.includes('oz')) {
                unitSelect.value = 'oz';
            } else if (value.includes('piece') || value.includes('pcs')) {
                unitSelect.value = 'piece';
            } else if (value.includes('pack')) {
                unitSelect.value = 'pack';
            } else if (value.includes('liter') || value.includes('l')) {
                unitSelect.value = 'liter';
            } else if (value.includes('ml')) {
                unitSelect.value = 'ml';
            } else if (value.includes('box')) {
                unitSelect.value = 'box';
            } else if (value.includes('carton')) {
                unitSelect.value = 'carton';
            }
        });
        
        // التأكيد قبل الحذف
        const deleteLinks = document.querySelectorAll('a[href*="delete_product"]');
        deleteLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to deactivate this product?\n\nNote: Product will be hidden from customers but can be restored later.')) {
                    e.preventDefault();
                }
            });
        });
        
        // إظهار رسالة نجاح مؤقتة
        <?php if ($message && $message_type == 'success'): ?>
        setTimeout(function() {
            const alert = document.querySelector('.alert-success');
            if (alert) {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }
        }, 5000);
        <?php endif; ?>
    });
    </script>
</body>
</html>