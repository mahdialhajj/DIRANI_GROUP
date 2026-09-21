<?php
$company_id = $_GET['id'] ?? null;
$company_name = '';
if ($company_id) {
    include 'config.php';
    try {
        $stmt = $pdo->prepare("SELECT name FROM companies WHERE id = ?");
        $stmt->execute([$company_id]);
        $company = $stmt->fetch();
        $company_name = $company ? $company['name'] : '';
    } catch(PDOException $e) {
        // Handle error silently
    }
}
// التحقق من دور المستخدم لعرض رابط إدارة الطلبات
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT ur.role_name 
            FROM users u
            JOIN user_roles ur ON u.role_id = ur.id
            WHERE u.id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $user_role = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user_role && in_array($user_role['role_name'], ['sales_manager', 'admin'])) {
            echo '<a href="admin_orders.php" class="btn-primary" style="margin-left: 10px;">
                    <i class="fas fa-clipboard-list"></i>
                    إدارة الطلبات
                  </a>';
        }
    } catch(PDOException $e) {
        // تجاهل الخطأ
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $company_name ? htmlspecialchars($company_name) : 'Company Profile'; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header class="main-header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <a href="index.php">CompanyDirectory</a>
                </div>
                <div class="nav-menu">
                    <a href="index.php">Home</a>
                    <?php if ($company_id): ?>
                        <a href="#story">Our Story</a>
                        <a href="#team">Our Team</a>
                        <a href="#brands">Brands</a>
                        <a href="#feedback">Feedback</a>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="sales_order.php?company_id=<?php echo $company_id; ?>" class="btn-primary">Sales Order</a>
                        <a href="logout.php" class="btn-secondary">Logout</a>
                    <?php else: ?>
                        <a href="login.php?company_id=<?php echo $company_id; ?>" class="btn-primary">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>
    <main>