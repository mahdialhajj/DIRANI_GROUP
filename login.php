<?php
// login.php

include 'config.php';

// إذا كان المستخدم مسجل دخول بالفعل، توجيهه حسب الدور
if (isset($_SESSION['user_id'])) {
    redirectBasedOnRole($_SESSION['role'] ?? 'user');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'يرجى إدخال اسم المستخدم وكلمة المرور';
    } else {
        try {
            // البحث عن المستخدم
            $stmt = $pdo->prepare("
                SELECT u.*, ur.role_name 
                FROM users u
                LEFT JOIN user_roles ur ON u.role_id = ur.id
                WHERE u.username = ? OR u.email = ?
            ");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // التحقق من كلمة المرور
                if (password_verify($password, $user['password'])) {
                    // حفظ بيانات المستخدم في الجلسة
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role_name'] ?? 'user';
                    
                    // تسجيل وقت الدخول
                    $_SESSION['login_time'] = time();
                    
                    $success = "تم تسجيل الدخول بنجاح! جاري التوجيه...";
                    
                    // توجيه المستخدم حسب الدور
                    redirectBasedOnRole($_SESSION['role']);
                    exit;
                    
                } else {
                    $error = 'كلمة المرور غير صحيحة';
                }
            } else {
                $error = 'اسم المستخدم غير موجود';
            }
        } catch(PDOException $e) {
            $error = 'حدث خطأ في النظام: ' . $e->getMessage();
        }
    }
}

// دالة توجيه المستخدم حسب الدور
function redirectBasedOnRole($role) {
    switch($role) {
        case 'admin':
        case 'sales_manager':
            // إذا كان هناك رابط redirect في الرابط
            if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
                header("Location: " . $_GET['redirect']);
            } else {
                header("Location: admin_orders.php");
            }
            break;
            
        case 'company_admin':
            // توجيه إلى صفحة إدارة الشركة
            if (isset($_GET['company_id']) && !empty($_GET['company_id'])) {
                header("Location: admin_company.php?company_id=" . $_GET['company_id']);
            } else {
                header("Location: index.php");
            }
            break;
            
        default: // user
            // توجيه حسب رابط redirect أو إلى الصفحة الرئيسية
            if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
                header("Location: " . $_GET['redirect']);
            } elseif (isset($_GET['company_id']) && !empty($_GET['company_id'])) {
                header("Location: sales_order.php?company_id=" . $_GET['company_id']);
            } else {
                header("Location: index.php");
            }
            break;
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول</title>
    <link rel="stylesheet" href="CSS/login.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
 
    </style>
</head>
<body>
    <div class="login-page">
        <div class="login-container">
            <div class="login-header">
                <h1><i class="fas fa-sign-in-alt"></i> تسجيل الدخول</h1>
                <p>أدخل بيانات حسابك للوصول إلى النظام</p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i>
                        اسم المستخدم أو البريد الإلكتروني
                    </label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="text" id="username" name="username" 
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                               required placeholder="أدخل اسم المستخدم أو البريد الإلكتروني">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        كلمة المرور
                    </label>
                    <div class="input-with-icon">
                        <i class="fas fa-key"></i>
                        <input type="password" id="password" name="password" 
                               required placeholder="أدخل كلمة المرور">
                    </div>
                </div>
                
                <?php if (isset($_GET['company_id']) && !empty($_GET['company_id'])): ?>
                    <input type="hidden" name="company_id" value="<?php echo $_GET['company_id']; ?>">
                    <div class="role-info">
                        <i class="fas fa-info-circle"></i>
                        تسجيل الدخول للوصول إلى طلبات الشركة
                    </div>
                <?php endif; ?>
                
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i>
                    تسجيل الدخول
                </button>
            </form>
            
            <div class="login-footer">
               
                    <a href="index.php">العودة إلى الصفحة الرئيسية</a>
                </p>
            </div>
        </div>
    </div>
    
    <script>
        // عرض/إخفاء كلمة المرور
        document.getElementById('password').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                document.querySelector('form').submit();
            }
        });
        
        // التحقق من النموذج
        document.querySelector('form').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (!username || !password) {
                e.preventDefault();
                alert('يرجى ملء جميع الحقول');
                return false;
            }
            
            return true;
        });
        
        // إذا كان هناك رسالة نجاح، توجيه بعد 2 ثانية
        <?php if (!empty($success)): ?>
        setTimeout(function() {
            <?php 
            if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
                echo "window.location.href = '" . $_GET['redirect'] . "';";
            } elseif (isset($_GET['company_id']) && !empty($_GET['company_id'])) {
                echo "window.location.href = 'sales_order.php?company_id=" . $_GET['company_id'] . "';";
            } else {
                echo "window.location.href = 'index.php';";
            }
            ?>
        }, 2000);
        <?php endif; ?>
    </script>
</body>
</html>