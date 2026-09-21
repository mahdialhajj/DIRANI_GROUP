<?php
// my_orders.php
include 'config.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// جلب طلبات المستخدم
try {
    $stmt = $pdo->prepare("
        SELECT so.*, 
               c.name as company_name,
               so.brand_names,
               (SELECT COUNT(*) FROM order_items WHERE order_id = so.id) as item_count
        FROM sales_orders so
        LEFT JOIN companies c ON so.company_id = c.id
        WHERE so.user_id = ?
        ORDER BY so.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // جلب عناصر كل طلب
    foreach ($orders as &$order) {
        $items_stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $items_stmt->execute([$order['id']]);
        $order['items'] = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    unset($order);
    
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلباتي - نظام المبيعات</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .user-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .user-header {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .orders-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .order-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .order-card:hover {
            transform: translateY(-5px);
        }
        
        .order-header {
            padding: 1.5rem;
            background: #f8f9fa;
            border-bottom: 2px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            padding: 1.5rem;
        }
        
        .order-brands {
            background: #e8f4fc;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 1.5rem;
            border-right: 4px solid #3498db;
        }
        
        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #3498db;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            margin-left: 5px;
        }
        
        .order-items {
            padding: 0 1.5rem 1.5rem;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            border-bottom: 1px solid #eee;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .status-pending { background: #f39c12; color: white; }
        .status-confirmed { background: #3498db; color: white; }
        .status-shipped { background: #9b59b6; color: white; }
        .status-delivered { background: #2ecc71; color: white; }
        .status-cancelled { background: #e74c3c; color: white; }
        
        .no-orders {
            text-align: center;
            padding: 3rem;
            color: #7f8c8d;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #3498db;
            text-decoration: none;
            margin-bottom: 1rem;
            font-weight: 500;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            
            .order-info {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="user-container">
        <!-- رابط العودة -->
        <a href="index.php" class="back-link">
            <i class="fas fa-arrow-right"></i>
            العودة للرئيسية
        </a>
        
        <!-- عنوان الصفحة -->
        <div class="user-header">
            <h1 style="margin-bottom: 0.5rem;">
                <i class="fas fa-shopping-cart"></i>
                طلباتي
            </h1>
            <p style="color: rgba(255,255,255,0.9); margin-bottom: 0;">
                عرض جميع طلباتك السابقة
            </p>
        </div>
        
        <!-- قائمة الطلبات -->
        <div class="orders-list">
            <?php if (empty($orders)): ?>
                <div class="no-orders">
                    <i class="fas fa-shopping-cart fa-3x" style="margin-bottom: 1rem; color: #bdc3c7;"></i>
                    <h3 style="color: #2c3e50; margin-bottom: 1rem;">لا توجد طلبات</h3>
                    <p>لم تقم بإجراء أي طلبات حتى الآن</p>
                    <a href="index.php" style="display: inline-block; margin-top: 1rem; padding: 0.75rem 1.5rem; background: #3498db; color: white; text-decoration: none; border-radius: 5px;">
                        <i class="fas fa-plus"></i> إنشاء طلب جديد
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <h3 style="margin: 0; color: #2c3e50;">
                                <i class="fas fa-file-invoice"></i>
                                طلب #<?php echo $order['id']; ?>
                            </h3>
                            <p style="color: #7f8c8d; margin: 0.25rem 0 0 0;">
                                <i class="fas fa-building"></i>
                                <?php echo htmlspecialchars($order['company_name']); ?>
                            </p>
                        </div>
                        <div>
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
                            <p style="color: #7f8c8d; margin: 0.5rem 0 0 0; text-align: center;">
                                <i class="fas fa-calendar"></i>
                                <?php echo date('Y/m/d', strtotime($order['order_date'])); ?>
                            </p>
                        </div>
                    </div>
                    
                    <?php if (!empty($order['brand_names'])): ?>
                    <div class="order-brands">
                        <p style="margin: 0; color: #2c3e50; font-weight: 500;">
                            <i class="fas fa-tags"></i>
                            العلامات التجارية:
                        </p>
                        <div style="margin-top: 0.5rem;">
                            <?php 
                            $brands = explode(', ', $order['brand_names']);
                            foreach ($brands as $brand): 
                            ?>
                                <span class="brand-badge">
                                    <i class="fas fa-tag"></i>
                                    <?php echo htmlspecialchars($brand); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="order-info">
                        <div>
                            <p style="margin: 0; color: #7f8c8d; font-size: 0.9rem;">رقم الطلب</p>
                            <p style="margin: 0.25rem 0 0 0; color: #2c3e50; font-weight: 500;">#<?php echo $order['id']; ?></p>
                        </div>
                        <div>
                            <p style="margin: 0; color: #7f8c8d; font-size: 0.9rem;">عدد المنتجات</p>
                            <p style="margin: 0.25rem 0 0 0; color: #2c3e50; font-weight: 500;"><?php echo $order['item_count']; ?> منتج</p>
                        </div>
                        <div>
                            <p style="margin: 0; color: #7f8c8d; font-size: 0.9rem;">تاريخ الطلب</p>
                            <p style="margin: 0.25rem 0 0 0; color: #2c3e50; font-weight: 500;">
                                <?php echo date('Y/m/d', strtotime($order['order_date'])); ?>
                            </p>
                        </div>
                        <div>
                            <p style="margin: 0; color: #7f8c8d; font-size: 0.9rem;">آخر تحديث</p>
                            <p style="margin: 0.25rem 0 0 0; color: #2c3e50; font-weight: 500;">
                                <?php echo date('Y/m/d H:i', strtotime($order['created_at'])); ?>
                            </p>
                        </div>
                    </div>
                    
                    <?php if (!empty($order['items'])): ?>
                    <div class="order-items">
                        <h4 style="color: #2c3e50; margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-box-open"></i>
                            منتجات الطلب
                        </h4>
                        
                        <?php foreach ($order['items'] as $item): ?>
                        <div class="order-item">
                            <div style="flex: 1;">
                                <p style="margin: 0; color: #2c3e50; font-weight: 500;">
                                    <?php echo htmlspecialchars($item['product_description']); ?>
                                </p>
                                <?php if (!empty($item['notes'])): ?>
                                    <p style="margin: 0.25rem 0 0 0; color: #7f8c8d; font-size: 0.9rem;">
                                        <i class="fas fa-sticky-note"></i>
                                        <?php echo htmlspecialchars($item['notes']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div style="font-weight: bold; color: #3498db;">
                                <?php echo $item['quantity']; ?> وحدة
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>