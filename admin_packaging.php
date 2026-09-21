<?php
// admin_packaging.php - Manage product packaging
//session_start();
include 'config.php';

// Check if logged in (add your own auth check)
if (!isset($_SESSION['user_id'])) {
    die("Please login first");
}

$company_id = 11;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_packaging'])) {
        $product_id = $_POST['product_id'];
        $packaging_type = $_POST['packaging_type'];
        $quantity_per_pack = $_POST['quantity_per_pack'] ?: NULL;
        $weight = $_POST['weight'] ?: NULL;
        $weight_unit = $_POST['weight_unit'] ?: NULL;
        $master_pack = $_POST['master_pack'] ?: NULL;
        $price = $_POST['price'] ?: NULL;
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO packaging 
                (product_id, packaging_type, quantity_per_pack, weight, weight_unit, master_pack, price) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$product_id, $packaging_type, $quantity_per_pack, $weight, $weight_unit, $master_pack, $price]);
            echo "<p style='color:green;'>Packaging added successfully!</p>";
        } catch (Exception $e) {
            echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
        }
    }
}

// Fetch all products
$stmt = $pdo->prepare("SELECT id, product_name_ar FROM products WHERE company_id = ? ORDER BY product_name_ar");
$stmt->execute([$company_id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Product Packaging</title>
    <style>
        body { font-family: Arial; padding: 20px; max-width: 800px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #667eea; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
        .container { background: #f9f9f9; padding: 20px; border-radius: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add Product Packaging</h1>
        
        <form method="POST">
            <div class="form-group">
                <label>Product:</label>
                <select name="product_id" required>
                    <option value="">-- Select Product --</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?php echo $product['id']; ?>">
                            <?php echo htmlspecialchars($product['product_name_ar']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Packaging Type:</label>
                <input type="text" name="packaging_type" placeholder="e.g., صندوق, سطل, تنك, مرطبان" required>
            </div>
            
            <div class="form-group">
                <label>Quantity per Pack:</label>
                <input type="number" name="quantity_per_pack" placeholder="e.g., 12">
            </div>
            
            <div class="form-group">
                <label>Weight:</label>
                <input type="number" step="0.01" name="weight" placeholder="e.g., 660">
            </div>
            
            <div class="form-group">
                <label>Weight Unit:</label>
                <select name="weight_unit">
                    <option value="">-- Select Unit --</option>
                    <option value="gr">grams (gr)</option>
                    <option value="kg">kilograms (kg)</option>
                    <option value="ml">milliliters (ml)</option>
                    <option value="lt">liters (lt)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Master Pack:</label>
                <input type="text" name="master_pack" placeholder="e.g., صندوق, سطل">
            </div>
            
            <div class="form-group">
                <label>Price (optional):</label>
                <input type="number" step="0.01" name="price" placeholder="e.g., 10.50">
            </div>
            
            <button type="submit" name="add_packaging">Add Packaging</button>
        </form>
        
        <hr>
        <p><a href="category_products.php?main_category_id=1&company_id=11">Test Category 1</a></p>
        <p><a href="company_profile.php?id=11">Back to Company Profile</a></p>
    </div>
</body>
</html>