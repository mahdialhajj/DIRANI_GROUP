<?php
// admin_orders.php
include 'config.php';

// تعيين التوقيت المحلي للبنان
date_default_timezone_set('Asia/Beirut');

// التحقق من تسجيل الدخول والصلاحيات
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// جلب دور المستخدم وبياناته
$user_id = $_SESSION['user_id'];
try {
    $stmt = $pdo->prepare("
        SELECT u.*, ur.role_name 
        FROM users u
        JOIN user_roles ur ON u.role_id = ur.id
        WHERE u.id = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        header("Location: logout.php");
        exit;
    }
    
    // التحقق من الصلاحيات (sales_manager أو admin فقط)
    if (!in_array($user['role_name'], ['sales_manager', 'admin'])) {
        header("Location: index.php");
        exit;
    }
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// جلب العميل المرتبط بالمستخدم (إذا كان ليس admin)
$user_customer_id = null;
if ($user['role_name'] != 'admin') {
    if (!empty($user['customer_id'])) {
        $user_customer_id = $user['customer_id'];
    }
}

// جلب company_id من الرابط
$company_id = $_GET['company_id'] ?? null;
$customer_id = $_GET['customer_id'] ?? null;
$status_filter = $_GET['status'] ?? 'all';

// معالجة عرض الفاتورة للطباعة
if (isset($_GET['print_invoice']) && isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
    
    try {
        // جلب بيانات الطلب
        $stmt = $pdo->prepare("
            SELECT so.*, 
                   c.name as company_name,
                   u.username as customer_name
            FROM sales_orders so
            LEFT JOIN companies c ON so.company_id = c.id
            LEFT JOIN users u ON so.user_id = u.id
            WHERE so.id = ?
        ");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            die("الطلب غير موجود");
        }
        
        // التحقق من صلاحية المستخدم للوصول لهذا الطلب
        if ($user['role_name'] != 'admin' && $user_customer_id && $order['user_id'] != $user_customer_id) {
            die("ليس لديك صلاحية لعرض هذا الطلب");
        }
        
        // جلب عناصر الطلب مع العلامات التجارية
        $items_stmt = $pdo->prepare("
            SELECT oi.*, 
                   GROUP_CONCAT(DISTINCT cb.brand_name SEPARATOR ', ') as brand_names
            FROM order_items oi
            LEFT JOIN company_brands cb ON FIND_IN_SET(cb.id, oi.brand_ids)
            WHERE oi.order_id = ?
            GROUP BY oi.id
            ORDER BY oi.id ASC
        ");
        $items_stmt->execute([$order_id]);
        $order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch(PDOException $e) {
        die("Error: " . $e->getMessage());
    }
    
    // عرض صفحة الفاتورة للطباعة
    ?>
    <!DOCTYPE html>
    <html lang="ar" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="CSS/admin_orders.css">
        <title>فاتورة الطلب #<?php echo $order_id; ?></title>
        <style>

        </style>
    </head>
    <body>
        <div class="no-print">
            <button onclick="window.print()" class="print-button">
                <i class="fas fa-print"></i> طباعة الفاتورة
            </button>
            <a href="admin_orders.php" class="back-button">
                <i class="fas fa-arrow-right"></i> العودة للطلبات
            </a>
        </div>
        
        <div class="invoice-container">
            <div class="invoice-header">
                <h1 class="invoice-title">فاتورة طلب</h1>
                <p class="invoice-subtitle">نظام إدارة المبيعات</p>
                <div class="invoice-number">رقم الفاتورة: #<?php echo $order['id']; ?></div>
            </div>
            
            <div class="invoice-info">
                <div class="info-section">
                    <div class="info-label">معلومات الشركة</div>
                    <div class="info-value"><?php echo htmlspecialchars($order['company_name'] ?? 'غير محدد'); ?></div>
                </div>
                
                <div class="info-section">
                    <div class="info-label">معلومات العميل</div>
                    <div class="info-value"><?php echo htmlspecialchars($order['customer_name'] ?? 'غير محدد'); ?></div>
                </div>
                
                <div class="info-section">
                    <div class="info-label">تاريخ الطلب</div>
                    <div class="info-value"><?php echo date('Y/m/d', strtotime($order['order_date'])); ?></div>
                </div>
                
                <div class="info-section">
                    <div class="info-label">حالة الطلب</div>
                    <div class="info-value">
                        <?php 
                        $status_labels = [
                            'pending' => 'قيد الانتظار',
                            'confirmed' => 'مؤكد',
                            'shipped' => 'تم الشحن',
                            'delivered' => 'تم التوصيل',
                            'cancelled' => 'ملغي'
                        ];
                        echo $status_labels[$order['status']] ?? $order['status'];
                        ?>
                    </div>
                </div>
            </div>
            
            <table class="items-table">
                <thead>
                    <tr>
                        <th>المنتج</th>
                        <th>العلامة التجارية</th>
                        <th>الكمية</th>
                        <th>ملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_quantity = 0;
                    if (!empty($order_items)): 
                        foreach ($order_items as $item): 
                            $total_quantity += $item['quantity'];
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_description']); ?></td>
                        <td>
                            <?php if (!empty($item['brand_names'])): ?>
                                <span class="brand-name"><?php echo htmlspecialchars($item['brand_names']); ?></span>
                            <?php else: ?>
                                <span style="color: #999;">غير محدد</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo !empty($item['notes']) ? htmlspecialchars($item['notes']) : '---'; ?></td>
                    </tr>
                    <?php 
                        endforeach; 
                    else: 
                    ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: #7f8c8d;">لا توجد عناصر</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <div class="invoice-footer">
                <div class="total-section">
                    <div class="total-row">
                        <span class="total-label">إجمالي عدد المنتجات:</span>
                        <span class="total-value"><?php echo count($order_items); ?> منتج</span>
                    </div>
                    <div class="total-row">
                        <span class="total-label">إجمالي عدد الوحدات:</span>
                        <span class="total-value"><?php echo $total_quantity; ?> وحدة</span>
                    </div>
                </div>
                
                <?php if (!empty($order['notes'])): ?>
                <div class="notes-section">
                    <span class="notes-label">ملاحظات الطلب:</span>
                    <p class="notes-text"><?php echo htmlspecialchars($order['notes']); ?></p>
                </div>
                <?php endif; ?>
                
                <div class="company-info">
                    <p>شكراً لتعاملكم معنا</p>
                    <p>هذه الفاتورة تم إنشاؤها تلقائياً بواسطة نظام إدارة المبيعات</p>
                    <p>تاريخ الطباعة: <?php echo date('Y/m/d H:i'); ?></p>
                </div>
            </div>
        </div>
        
        <script>
            window.onload = function() {
                setTimeout(function() {
                    window.print();
                }, 1000);
            };
            
            window.onafterprint = function() {
                setTimeout(function() {
                    window.location.href = 'admin_orders.php';
                }, 1000);
            };
        </script>
    </body>
    </html>
    <?php
    exit;
}

// بناء استعلام الطلبات
$query = "
    SELECT so.*, 
           c.name as company_name,
           u.username as customer_name,
           (SELECT COUNT(*) FROM order_items WHERE order_id = so.id) as item_count
    FROM sales_orders so
    LEFT JOIN companies c ON so.company_id = c.id
    LEFT JOIN users u ON so.user_id = u.id
";

$params = [];
$conditions = [];

// تطبيق الفلاتر
if ($company_id) {
    $conditions[] = "so.company_id = ?";
    $params[] = $company_id;
}

if ($customer_id) {
    $conditions[] = "so.user_id = ?";
    $params[] = $customer_id;
} elseif ($user['role_name'] != 'admin' && $user_customer_id) {
    $conditions[] = "so.user_id = ?";
    $params[] = $user_customer_id;
}

if ($status_filter != 'all') {
    $conditions[] = "so.status = ?";
    $params[] = $status_filter;
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY so.created_at DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // جلب عناصر كل طلب مع العلامات التجارية
    foreach ($orders as &$order) {
        $items_stmt = $pdo->prepare("
            SELECT oi.*, 
                   GROUP_CONCAT(DISTINCT cb.brand_name SEPARATOR ', ') as brand_names
            FROM order_items oi
            LEFT JOIN company_brands cb ON FIND_IN_SET(cb.id, oi.brand_ids)
            WHERE oi.order_id = ?
            GROUP BY oi.id
        ");
        $items_stmt->execute([$order['id']]);
        $order['items'] = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($order);
    
    // جلب إحصائيات الطلبات
    $stats_stmt = $pdo->prepare("
        SELECT 
            status,
            COUNT(*) as count
        FROM sales_orders
        " . (!empty($conditions) ? "WHERE " . implode(" AND ", array_map(function($c) { 
            return str_replace('so.', '', $c); 
        }, $conditions)) : "") . "
        GROUP BY status
        ORDER BY status
    ");
    
    $stats_stmt->execute($params);
    $status_stats = $stats_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // جلب جميع الشركات للفلتر
    if ($user['role_name'] == 'admin') {
        $companies_stmt = $pdo->prepare("SELECT id, name FROM companies ORDER BY name ASC");
        $companies_stmt->execute();
    } else {
        $companies_stmt = $pdo->prepare("
            SELECT c.id, c.name 
            FROM companies c
            JOIN sales_orders so ON c.id = so.company_id
            WHERE so.user_id = ?
            GROUP BY c.id
            ORDER BY c.name ASC
        ");
        $companies_stmt->execute([$user_customer_id]);
    }
    $all_companies = $companies_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // جلب جميع العملاء للفلتر (للمسؤول فقط)
    $all_customers = [];
    if ($user['role_name'] == 'admin') {
        $customers_stmt = $pdo->prepare("
            SELECT id, username 
            FROM users 
            WHERE role_id = (SELECT id FROM user_roles WHERE role_name = 'customer') 
            ORDER BY username ASC
        ");
        $customers_stmt->execute();
        $all_customers = $customers_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// معالجة تحديث حالة الطلب
$update_error = '';
$update_success = '';
$delete_error = '';
$delete_success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_status'])) {
        $order_id = $_POST['order_id'];
        $new_status = $_POST['status'];
        
        try {
            $check_stmt = $pdo->prepare("
                SELECT so.* FROM sales_orders so
                WHERE so.id = ?
            ");
            $check_stmt->execute([$order_id]);
            $check_order = $check_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$check_order) {
                $update_error = "الطلب غير موجود";
            } elseif ($user['role_name'] != 'admin' && $user_customer_id && $check_order['user_id'] != $user_customer_id) {
                $update_error = "ليس لديك صلاحية لتحديث هذا الطلب";
            } else {
                $stmt = $pdo->prepare("UPDATE sales_orders SET status = ? WHERE id = ?");
                $stmt->execute([$new_status, $order_id]);
                
                $update_success = "تم تحديث حالة الطلب #{$order_id} بنجاح";
                header("Location: admin_orders.php?" . http_build_query($_GET));
                exit;
            }
            
        } catch(PDOException $e) {
            $update_error = "حدث خطأ أثناء تحديث حالة الطلب: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['delete_order'])) {
        $order_id = $_POST['order_id'];
        
        try {
            $check_stmt = $pdo->prepare("
                SELECT so.* FROM sales_orders so
                WHERE so.id = ?
            ");
            $check_stmt->execute([$order_id]);
            $check_order = $check_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$check_order) {
                $delete_error = "الطلب غير موجود";
            } elseif ($user['role_name'] != 'admin') {
                $delete_error = "ليس لديك صلاحية لحذف الطلبات";
            } else {
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
                $stmt->execute([$order_id]);
                
                $stmt = $pdo->prepare("DELETE FROM sales_orders WHERE id = ?");
                $stmt->execute([$order_id]);
                
                $pdo->commit();
                
                $delete_success = "تم حذف الطلب #{$order_id} بنجاح";
                header("Location: admin_orders.php?" . http_build_query($_GET));
                exit;
            }
            
        } catch(PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $delete_error = "حدث خطأ أثناء حذف الطلب: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الطلبات - نظام المبيعات</title>
    <link rel="stylesheet" href="CSS/admin_orders.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <a href="index.php" class="back-link">
            <i class="fas fa-arrow-right"></i>
            العودة للرئيسية
        </a>
        
        <div class="admin-header">
            <h1 style="margin-bottom: 0.5rem;">
                <i class="fas fa-clipboard-list"></i>
                إدارة الطلبات
            </h1>
            <p style="color: rgba(255,255,255,0.9); margin-bottom: 0;">
                المستخدم: <?php echo htmlspecialchars($user['username']); ?> | 
                الدور: <?php echo htmlspecialchars($user['role_name']); ?>
                <?php if ($user['role_name'] != 'admin' && $user_customer_id): ?>
                    <?php 
                        $customer_stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
                        $customer_stmt->execute([$user_customer_id]);
                        $customer = $customer_stmt->fetch(PDO::FETCH_ASSOC);
                    ?>
                    | العميل: <?php echo htmlspecialchars($customer['username'] ?? 'غير محدد'); ?>
                <?php endif; ?>
            </p>
        </div>
        
        <?php if ($user['role_name'] != 'admin' && $user_customer_id): ?>
            <div class="user-permission-note">
                <i class="fas fa-info-circle"></i>
                أنت تستطيع فقط عرض وإدارة طلبات العميل المرتبط بحسابك.
            </div>
        <?php endif; ?>
        
        <?php if ($update_success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $update_success; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($update_error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $update_error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($delete_success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $delete_success; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($delete_error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $delete_error; ?>
            </div>
        <?php endif; ?>
        
        <div class="stats-cards">
            <?php 
            $total_orders = count($orders);
            $pending_orders = array_filter($orders, fn($o) => $o['status'] == 'pending');
            $confirmed_orders = array_filter($orders, fn($o) => $o['status'] == 'confirmed');
            $shipped_orders = array_filter($orders, fn($o) => $o['status'] == 'shipped');
            $delivered_orders = array_filter($orders, fn($o) => $o['status'] == 'delivered');
            $total_items = array_sum(array_column($orders, 'item_count'));
            ?>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_orders; ?></div>
                <div class="stat-label">إجمالي الطلبات</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_items; ?></div>
                <div class="stat-label">إجمالي المنتجات</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo count($pending_orders); ?></div>
                <div class="stat-label">طلبات قيد الانتظار</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo count($confirmed_orders); ?></div>
                <div class="stat-label">طلبات مؤكدة</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo count($delivered_orders); ?></div>
                <div class="stat-label">طلبات منتهية</div>
            </div>
        </div>
        
        <div class="filters-section">
            <h3 style="color: #2c3e50; margin-bottom: 1rem;">
                <i class="fas fa-filter"></i>
                تصفية الطلبات
            </h3>
            
            <form method="GET" action="admin_orders.php">
                <div class="filters-grid">
                    <?php if ($user['role_name'] == 'admin'): ?>
                        <div class="filter-group">
                            <label for="customer_id">العميل</label>
                            <select id="customer_id" name="customer_id">
                                <option value="">جميع العملاء</option>
                                <?php foreach ($all_customers as $customer): ?>
                                    <option value="<?php echo $customer['id']; ?>" 
                                        <?php echo ($customer_id == $customer['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($customer['username']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    
                    <div class="filter-group">
                        <label for="company_id">الشركة</label>
                        <select id="company_id" name="company_id">
                            <option value="">جميع الشركات</option>
                            <?php foreach ($all_companies as $company): ?>
                                <option value="<?php echo $company['id']; ?>" 
                                    <?php echo ($company_id == $company['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($company['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="status">حالة الطلب</label>
                        <select id="status" name="status">
                            <option value="all" <?php echo ($status_filter == 'all') ? 'selected' : ''; ?>>جميع الحالات</option>
                            <option value="pending" <?php echo ($status_filter == 'pending') ? 'selected' : ''; ?>>قيد الانتظار</option>
                            <option value="confirmed" <?php echo ($status_filter == 'confirmed') ? 'selected' : ''; ?>>مؤكد</option>
                            <option value="shipped" <?php echo ($status_filter == 'shipped') ? 'selected' : ''; ?>>تم الشحن</option>
                            <option value="delivered" <?php echo ($status_filter == 'delivered') ? 'selected' : ''; ?>>تم التوصيل</option>
                            <option value="cancelled" <?php echo ($status_filter == 'cancelled') ? 'selected' : ''; ?>>ملغي</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="btn-apply-filters">
                    <i class="fas fa-search"></i>
                    تطبيق الفلاتر
                </button>
            </form>
        </div>
        
        <div class="orders-table-container">
            <?php if (empty($orders)): ?>
                <div class="no-orders">
                    <i class="fas fa-clipboard-list fa-3x" style="margin-bottom: 1rem; color: #bdc3c7;"></i>
                    <h3 style="color: #2c3e50; margin-bottom: 1rem;">لا توجد طلبات</h3>
                    <p>لم يتم العثور على طلبات تطابق معايير البحث</p>
                </div>
            <?php else: ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>رقم الطلب</th>
                            <th>الشركة</th>
                            <th>عدد المنتجات</th>
                            <th>العميل</th>
                            <th>التاريخ</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><strong>#<?php echo $order['id']; ?></strong></td>
                            <td><?php echo htmlspecialchars($order['company_name'] ?? 'غير معروف'); ?></td>
                            <td><?php echo $order['item_count']; ?> منتج</td>
                            <td><?php echo htmlspecialchars($order['customer_name'] ?? 'غير معروف'); ?></td>
                            <td><?php echo date('Y/m/d', strtotime($order['order_date'])); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php 
                                    $status_labels = [
                                        'pending' => 'قيد الانتظار',
                                        'confirmed' => 'مؤكد',
                                        'shipped' => 'تم الشحن',
                                        'delivered' => 'تم التوصيل',
                                        'cancelled' => 'ملغي'
                                    ];
                                    echo $status_labels[$order['status']] ?? $order['status'];
                                    ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <?php if ($order['status'] == 'pending'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <input type="hidden" name="status" value="confirmed">
                                            <button type="submit" name="update_status" class="btn-status btn-confirm" 
                                                    onclick="return confirm('هل تريد تأكيد هذا الطلب؟')">
                                                <i class="fas fa-check"></i> تأكيد
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" name="update_status" class="btn-status btn-cancel" 
                                                    onclick="return confirm('هل تريد إلغاء هذا الطلب؟')">
                                                <i class="fas fa-times"></i> إلغاء
                                            </button>
                                        </form>
                                    <?php elseif ($order['status'] == 'confirmed'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <input type="hidden" name="status" value="shipped">
                                            <button type="submit" name="update_status" class="btn-status btn-ship" 
                                                    onclick="return confirm('هل تريد تحديث حالة الطلب إلى تم الشحن؟')">
                                                <i class="fas fa-shipping-fast"></i> شحن
                                            </button>
                                        </form>
                                    <?php elseif ($order['status'] == 'shipped'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <input type="hidden" name="status" value="delivered">
                                            <button type="submit" name="update_status" class="btn-status btn-deliver" 
                                                    onclick="return confirm('هل تريد تأكيد استلام الطلب؟')">
                                                <i class="fas fa-check-double"></i> تسليم
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <button type="button" class="btn-status btn-view"
                                            onclick="showOrderDetails(<?php echo htmlspecialchars(json_encode($order), ENT_QUOTES, 'UTF-8'); ?>)">
                                        <i class="fas fa-eye"></i> تفاصيل
                                    </button>
                                    
                                    <a href="admin_orders.php?print_invoice=1&order_id=<?php echo $order['id']; ?>" 
                                       target="_blank" 
                                       class="btn-status btn-print">
                                        <i class="fas fa-print"></i> طباعة
                                    </a>
                                    
                                    <?php if ($user['role_name'] == 'admin'): ?>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا الطلب؟ هذا الإجراء لا يمكن التراجع عنه.');">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <button type="submit" name="delete_order" class="btn-status btn-delete">
                                                <i class="fas fa-trash"></i> حذف
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="order-details-modal" id="orderDetailsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 style="color: #2c3e50; margin: 0;">
                    <i class="fas fa-info-circle"></i>
                    تفاصيل الطلب
                </h3>
                <button type="button" class="close-modal" onclick="closeOrderDetails()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div id="orderDetailsContent">
                <!-- سيتم ملؤه بالجافاسكريبت -->
            </div>
        </div>
    </div>
    
    <script>
        function showOrderDetails(order) {
            const modal = document.getElementById('orderDetailsModal');
            const content = document.getElementById('orderDetailsContent');
            
            const orderDate = new Date(order.order_date);
            const formattedDate = orderDate.toLocaleDateString('ar-SA');
            
            const statusLabels = {
                'pending': 'قيد الانتظار',
                'confirmed': 'مؤكد',
                'shipped': 'تم الشحن',
                'delivered': 'تم التوصيل',
                'cancelled': 'ملغي'
            };
            
            const statusClass = {
                'pending': 'status-pending',
                'confirmed': 'status-confirmed',
                'shipped': 'status-shipped',
                'delivered': 'status-delivered',
                'cancelled': 'status-cancelled'
            };
            
            let itemsTable = '';
            let totalQuantity = 0;
            
            if (order.items && order.items.length > 0) {
                itemsTable = `
                    <div class="items-table-container">
                        <h4 style="color: #2c3e50; margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-box-open"></i>
                            عناصر الطلب (${order.items.length})
                        </h4>
                        <table class="items-table-details">
                            <thead>
                                <tr>
                                    <th>المنتج</th>
                                    <th>العلامة التجارية</th>
                                    <th>الكمية</th>
                                    <th>ملاحظات</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                order.items.forEach(item => {
                    totalQuantity += item.quantity;
                    itemsTable += `
                        <tr>
                            <td>${item.product_description}</td>
                            <td class="brand-name-cell">
                                ${item.brand_names ? item.brand_names : 'غير محدد'}
                            </td>
                            <td><strong>${item.quantity}</strong></td>
                            <td>${item.notes ? item.notes : '---'}</td>
                        </tr>
                    `;
                });
                
                itemsTable += `
                            </tbody>
                        </table>
                    </div>
                `;
            } else {
                itemsTable = '<p style="color: #7f8c8d; text-align: center;">لا توجد عناصر</p>';
            }
            
            const detailsHtml = `
                <div class="order-info-grid">
                    <div class="info-item">
                        <span class="info-label">رقم الطلب</span>
                        <span class="info-value">#${order.id}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">تاريخ الطلب</span>
                        <span class="info-value">${formattedDate}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">الشركة</span>
                        <span class="info-value">${order.company_name || 'غير معروف'}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">العميل</span>
                        <span class="info-value">${order.customer_name || 'غير معروف'}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">عدد المنتجات</span>
                        <span class="info-value">${order.item_count} منتج</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">الحالة</span>
                        <span class="info-value">
                            <span class="status-badge ${statusClass[order.status] || ''}">
                                ${statusLabels[order.status] || order.status}
                            </span>
                        </span>
                    </div>
                </div>
                
                ${itemsTable}
                
                <div class="total-summary">
                    <span style="font-weight: 500;">
                        <i class="fas fa-calculator"></i>
                        إجمالي الوحدات
                    </span>
                    <span style="font-size: 1.2rem; font-weight: bold;">
                        ${totalQuantity} وحدة
                    </span>
                </div>
                
                ${order.notes ? `
                <div style="margin-top: 1.5rem;">
                    <h4 style="color: #2c3e50; margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-sticky-note"></i>
                        ملاحظات عامة
                    </h4>
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px;">
                        <p style="margin: 0; color: #555;">
                            ${order.notes || 'لا توجد ملاحظات'}
                        </p>
                    </div>
                </div>
                ` : ''}
            `;
            
            content.innerHTML = detailsHtml;
            modal.style.display = 'flex';
        }
        
        function closeOrderDetails() {
            document.getElementById('orderDetailsModal').style.display = 'none';
        }
        
        document.getElementById('orderDetailsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeOrderDetails();
            }
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeOrderDetails();
            }
        });
    </script>
</body>
</html>