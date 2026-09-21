<?php
// assign_brands.php - Simple tool to assign multiple brands to products
//session_start();
include 'config.php';

$company_id = 11; // Your company ID

// Pagination settings
$limit = 100; // Products per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_brands'])) {
    $product_id = $_POST['product_id'];
    $selected_brands = $_POST['brands'] ?? [];
    
    try {
        // Delete existing brand assignments for this product
        $stmt = $pdo->prepare("DELETE FROM product_brands WHERE product_id = ?");
        $stmt->execute([$product_id]);
        
        // Insert new brand assignments
        foreach ($selected_brands as $brand_id) {
            $stmt = $pdo->prepare("INSERT INTO product_brands (product_id, brand_id) VALUES (?, ?)");
            $stmt->execute([$product_id, $brand_id]);
        }
        
        echo "<div style='background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px;'>
                ✅ Brands assigned successfully to Product ID: $product_id
              </div>";
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px;'>
                ❌ Error: " . htmlspecialchars($e->getMessage()) . "
              </div>";
    }
}

// Get total number of products for pagination
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM products WHERE company_id = ?");
$stmt->execute([$company_id]);
$totalProducts = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalProducts / $limit);

// Fetch products with pagination - USING POSITIONAL PARAMETERS
$stmt = $pdo->prepare("SELECT id, product_name_ar FROM products WHERE company_id = ? ORDER BY product_name_ar LIMIT ? OFFSET ?");
$stmt->bindValue(1, $company_id, PDO::PARAM_INT);
$stmt->bindValue(2, $limit, PDO::PARAM_INT);
$stmt->bindValue(3, $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all brands
$stmt = $pdo->prepare("SELECT id, brand_name FROM company_brands WHERE company_id = ? ORDER BY brand_name");
$stmt->execute([$company_id]);
$brands = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to get currently assigned brands for a product
function getAssignedBrands($pdo, $product_id) {
    try {
        $stmt = $pdo->prepare("SELECT brand_id FROM product_brands WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $result ?: [];
    } catch (Exception $e) {
        return [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Brands to Products</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        .form-section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #2c3e50;
        }
        select, input {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .brands-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
            max-height: 400px;
            overflow-y: auto;
            padding: 10px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .brand-checkbox {
            padding: 10px;
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .brand-checkbox:hover {
            background: #e9ecef;
            border-color: #667eea;
        }
        .brand-checkbox.selected {
            background: #d4edda;
            border-color: #28a745;
        }
        .brand-checkbox input[type="checkbox"] {
            margin-right: 10px;
        }
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .quick-links {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        .quick-links a {
            display: inline-block;
            margin-right: 15px;
            color: #667eea;
            text-decoration: none;
            padding: 8px 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .quick-links a:hover {
            background: #e9ecef;
        }
        .stats {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .stats p {
            margin: 5px 0;
        }
        .pagination {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }
        .pagination a, .pagination span {
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #ddd;
            border-radius: 5px;
            color: #667eea;
            background: white;
        }
        .pagination a:hover {
            background: #f8f9fa;
        }
        .pagination .current {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        .pagination .disabled {
            color: #ccc;
            pointer-events: none;
        }
        .search-box {
            margin-bottom: 20px;
        }
        .search-box input {
            width: 300px;
            display: inline-block;
            margin-right: 10px;
        }
        .search-box button {
            padding: 10px 20px;
            margin-top: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-tags"></i> Assign Brands to Products</h1>
        
        <div class="stats">
            <p><strong>Total Products:</strong> <?php echo $totalProducts; ?></p>
            <p><strong>Showing:</strong> <?php echo min(($offset + 1), $totalProducts); ?> - <?php echo min(($offset + count($products)), $totalProducts); ?> of <?php echo $totalProducts; ?></p>
            <p><strong>Total Brands:</strong> <?php echo count($brands); ?></p>
            <p><strong>Current Page:</strong> <?php echo $page; ?> of <?php echo $totalPages; ?></p>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=1"><i class="fas fa-angle-double-left"></i></a>
                <a href="?page=<?php echo $page - 1; ?>"><i class="fas fa-angle-left"></i></a>
            <?php else: ?>
                <span class="disabled"><i class="fas fa-angle-double-left"></i></span>
                <span class="disabled"><i class="fas fa-angle-left"></i></span>
            <?php endif; ?>
            
            <?php
            // Show page numbers
            $startPage = max(1, $page - 2);
            $endPage = min($totalPages, $page + 2);
            
            for ($i = $startPage; $i <= $endPage; $i++):
            ?>
                <a href="?page=<?php echo $i; ?>" <?php echo ($i == $page) ? 'class="current"' : ''; ?>>
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>"><i class="fas fa-angle-right"></i></a>
                <a href="?page=<?php echo $totalPages; ?>"><i class="fas fa-angle-double-right"></i></a>
            <?php else: ?>
                <span class="disabled"><i class="fas fa-angle-right"></i></span>
                <span class="disabled"><i class="fas fa-angle-double-right"></i></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-section">
                <label for="product_id"><i class="fas fa-box"></i> Select Product:</label>
                <select name="product_id" id="product_id" required onchange="loadAssignedBrands(this.value)">
                    <option value="">-- Choose a Product --</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?php echo $product['id']; ?>">
                            <?php echo htmlspecialchars($product['product_name_ar']); ?> (ID: <?php echo $product['id']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <p style="color: #666; font-size: 14px; margin-top: 5px;">
                    <i class="fas fa-info-circle"></i> Showing products <?php echo $offset + 1; ?>-<?php echo $offset + count($products); ?> 
                    (Page <?php echo $page; ?> of <?php echo $totalPages; ?>)
                </p>
            </div>
            
            <div class="form-section">
                <label><i class="fas fa-crown"></i> Select Brands (Multiple Selection):</label>
                <p style="color: #666; margin-bottom: 10px;">Hold Ctrl/Cmd to select multiple brands</p>
                
                <div class="brands-grid" id="brandsContainer">
                    <?php foreach ($brands as $brand): ?>
                        <label class="brand-checkbox">
                            <input type="checkbox" name="brands[]" value="<?php echo $brand['id']; ?>"
                                   id="brand_<?php echo $brand['id']; ?>">
                            <?php echo htmlspecialchars($brand['brand_name']); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <button type="submit" name="assign_brands">
                <i class="fas fa-save"></i> Assign Selected Brands
            </button>
        </form>
        
        <!-- Pagination (bottom) -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination" style="margin-top: 30px;">
            <?php if ($page > 1): ?>
                <a href="?page=1"><i class="fas fa-angle-double-left"></i></a>
                <a href="?page=<?php echo $page - 1; ?>"><i class="fas fa-angle-left"></i></a>
            <?php else: ?>
                <span class="disabled"><i class="fas fa-angle-double-left"></i></span>
                <span class="disabled"><i class="fas fa-angle-left"></i></span>
            <?php endif; ?>
            
            <?php
            // Show page numbers
            $startPage = max(1, $page - 2);
            $endPage = min($totalPages, $page + 2);
            
            for ($i = $startPage; $i <= $endPage; $i++):
            ?>
                <a href="?page=<?php echo $i; ?>" <?php echo ($i == $page) ? 'class="current"' : ''; ?>>
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>"><i class="fas fa-angle-right"></i></a>
                <a href="?page=<?php echo $totalPages; ?>"><i class="fas fa-angle-double-right"></i></a>
            <?php else: ?>
                <span class="disabled"><i class="fas fa-angle-right"></i></span>
                <span class="disabled"><i class="fas fa-angle-double-right"></i></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <div class="quick-links">
            <h3><i class="fas fa-rocket"></i> Quick Actions:</h3>
            <a href="category_products.php?main_category_id=1&company_id=11" target="_blank">
                <i class="fas fa-eye"></i> Test Category 1
            </a>
            <a href="company_profile.php?id=11" target="_blank">
                <i class="fas fa-building"></i> Company Profile
            </a>
            <a href="test_assignments.php" target="_blank">
                <i class="fas fa-check-circle"></i> Check Assignments
            </a>
        </div>
    </div>

    <script>
        // Function to load already assigned brands when a product is selected
        function loadAssignedBrands(productId) {
            if (!productId) return;
            
            // Clear all checkboxes first
            document.querySelectorAll('input[name="brands[]"]').forEach(checkbox => {
                checkbox.checked = false;
                checkbox.parentElement.classList.remove('selected');
            });
            
            // Fetch assigned brands for this product
            fetch('get_assigned_brands.php?product_id=' + productId)
                .then(response => response.json())
                .then(data => {
                    data.forEach(brandId => {
                        const checkbox = document.getElementById('brand_' + brandId);
                        if (checkbox) {
                            checkbox.checked = true;
                            checkbox.parentElement.classList.add('selected');
                        }
                    });
                })
                .catch(error => console.error('Error:', error));
        }
        
        // Add click effect to brand checkboxes
        document.querySelectorAll('.brand-checkbox').forEach(item => {
            item.addEventListener('click', function(e) {
                if (e.target.type !== 'checkbox') {
                    const checkbox = this.querySelector('input[type="checkbox"]');
                    checkbox.checked = !checkbox.checked;
                }
                this.classList.toggle('selected', this.querySelector('input[type="checkbox"]').checked);
            });
        });
    </script>
</body>
</html>