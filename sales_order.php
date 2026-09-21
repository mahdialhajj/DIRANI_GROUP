<?php

include 'config.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$user_id = $_SESSION['user_id'];
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

// Function to check if user is admin
function isUserAdmin($pdo, $user_id, $company_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT u.role_id, ur.role_name 
            FROM users u
            JOIN user_roles ur ON u.role_id = ur.id
            WHERE u.id = ? AND ur.role_name IN ('admin', 'super_admin')
        ");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ? true : false;
    } catch(Exception $e) {
        return false;
    }
}

// Function to get user's role name
function getUserRole($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT ur.role_name 
            FROM users u
            JOIN user_roles ur ON u.role_id = ur.id
            WHERE u.id = ?
        ");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['role_name'] ?? 'user';
    } catch(Exception $e) {
        return 'user';
    }
}

// Function to get brands assigned to user
function getUserBrands($pdo, $user_id, $company_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT cb.id, cb.brand_name, cb.brand_logo 
            FROM company_brands cb
            INNER JOIN user_brands ub ON cb.id = ub.brand_id
            WHERE ub.user_id = ? AND cb.company_id = ?
            ORDER BY cb.brand_name ASC
        ");
        $stmt->execute([$user_id, $company_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(Exception $e) {
        return [];
    }
}

// Function to get products by brand
function getProductsByBrand($pdo, $brand_id, $company_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT DISTINCT p.*, 
                   sc.name_ar as subcategory_name,
                   mc.name_ar as main_category_name,
                   GROUP_CONCAT(DISTINCT cb.brand_name SEPARATOR ', ') as brand_names,
                   GROUP_CONCAT(DISTINCT pb.brand_id) as brand_ids
            FROM products p
            INNER JOIN product_brands pb ON p.id = pb.product_id
            INNER JOIN company_brands cb ON pb.brand_id = cb.id
            JOIN sub_categories sc ON p.sub_category_id = sc.id
            JOIN main_categories mc ON sc.main_category_id = mc.id
            WHERE pb.brand_id = ? AND p.company_id = ?
            GROUP BY p.id
            ORDER BY p.product_name_ar ASC
        ");
        $stmt->execute([$brand_id, $company_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(Exception $e) {
        return [];
    }
}

// Function to get all products (for admin users)
function getAllProducts($pdo, $company_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, 
                   sc.name_ar as subcategory_name,
                   mc.name_ar as main_category_name,
                   GROUP_CONCAT(DISTINCT cb.brand_name SEPARATOR ', ') as brand_names,
                   GROUP_CONCAT(DISTINCT pb.brand_id) as brand_ids
            FROM products p
            INNER JOIN product_brands pb ON p.id = pb.product_id
            INNER JOIN company_brands cb ON pb.brand_id = cb.id
            JOIN sub_categories sc ON p.sub_category_id = sc.id
            JOIN main_categories mc ON sc.main_category_id = mc.id
            WHERE p.company_id = ?
            GROUP BY p.id
            ORDER BY p.product_name_ar ASC
        ");
        $stmt->execute([$company_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

// Function to get all brands for admin
function getAllBrands($pdo, $company_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT id, brand_name, brand_logo 
            FROM company_brands 
            WHERE company_id = ? 
            ORDER BY brand_name ASC
        ");
        $stmt->execute([$company_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(Exception $e) {
        return [];
    }
}

// Function to get product by ID with all its brands
function getProductWithBrands($pdo, $product_id, $company_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*,
                   GROUP_CONCAT(DISTINCT cb.brand_name SEPARATOR ', ') as brand_names,
                   GROUP_CONCAT(DISTINCT cb.id) as brand_ids
            FROM products p
            INNER JOIN product_brands pb ON p.id = pb.product_id
            INNER JOIN company_brands cb ON pb.brand_id = cb.id
            WHERE p.id = ? AND p.company_id = ?
        ");
        $stmt->execute([$product_id, $company_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(Exception $e) {
        return null;
    }
}

// Function to get packaging options for product
function getPackagingOptions($pdo, $product_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM packaging WHERE product_id = ? ORDER BY id ASC");
        $stmt->execute([$product_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(Exception $e) {
        return [];
    }
}

// Get user info
$user_role_name = getUserRole($pdo, $user_id);
$is_admin = isUserAdmin($pdo, $user_id, $company_id);

// Get user's assigned brands
$user_brands = getUserBrands($pdo, $user_id, $company_id);

// Get all brands (for admin)
$all_brands = [];
if ($is_admin) {
    $all_brands = getAllBrands($pdo, $company_id);
}

// Initialize cart from session
if (!isset($_SESSION['cart'][$company_id])) {
    $_SESSION['cart'][$company_id] = [];
}

$cart = &$_SESSION['cart'][$company_id];

// معالجة إضافة منتج إلى السلة
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $packaging_id = $_POST['packaging_id'] ?? null;
    $quantity = intval($_POST['quantity']);
    $notes = $_POST['notes'] ?? '';
    
    // التحقق من صلاحية المنتج للمستخدم
    if (!$is_admin) {
        $access_stmt = $pdo->prepare("
            SELECT pb.product_id 
            FROM product_brands pb
            INNER JOIN user_brands ub ON pb.brand_id = ub.brand_id
            WHERE pb.product_id = ? AND ub.user_id = ?
            LIMIT 1
        ");
        $access_stmt->execute([$product_id, $user_id]);
        $has_access = $access_stmt->fetch();
        
        if (!$has_access) {
            $cart_error = "ليس لديك صلاحية لإضافة هذا المنتج.";
        }
    }
    
    if (!isset($cart_error)) {
        try {
            // جلب بيانات المنتج
            $product = getProductWithBrands($pdo, $product_id, $company_id);
            
            if (!$product) {
                throw new Exception("المنتج غير موجود");
            }
            
            // جلب بيانات التغليف
            $packaging = null;
            if ($packaging_id) {
                $packaging_stmt = $pdo->prepare("SELECT * FROM packaging WHERE id = ?");
                $packaging_stmt->execute([$packaging_id]);
                $packaging = $packaging_stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            // إنشاء معرف فريد للعنصر في السلة
            $cart_item_id = $product_id . '_' . ($packaging_id ?: '0');
            
            // التحقق مما إذا كان المنتج موجود بالفعل في السلة
            if (isset($cart[$cart_item_id])) {
                // تحديث الكمية
                $cart[$cart_item_id]['quantity'] += $quantity;
                $cart[$cart_item_id]['notes'] = $notes; // تحديث الملاحظات
            } else {
                // إضافة عنصر جديد إلى السلة
                $cart[$cart_item_id] = [
                    'product_id' => $product_id,
                    'product_name' => $product['product_name_ar'],
                    'packaging_id' => $packaging_id,
                    'packaging_type' => $packaging ? $packaging['packaging_type'] : null,
                    'weight' => $packaging ? $packaging['weight'] : null,
                    'weight_unit' => $packaging ? $packaging['weight_unit'] : null,
                    'quantity_per_pack' => $packaging ? $packaging['quantity_per_pack'] : null,
                    'quantity' => $quantity,
                    'notes' => $notes,
                    'brand_ids' => explode(',', $product['brand_ids']),
                    'brand_names' => $product['brand_names']
                ];
            }
            
            $cart_success = "تم إضافة المنتج إلى السلة بنجاح!";
            
        } catch(Exception $e) {
            $cart_error = "حدث خطأ أثناء إضافة المنتج: " . $e->getMessage();
        }
    }
}

// معالجة إزالة عنصر من السلة
if (isset($_GET['remove_item'])) {
    $item_id = $_GET['remove_item'];
    if (isset($cart[$item_id])) {
        unset($cart[$item_id]);
        $cart_success = "تم إزالة المنتج من السلة";
    }
}

// معالجة تحديث الكمية
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $item_id => $quantity) {
        if (isset($cart[$item_id]) && $quantity > 0) {
            $cart[$item_id]['quantity'] = intval($quantity);
        } elseif (isset($cart[$item_id]) && $quantity <= 0) {
            unset($cart[$item_id]);
        }
    }
    $cart_success = "تم تحديث السلة بنجاح";
}

// معالجة إرسال الطلب النهائي
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_order'])) {
    if (empty($cart)) {
        $order_error = "السلة فارغة. يرجى إضافة منتجات أولاً.";
    } else {
        try {
            $pdo->beginTransaction();
            
            // إنشاء طلب جديد في sales_orders
            $order_stmt = $pdo->prepare("
                INSERT INTO sales_orders 
                (company_id, user_id, order_date, status, total_items) 
                VALUES (?, ?, CURDATE(), 'pending', ?)
            ");
            
            $total_items = count($cart);
            $order_stmt->execute([$company_id, $user_id, $total_items]);
            
            $order_id = $pdo->lastInsertId();
            
            // إضافة عناصر الطلب إلى order_items
            foreach ($cart as $item_id => $item) {
                // بناء وصف المنتج
                $product_description = $item['product_name'];
                
                if ($item['weight']) {
                    $product_description .= ' - ' . $item['weight'] . ' ' . $item['weight_unit'];
                }
                
                if ($item['packaging_type']) {
                    $product_description .= ' - ' . $item['packaging_type'];
                    if ($item['quantity_per_pack']) {
                        $product_description .= ' (' . $item['quantity_per_pack'] . ' قطعة)';
                    }
                }
                
                if (!empty($item['notes'])) {
                    $product_description .= ' (ملاحظات: ' . $item['notes'] . ')';
                }
                
                // إدخال عنصر الطلب في order_items
                $item_stmt = $pdo->prepare("
                    INSERT INTO order_items 
                    (order_id, product_id, packaging_id, product_description, quantity, notes, brand_ids) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                
                $brand_ids_str = implode(',', $item['brand_ids']);
                $item_stmt->execute([
                    $order_id,
                    $item['product_id'],
                    $item['packaging_id'],
                    $product_description,
                    $item['quantity'],
                    $item['notes'],
                    $brand_ids_str
                ]);
                
                // تحديث حقل product_name في sales_orders لأول منتج (للتوافق)
                if ($order_id && !isset($first_product_added)) {
                    $update_stmt = $pdo->prepare("
                        UPDATE sales_orders 
                        SET product_name = ? 
                        WHERE id = ?
                    ");
                    $update_stmt->execute([$product_description, $order_id]);
                    $first_product_added = true;
                }
            }
            
            $pdo->commit();
            
            // تفريغ السلة بعد إرسال الطلب
            unset($_SESSION['cart'][$company_id]);
            
            $order_success = "تم إرسال طلبك بنجاح! رقم الطلب: #" . $order_id;
            
        } catch(PDOException $e) {
            $pdo->rollBack();
            $order_error = "حدث خطأ أثناء حفظ الطلب: " . $e->getMessage();
            error_log("Order Error: " . $e->getMessage());
        }
    }
}

// معالجة إخلاء السلة
if (isset($_GET['clear_cart'])) {
    unset($_SESSION['cart'][$company_id]);
    $cart_success = "تم إخلاء السلة";
}

// تحديد العلامات التجارية المختارة من السلة
$selected_brands_in_cart = [];
if (!empty($cart)) {
    foreach ($cart as $item) {
        foreach ($item['brand_ids'] as $brand_id) {
            if (!in_array($brand_id, $selected_brands_in_cart)) {
                $selected_brands_in_cart[] = $brand_id;
            }
        }
    }
}

// حساب إجماليات السلة
$cart_total_items = 0;
$cart_total_quantity = 0;
foreach ($cart as $item) {
    $cart_total_items++;
    $cart_total_quantity += $item['quantity'];
}

// Get brands to display
$brands_to_display = [];
$products = [];
$selected_brand_id = $_GET['brand_id'] ?? null;

if ($is_admin) {
    $brands_to_display = $all_brands;
    if ($selected_brand_id) {
        $products = getProductsByBrand($pdo, $selected_brand_id, $company_id);
    } else {
        $products = getAllProducts($pdo, $company_id);
    }
} else {
    $brands_to_display = $user_brands;
    if ($selected_brand_id) {
        $products = getProductsByBrand($pdo, $selected_brand_id, $company_id);
    }
}

// جلب الأوزان والتعبئة للمنتج المختار
$packaging_options = [];
$selected_product_id = $_POST['product_id'] ?? null;

if ($selected_product_id) {
    $packaging_options = getPackagingOptions($pdo, $selected_product_id);
}

// جلب طلبات المستخدم السابقة
try {
    $stmt = $pdo->prepare("
        SELECT so.*,
               (SELECT COUNT(*) FROM order_items WHERE order_id = so.id) as item_count
        FROM sales_orders so
        WHERE so.user_id = ? AND so.company_id = ?
        ORDER BY so.created_at DESC
        LIMIT 10
    ");
    
    $stmt->execute([$user_id, $company_id]);
    $previous_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $previous_orders = [];
}

// جلب تفاصيل عناصر الطلبات السابقة
foreach ($previous_orders as &$order) {
    $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmt->execute([$order['id']]);
    $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
unset($order);

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلب مبيعات - <?php echo htmlspecialchars($company['name']); ?></title>
    
    <link rel="stylesheet" href="CSS/sales_order.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
    </style>
</head>
<body>
    <div class="order-page">
        <div class="order-container">
            <!-- رابط العودة -->
            <a href="company_profile.php?id=<?php echo $company_id; ?>" class="back-link">
                <i class="fas fa-arrow-right"></i>
                العودة إلى صفحة الشركة
            </a>
            
            <!-- عنوان الصفحة -->
            <div class="order-header">
                <h1 style="color: #2c3e50; margin-bottom: 0.5rem;">
                    <i class="fas fa-shopping-cart"></i>
                    طلب مبيعات جديد
                    <span class="user-role-badge role-<?php echo str_replace(' ', '_', strtolower($user_role_name)); ?>">
                        <?php echo htmlspecialchars($user_role_name); ?>
                    </span>
                </h1>
                <p style="color: #7f8c8d; margin-bottom: 0;">الشركة: <?php echo htmlspecialchars($company['name']); ?></p>
                <p style="color: #27ae60; margin-top: 0.5rem;">
                    <i class="fas fa-info-circle"></i> يمكنك الآن إضافة منتجات من علامات تجارية متعددة في طلب واحد
                </p>
            </div>
            
            <!-- رسائل التنبيه -->
            <?php if (isset($order_success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $order_success; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($order_error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $order_error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($cart_success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $cart_success; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($cart_error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $cart_error; ?>
                </div>
            <?php endif; ?>
            
            <div class="order-content">
                <!-- قسم سلة التسوق -->
                <div class="cart-section">
                    <div class="cart-header">
                        <div>
                            <h3 style="margin: 0; color: #2c3e50;">
                                <i class="fas fa-shopping-basket"></i>
                                سلة الطلبات
                            </h3>
                        </div>
                        
                        <div class="cart-totals">
                            <span class="cart-badge">
                                <i class="fas fa-boxes"></i>
                                <?php echo $cart_total_items; ?> منتج
                            </span>
                            <span class="cart-badge" style="background: #2ecc71;">
                                <i class="fas fa-calculator"></i>
                                <?php echo $cart_total_quantity; ?> وحدة
                            </span>
                        </div>
                        
                        <div class="cart-actions">
                            <?php if (!empty($cart)): ?>
                                <form method="post" style="display: inline;">
                                    <button type="submit" name="update_cart" class="btn-update-cart" 
                                            style="padding: 0.5rem 1rem; background: #3498db; color: white; border: none; border-radius: 5px; cursor: pointer;">
                                        <i class="fas fa-sync-alt"></i> تحديث
                                    </button>
                                </form>
                                <a href="?company_id=<?php echo $company_id; ?>&clear_cart=1" class="btn-clear-cart"
                                   onclick="return confirm('هل أنت متأكد من إخلاء السلة؟');">
                                    <i class="fas fa-trash"></i> إخلاء السلة
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($cart)): ?>
                        <form method="post" id="cartForm">
                            <div class="cart-items">
                                <?php foreach ($cart as $item_id => $item): ?>
                                    <div class="cart-item">
                                        <div class="cart-item-info">
                                            <div class="cart-item-header">
                                                <div class="cart-item-name">
                                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                                </div>
                                                <div class="cart-item-brands">
                                                    <i class="fas fa-tags"></i>
                                                    <?php echo htmlspecialchars($item['brand_names']); ?>
                                                </div>
                                            </div>
                                            
                                            <div class="cart-item-details">
                                                <?php if ($item['packaging_type']): ?>
                                                    <div class="cart-item-detail">
                                                        <span>التعبئة:</span>
                                                        <span><?php echo htmlspecialchars($item['packaging_type']); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if ($item['weight']): ?>
                                                    <div class="cart-item-detail">
                                                        <span>الوزن:</span>
                                                        <span><?php echo $item['weight']; ?> <?php echo htmlspecialchars($item['weight_unit']); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if ($item['quantity_per_pack']): ?>
                                                    <div class="cart-item-detail">
                                                        <span>الكمية/العبوة:</span>
                                                        <span><?php echo $item['quantity_per_pack']; ?> قطعة</span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <?php if (!empty($item['notes'])): ?>
                                                <div class="cart-item-notes">
                                                    <i class="fas fa-sticky-note"></i>
                                                    <?php echo htmlspecialchars($item['notes']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="cart-item-actions">
                                            <div class="cart-quantity-control">
                                                <input type="number" 
                                                       name="quantity[<?php echo $item_id; ?>]" 
                                                       class="cart-quantity-input" 
                                                       value="<?php echo $item['quantity']; ?>" 
                                                       min="1" 
                                                       max="1000">
                                            </div>
                                            
                                            <a href="?company_id=<?php echo $company_id; ?>&remove_item=<?php echo urlencode($item_id); ?>" 
                                               class="btn-remove-item"
                                               onclick="return confirm('هل أنت متأكد من إزالة هذا المنتج؟');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </form>
                        
                        <!-- قسم إرسال الطلب -->
                        <div class="submit-order-section">
                            <form method="post">
                                <button type="submit" name="submit_order" class="btn-submit-order">
                                    <i class="fas fa-paper-plane"></i>
                                    إرسال الطلب النهائي
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="empty-cart">
                            <i class="fas fa-shopping-basket fa-3x" style="color: #bdc3c7; margin-bottom: 1rem;"></i>
                            <h4 style="color: #2c3e50; margin-bottom: 0.5rem;">السلة فارغة</h4>
                            <p style="color: #7f8c8d;">أضف منتجات من العلامات التجارية المختلفة لإتمام الطلب</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- قسم العلامات التجارية -->
                <?php if (!empty($brands_to_display)): ?>
                <div class="brand-filter-section">
                    <h4><i class="fas fa-crown"></i> اختيار العلامة التجارية</h4>
                    <p style="color: #7f8c8d; margin-bottom: 1rem;">
                        <i class="fas fa-info-circle"></i>
                        اختر علامة تجارية لعرض منتجاتها
                    </p>
                    
                    <div class="brand-selection-grid">
                        <?php if ($is_admin): ?>
                            <!-- Admin can see all brands or no brand -->
                            <div class="brand-option <?php echo !$selected_brand_id ? 'selected' : ''; ?> <?php echo empty($cart) ? '' : 'in-cart'; ?>"
                                 onclick="window.location.href='?company_id=<?php echo $company_id; ?>&brand_id='">
                                <div class="brand-icon">
                                    <i class="fas fa-globe"></i>
                                </div>
                                <div class="brand-name">جميع المنتجات</div>
                                <small style="color: #7f8c8d;">عرض جميع المنتجات</small>
                            </div>
                            
                            <?php foreach ($all_brands as $brand): 
                                $is_in_cart = in_array($brand['id'], $selected_brands_in_cart);
                            ?>
                            <div class="brand-option <?php echo ($selected_brand_id == $brand['id']) ? 'selected' : ''; ?> <?php echo $is_in_cart ? 'in-cart' : ''; ?>"
                                 onclick="window.location.href='?company_id=<?php echo $company_id; ?>&brand_id=<?php echo $brand['id']; ?>'">
                                <div class="brand-icon">
                                    <?php if (!empty($brand['brand_logo'])): ?>
                                        <img src="<?php echo htmlspecialchars($brand['brand_logo']); ?>" 
                                             alt="<?php echo htmlspecialchars($brand['brand_name']); ?>"
                                             class="brand-logo-img"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block'">
                                        <i class="fas fa-crown" style="display: none; font-size: 2rem; color: #3498db;"></i>
                                    <?php else: ?>
                                        <i class="fas fa-crown" style="font-size: 2rem; color: #3498db;"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="brand-name"><?php echo htmlspecialchars($brand['brand_name']); ?></div>
                                <?php if ($is_in_cart): ?>
                                    <small style="color: #f39c12;">
                                        <i class="fas fa-check"></i> موجود في السلة
                                    </small>
                                <?php else: ?>
                                    <small style="color: #7f8c8d;">عرض منتجات العلامة</small>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Regular users can only see their assigned brands -->
                            <?php foreach ($user_brands as $brand): 
                                $is_in_cart = in_array($brand['id'], $selected_brands_in_cart);
                            ?>
                            <div class="brand-option <?php echo ($selected_brand_id == $brand['id']) ? 'selected' : ''; ?> <?php echo $is_in_cart ? 'in-cart' : ''; ?>"
                                 onclick="window.location.href='?company_id=<?php echo $company_id; ?>&brand_id=<?php echo $brand['id']; ?>'">
                                <div class="brand-icon">
                                    <?php if (!empty($brand['brand_logo'])): ?>
                                        <img src="<?php echo htmlspecialchars($brand['brand_logo']); ?>" 
                                             alt="<?php echo htmlspecialchars($brand['brand_name']); ?>"
                                             class="brand-logo-img"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block'">
                                        <i class="fas fa-crown" style="display: none; font-size: 2rem; color: #3498db;"></i>
                                    <?php else: ?>
                                        <i class="fas fa-crown" style="font-size: 2rem; color: #3498db;"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="brand-name"><?php echo htmlspecialchars($brand['brand_name']); ?></div>
                                <?php if ($is_in_cart): ?>
                                    <small style="color: #f39c12;">
                                        <i class="fas fa-check"></i> موجود في السلة
                                    </small>
                                <?php else: ?>
                                    <small style="color: #7f8c8d;">مسموح لك بالوصول</small>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- قسم المنتجات -->
                <?php if ($selected_brand_id || ($is_admin && !$selected_brand_id)): ?>
                <div class="order-form-section">
                    <h2 class="order-form-title">
                        <i class="fas fa-box"></i>
                        <?php if ($selected_brand_id): ?>
                            منتجات العلامة التجارية
                        <?php else: ?>
                            جميع المنتجات
                        <?php endif; ?>
                    </h2>
                    
                    <?php if (!empty($products)): ?>
                        <div style="margin-bottom: 1.5rem;">
                            <form method="POST" id="productForm">
                                <input type="hidden" name="brand_id" value="<?php echo $selected_brand_id; ?>">
                                
                                <div class="form-group">
                                    <label for="product_id">
                                        <i class="fas fa-tag"></i>
                                        اختر المنتج
                                    </label>
                                    <select id="product_id" name="product_id" required onchange="loadPackagingOptions(this.value)">
                                        <option value="">-- اختر منتجًا --</option>
                                        <?php foreach ($products as $product): ?>
                                            <option value="<?php echo $product['id']; ?>">
                                                <?php echo htmlspecialchars($product['product_name_ar']); ?>
                                                <?php if (!empty($product['subcategory_name'])): ?>
                                                    (<?php echo htmlspecialchars($product['subcategory_name']); ?>)
                                                <?php endif; ?>
                                                <?php if (!empty($product['brand_names'])): ?>
                                                    - <?php echo htmlspecialchars($product['brand_names']); ?>
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- قسم خيارات التعبئة -->
                                <div id="packagingSection" style="display: none;">
                                    <h4><i class="fas fa-weight-hanging"></i> اختيار الوزن والتعبئة</h4>
                                    <div id="packagingOptions"></div>
                                    
                                    <!-- قسم الكمية -->
                                    <div class="form-step">
                                        <h4><i class="fas fa-calculator"></i> تحديد الكمية</h4>
                                        <div class="quantity-control">
                                            <button type="button" class="quantity-btn" onclick="decreaseQuantity()">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" id="quantity" name="quantity" class="quantity-input" 
                                                   value="1" min="1" max="1000" required>
                                            <button type="button" class="quantity-btn" onclick="increaseQuantity()">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- قسم الملاحظات -->
                                    <div class="notes-section">
                                        <h4><i class="fas fa-edit"></i> ملاحظات إضافية</h4>
                                        <textarea id="notes" name="notes" 
                                                  placeholder="اكتب هنا أي ملاحظات خاصة بهذا المنتج (اختياري)..."></textarea>
                                    </div>
                                    
                                    <!-- زر إضافة إلى السلة -->
                                    <button type="submit" name="add_to_cart" class="btn-submit-order">
                                        <i class="fas fa-cart-plus"></i>
                                        إضافة إلى السلة
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php else: ?>
                        <p style="color: #7f8c8d; text-align: center; padding: 2rem;">
                            <i class="fas fa-exclamation-circle"></i>
                            لا توجد منتجات متاحة
                        </p>
                    <?php endif; ?>
                </div>
                <?php elseif (!empty($brands_to_display)): ?>
                <div class="order-form-section" style="text-align: center; padding: 3rem 2rem; color: #7f8c8d; background: #f8f9fa; border-radius: 10px;">
                    <i class="fas fa-crown fa-3x" style="margin-bottom: 1rem; color: #bdc3c7;"></i>
                    <h3 style="color: #2c3e50; margin-bottom: 1rem;">اختر علامة تجارية أولاً</h3>
                    <p>اختر علامة تجارية من القائمة لعرض منتجاتها وإضافتها إلى السلة</p>
                </div>
                <?php endif; ?>
                
                <!-- قسم سجل الطلبات السابقة -->
                <div class="order-history-section">
                    <h2 class="order-history-title">
                        <i class="fas fa-history"></i>
                        طلباتي السابقة
                    </h2>
                    
                    <div class="order-history-list">
                        <?php if (!empty($previous_orders)): ?>
                            <?php foreach ($previous_orders as $order): ?>
                                <div class="order-item">
                                    <div class="order-header-info">
                                        <div>
                                            <span class="order-number">طلب #<?php echo $order['id']; ?></span>
                                            <span class="order-date">
                                                <i class="fas fa-calendar"></i>
                                                <?php echo date('Y/m/d', strtotime($order['order_date'])); ?>
                                            </span>
                                        </div>
                                        <span class="order-status status-<?php echo $order['status']; ?>">
                                            <?php 
                                            $status_labels = [
                                                'pending' => 'قيد الانتظار',
                                                'confirmed' => 'مؤكد',
                                                'shipped' => 'تم الشحن',
                                                'delivered' => 'تم التوصيل',
                                                'cancelled' => 'ملغى'
                                            ];
                                            echo $status_labels[$order['status']] ?? $order['status'];
                                            ?>
                                        </span>
                                    </div>
                                    
                                    <div class="order-details">
                                        <div><strong>عدد المنتجات:</strong> <?php echo $order['item_count']; ?> منتج</div>
                                        <div><strong>حالة الطلب:</strong> <?php echo $status_labels[$order['status']] ?? $order['status']; ?></div>
                                    </div>
                                    
                                    <?php if (!empty($order['items'])): ?>
                                        <div class="order-items-list" style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #eee;">
                                            <?php foreach ($order['items'] as $item): ?>
                                                <div style="margin-bottom: 0.5rem; padding: 0.5rem; background: #f8f9fa; border-radius: 5px;">
                                                    <div><strong>المنتج:</strong> <?php echo htmlspecialchars($item['product_description']); ?></div>
                                                    <div><strong>الكمية:</strong> <?php echo $item['quantity']; ?> وحدة</div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-orders">
                                <i class="fas fa-clipboard-list"></i>
                                <h4>لا توجد طلبات سابقة</h4>
                                <p>لم تقم بإرسال أي طلبات لهذه الشركة بعد.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // وظائف JavaScript
        let selectedPackaging = null;
        let selectedPackagingElement = null;
        
        function loadPackagingOptions(productId) {
            if (!productId) {
                document.getElementById('packagingSection').style.display = 'none';
                return;
            }
            
            // إظهار قسم التعبئة
            document.getElementById('packagingSection').style.display = 'block';
            
            // جلب خيارات التعبئة باستخدام AJAX
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `get_packaging.php?product_id=${productId}`, true);
            
            xhr.onload = function() {
                if (this.status === 200) {
                    document.getElementById('packagingOptions').innerHTML = this.responseText;
                    selectedPackaging = null;
                    selectedPackagingElement = null;
                }
            };
            
            xhr.send();
        }
        
        function selectPackaging(packagingId, element) {
            selectedPackaging = packagingId;
            document.getElementById('packaging_id').value = packagingId;
            
            // إزالة التحديد من جميع العناصر
            document.querySelectorAll('#packagingOptions .product-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            // إضافة التحديد للعنصر المختار
            element.classList.add('selected');
            selectedPackagingElement = element;
        }
        
        function increaseQuantity() {
            const quantityInput = document.getElementById('quantity');
            let quantity = parseInt(quantityInput.value);
            if (quantity < 1000) {
                quantityInput.value = quantity + 1;
            }
        }
        
        function decreaseQuantity() {
            const quantityInput = document.getElementById('quantity');
            let quantity = parseInt(quantityInput.value);
            if (quantity > 1) {
                quantityInput.value = quantity - 1;
            }
        }
        
        // التحقق من النموذج قبل الإضافة إلى السلة
        document.getElementById('productForm').addEventListener('submit', function(e) {
            const productId = document.getElementById('product_id').value;
            const quantity = parseInt(document.getElementById('quantity').value);
            
            if (!productId) {
                e.preventDefault();
                alert('يرجى اختيار منتج');
                return false;
            }
            
            if (!selectedPackaging) {
                e.preventDefault();
                alert('يرجى اختيار نوع التعبئة والوزن');
                return false;
            }
            
            if (!quantity || quantity < 1) {
                e.preventDefault();
                alert('يرجى تحديد كمية صحيحة (الحد الأدنى 1)');
                return false;
            }
            
            if (quantity > 1000) {
                e.preventDefault();
                alert('الكمية تتجاوز الحد الأقصى المسموح به (1000)');
                return false;
            }
            
            return true;
        });
        
        // تحديث الكمية عند التغيير
        document.getElementById('quantity').addEventListener('input', function() {
            const quantity = parseInt(this.value);
            if (quantity < 1) this.value = 1;
            if (quantity > 1000) this.value = 1000;
        });
        
        // تحديث السلة عند تغيير الكمية في نموذج السلة
        document.querySelectorAll('.cart-quantity-input').forEach(input => {
            input.addEventListener('change', function() {
                document.getElementById('cartForm').submit();
            });
        });
    </script>
</body>
</html>