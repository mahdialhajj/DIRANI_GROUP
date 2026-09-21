<?php
// admin_assign_brands.php
//session_start();
include 'config.php';

// Check if logged in (add your own auth check)
if (!isset($_SESSION['user_id'])) {
    die("Please login first");
}

$company_id = 11; // Your company ID

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign'])) {
    $product_id = $_POST['product_id'];
    $brand_ids = $_POST['brands'] ?? [];
    
    try {
        // First, remove existing brands for this product
        $stmt = $pdo->prepare("DELETE FROM product_brands WHERE product_id = ?");
        $stmt->execute([$product_id]);
        
        // Add new brand assignments
        foreach ($brand_ids as $brand_id) {
            $stmt = $pdo->prepare("INSERT INTO product_brands (product_id, brand_id) VALUES (?, ?)");
            $stmt->execute([$product_id, $brand_id]);
        }
        
        echo "<p style='color:green;'>Brands assigned successfully!</p>";
    } catch (Exception $e) {
        echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
    }
}

// Fetch all products
$stmt = $pdo->prepare("SELECT * FROM products WHERE company_id = ? ORDER BY product_name_ar");
$stmt->execute([$company_id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all brands
$stmt = $pdo->prepare("SELECT * FROM company_brands WHERE company_id = ? ORDER BY brand_name");
$stmt->execute([$company_id]);
$brands = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to get assigned brands for a product
function getAssignedBrands($pdo, $product_id) {
    $stmt = $pdo->prepare("SELECT brand_id FROM product_brands WHERE product_id = ?");
    $stmt->execute([$product_id]);
    return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'brand_id');
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Assign Brands to Products</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        select, input, button { padding: 5px; margin: 5px; }
        .brand-checkbox { margin: 5px; }
    </style>
</head>
<body>
    <h1>Assign Brands to Products</h1>
    
    <form method="POST">
        <div>
            <label>Select Product:</label>
            <select name="product_id" required>
                <option value="">-- Select Product --</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?php echo $product['id']; ?>">
                        <?php echo htmlspecialchars($product['product_name_ar']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div>
            <label>Select Brands:</label><br>
            <?php foreach ($brands as $brand): ?>
                <div class="brand-checkbox">
                    <input type="checkbox" name="brands[]" value="<?php echo $brand['id']; ?>" id="brand_<?php echo $brand['id']; ?>">
                    <label for="brand_<?php echo $brand['id']; ?>">
                        <?php echo htmlspecialchars($brand['brand_name']); ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
        
        <button type="submit" name="assign">Assign Brands</button>
    </form>
    
    <hr>
    <h2>Quick Test Links:</h2>
    <ul>
        <li><a href="category_products.php?main_category_id=1&company_id=11">Test Category Products</a></li>
        <li><a href="company_profile.php?id=11">Back to Company Profile</a></li>
    </ul>
</body>
</html>