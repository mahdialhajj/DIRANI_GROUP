<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    exit('غير مصرح');
}

$product_id = $_GET['product_id'] ?? 0;

if (!$product_id) {
    exit('معرف المنتج غير صالح');
}

try {
    $stmt = $pdo->prepare("SELECT * FROM packaging WHERE product_id = ? ORDER BY id ASC");
    $stmt->execute([$product_id]);
    $packaging_options = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($packaging_options)) {
        echo '<p style="color: #7f8c8d; text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                <i class="fas fa-info-circle"></i>
                لا توجد خيارات تعبئة متاحة لهذا المنتج
              </p>';
    } else {
        echo '<input type="hidden" name="packaging_id" id="packaging_id" value="">';
        echo '<p style="color: #7f8c8d; margin-bottom: 1rem;">
                <i class="fas fa-info-circle"></i>
                اختر نوع التعبئة والوزن المناسب
              </p>';
        echo '<div class="product-options-grid">';
        
        foreach ($packaging_options as $packaging) {
            echo '<div class="product-option" 
                      onclick="selectPackaging(' . $packaging['id'] . ', this)"
                      data-weight="' . $packaging['weight'] . '"
                      data-weight-unit="' . htmlspecialchars($packaging['weight_unit'] ?? '') . '"
                      data-packaging-type="' . htmlspecialchars($packaging['packaging_type']) . '"
                      data-quantity-per-pack="' . $packaging['quantity_per_pack'] . '">';
            
            echo '<div class="packaging-option-header">
                    <div class="option-title">
                        ' . htmlspecialchars($packaging['packaging_type']) . '
                    </div>
                  </div>';
            
            echo '<div class="option-details">';
            
            if ($packaging['weight']) {
                echo '<div class="option-detail">
                        <span>الوزن:</span>
                        <span>' . $packaging['weight'] . ' ' . htmlspecialchars($packaging['weight_unit'] ?? '') . '</span>
                      </div>';
            }
            
            if ($packaging['quantity_per_pack']) {
                echo '<div class="option-detail">
                        <span>الكمية/العبوة:</span>
                        <span>' . $packaging['quantity_per_pack'] . ' قطعة</span>
                      </div>';
            }
            
            if ($packaging['master_pack']) {
                echo '<div class="option-detail">
                        <span>التعبئة الرئيسية:</span>
                        <span>' . htmlspecialchars($packaging['master_pack']) . '</span>
                      </div>';
            }
            
            echo '</div></div>';
        }
        echo '</div>';
    }
} catch(PDOException $e) {
    error_log("Packaging Error: " . $e->getMessage());
    echo '<p style="color: #e74c3c;">حدث خطأ في جلب خيارات التعبئة</p>';
}
?>