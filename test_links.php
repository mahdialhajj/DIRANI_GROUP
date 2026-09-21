<?php
// test_links.php - Test your category links
echo "<h1>Test Category Links</h1>";

// First, let's see what categories exist
include 'config.php';

try {
    // Get all main categories for company ID 11
    $stmt = $pdo->prepare("
        SELECT DISTINCT mc.*
        FROM main_categories mc
        LEFT JOIN sub_categories sc ON mc.id = sc.main_category_id
        LEFT JOIN products p ON sc.id = p.sub_category_id
        WHERE p.company_id = 11
        GROUP BY mc.id
        HAVING COUNT(p.id) > 0
    ");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Available Categories for Company ID 11:</h2>";
    echo "<ul>";
    foreach ($categories as $category) {
        echo "<li>";
        echo "<strong>" . htmlspecialchars($category['name_ar']) . "</strong> (ID: " . $category['id'] . ")";
        echo " - <a href='category_products.php?main_category_id=" . $category['id'] . "&company_id=11' target='_blank'>Test Link</a>";
        echo "</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>