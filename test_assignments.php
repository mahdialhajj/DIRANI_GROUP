<?php
// test_assignments.php - View all product-brand assignments
include 'config.php';

$company_id = 11;

// Fetch all assignments
$stmt = $pdo->prepare("
    SELECT 
        p.id as product_id,
        p.product_name_ar,
        cb.id as brand_id,
        cb.brand_name,
        pb.created_at
    FROM products p
    LEFT JOIN product_brands pb ON p.id = pb.product_id
    LEFT JOIN company_brands cb ON pb.brand_id = cb.id
    WHERE p.company_id = ?
    ORDER BY p.product_name_ar, cb.brand_name
");
$stmt->execute([$company_id]);
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group by product
$products_with_brands = [];
foreach ($assignments as $row) {
    $product_id = $row['product_id'];
    if (!isset($products_with_brands[$product_id])) {
        $products_with_brands[$product_id] = [
            'product_name' => $row['product_name_ar'],
            'brands' => []
        ];
    }
    if ($row['brand_id']) {
        $products_with_brands[$product_id]['brands'][] = [
            'brand_name' => $row['brand_name'],
            'created_at' => $row['created_at']
        ];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Product-Brand Assignments</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .product-card { 
            border: 1px solid #ddd; 
            padding: 15px; 
            margin: 10px 0; 
            border-radius: 5px; 
            background: white;
        }
        .brand-tag {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            margin: 2px;
            font-size: 12px;
        }
        .no-brands {
            color: #7f8c8d;
            font-style: italic;
        }
        .stats {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h1>Product-Brand Assignments</h1>
    
    <div class="stats">
        <p><strong>Total Products:</strong> <?php echo count($products_with_brands); ?></p>
        <p><strong>Products with Brands:</strong> 
            <?php 
                $with_brands = array_filter($products_with_brands, function($p) { return !empty($p['brands']); });
                echo count($with_brands);
            ?>
        </p>
    </div>
    
    <?php foreach ($products_with_brands as $product_id => $product): ?>
        <div class="product-card">
            <h3><?php echo htmlspecialchars($product['product_name']); ?> (ID: <?php echo $product_id; ?>)</h3>
            
            <?php if (!empty($product['brands'])): ?>
                <p><strong>Brands:</strong></p>
                <div>
                    <?php foreach ($product['brands'] as $brand): ?>
                        <span class="brand-tag">
                            <?php echo htmlspecialchars($brand['brand_name']); ?>
                            <small>(<?php echo date('Y-m-d', strtotime($brand['created_at'])); ?>)</small>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-brands">No brands assigned yet</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    
    <hr>
    <p><a href="assign_brands.php">← Back to Assign Brands</a></p>
    <p><a href="category_products.php?main_category_id=1&company_id=11">Test Category 1</a></p>
</body>
</html>