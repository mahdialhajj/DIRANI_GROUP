<?php
// get_assigned_brands.php - Return JSON of assigned brands for a product
include 'config.php';

$product_id = $_GET['product_id'] ?? null;

if (!$product_id) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT brand_id FROM product_brands WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $brands = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    header('Content-Type: application/json');
    echo json_encode($brands);
} catch (Exception $e) {
    echo json_encode([]);
}
?>