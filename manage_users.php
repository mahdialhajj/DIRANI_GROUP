<?php
// manage_users.php
include 'config.php';

// التحقق من تسجيل الدخول والصلاحيات (فقط admin)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
try {
    $stmt = $pdo->prepare("SELECT u.*, ur.role_name FROM users u JOIN user_roles ur ON u.role_id = ur.id WHERE u.id = ?");
    $stmt->execute([$user_id]);
    $current_user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$current_user || $current_user['role_name'] != 'admin') {
        header("Location: index.php");
        exit;
    }
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// معالجة إضافة/تعديل المستخدم
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_user'])) {
        $username = $_POST['username'];
        $password = md5($_POST['password']);
        $email = $_POST['email'];
        $role_id = $_POST['role_id'];
        $customer_id = $_POST['customer_id'] ?: null;
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role_id, customer_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$username, $password, $email, $role_id, $customer_id]);
            $success = "تم إضافة المستخدم بنجاح";
        } catch(PDOException $e) {
            $error = "خطأ في إضافة المستخدم: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['update_user'])) {
        $user_id = $_POST['user_id'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $role_id = $_POST['role_id'];
        $customer_id = $_POST['customer_id'] ?: null;
        $password = !empty($_POST['password']) ? md5($_POST['password']) : null;
        
        try {
            if ($password) {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, role_id = ?, customer_id = ?, password = ? WHERE id = ?");
                $stmt->execute([$username, $email, $role_id, $customer_id, $password, $user_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, role_id = ?, customer_id = ? WHERE id = ?");
                $stmt->execute([$username, $email, $role_id, $customer_id, $user_id]);
            }
            $success = "تم تحديث بيانات المستخدم بنجاح";
        } catch(PDOException $e) {
            $error = "خطأ في تحديث المستخدم: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND id != ?");
            $stmt->execute([$user_id, $_SESSION['user_id']]);
            $success = "تم حذف المستخدم بنجاح";
        } catch(PDOException $e) {
            $error = "خطأ في حذف المستخدم: " . $e->getMessage();
        }
    }
}

// جلب جميع المستخدمين
try {
    $users_stmt = $pdo->prepare("
        SELECT u.*, ur.role_name, 
               cu.username as customer_name
        FROM users u
        JOIN user_roles ur ON u.role_id = ur.id
        LEFT JOIN users cu ON u.customer_id = cu.id
        ORDER BY u.created_at DESC
    ");
    $users_stmt->execute();
    $users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // جلب جميع الأدوار
    $roles_stmt = $pdo->prepare("SELECT * FROM user_roles ORDER BY role_name");
    $roles_stmt->execute();
    $roles = $roles_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // جلب جميع العملاء (المستخدمين من نوع customer)
    $customers_stmt = $pdo->prepare("
        SELECT u.* FROM users u
        JOIN user_roles ur ON u.role_id = ur.id
        WHERE ur.role_name = 'customer'
        ORDER BY u.username
    ");
    $customers_stmt->execute();
    $customers = $customers_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المستخدمين</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .form-section, .users-section {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-weight: 600;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-success {
            background: #2ecc71;
            color: white;
        }
        
        .btn-warning {
            background: #f39c12;
            color: white;
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .users-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .users-table th {
            background: #3498db;
            color: white;
            padding: 1rem;
            text-align: right;
            font-weight: 600;
        }
        
        .users-table td {
            padding: 1rem;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .users-table tr:hover {
            background: #f8f9fa;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">
            <i class="fas fa-arrow-right"></i> العودة للرئيسية
        </a>
        
        <div class="header">
            <h1><i class="fas fa-users"></i> إدارة المستخدمين</h1>
            <p>المسؤول: <?php echo htmlspecialchars($current_user['username']); ?></p>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <!-- قسم إضافة/تعديل مستخدم -->
        <div class="form-section">
            <h3><i class="fas fa-user-plus"></i> إضافة مستخدم جديد</h3>
            <form method="POST" action="">
                <div class="form-grid">
                    <div class="form-group">
                        <label>اسم المستخدم</label>
                        <input type="text" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label>البريد الإلكتروني</label>
                        <input type="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label>كلمة المرور</label>
                        <input type="password" name="password" required>
                    </div>
                    
                    <div class="form-group">
                        <label>الدور</label>
                        <select name="role_id" id="roleSelect" onchange="toggleCustomerField()" required>
                            <option value="">اختر الدور</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo $role['id']; ?>">
                                    <?php echo htmlspecialchars($role['role_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group" id="customerField" style="display: none;">
                        <label>العميل المرتبط</label>
                        <select name="customer_id">
                            <option value="">اختر العميل</option>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?php echo $customer['id']; ?>">
                                    <?php echo htmlspecialchars($customer['username']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small style="color: #7f8c8d;">(مطلوب فقط لمديري العملاء)</small>
                    </div>
                </div>
                
                <button type="submit" name="add_user" class="btn btn-primary">
                    <i class="fas fa-save"></i> إضافة مستخدم
                </button>
            </form>
        </div>
        
        <!-- قسم عرض المستخدمين -->
        <div class="users-section">
            <h3><i class="fas fa-list"></i> قائمة المستخدمين</h3>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>اسم المستخدم</th>
                        <th>البريد الإلكتروني</th>
                        <th>الدور</th>
                        <th>العميل المرتبط</th>
                        <th>تاريخ الإنشاء</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <?php 
                            $role_names = [
                                'admin' => 'مسؤول',
                                'sales_manager' => 'مدير مبيعات',
                                'customer' => 'عميل',
                                'customer_manager' => 'مدير عميل'
                            ];
                            echo $role_names[$user['role_name']] ?? $user['role_name'];
                            ?>
                        </td>
                        <td>
                            <?php echo $user['customer_name'] ? htmlspecialchars($user['customer_name']) : '---'; ?>
                        </td>
                        <td><?php echo date('Y/m/d', strtotime($user['created_at'])); ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm" onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                <i class="fas fa-edit"></i> تعديل
                            </button>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذا المستخدم؟');">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" name="delete_user" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i> حذف
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- نافذة تعديل المستخدم -->
    <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; padding: 20px;">
        <div style="background: white; width: 90%; max-width: 600px; border-radius: 15px; padding: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #e0e0e0;">
                <h3 style="color: #2c3e50; margin: 0;"><i class="fas fa-user-edit"></i> تعديل المستخدم</h3>
                <button onclick="closeEditModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #7f8c8d;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="editForm" method="POST" action="">
                <input type="hidden" name="user_id" id="editUserId">
                <div class="form-grid">
                    <div class="form-group">
                        <label>اسم المستخدم</label>
                        <input type="text" name="username" id="editUsername" required>
                    </div>
                    
                    <div class="form-group">
                        <label>البريد الإلكتروني</label>
                        <input type="email" name="email" id="editEmail" required>
                    </div>
                    
                    <div class="form-group">
                        <label>كلمة المرور (اترك فارغاً للحفاظ على القديمة)</label>
                        <input type="password" name="password" id="editPassword">
                    </div>
                    
                    <div class="form-group">
                        <label>الدور</label>
                        <select name="role_id" id="editRoleId" onchange="toggleCustomerFieldEdit()" required>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo $role['id']; ?>"><?php echo htmlspecialchars($role['role_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group" id="customerFieldEdit">
                        <label>العميل المرتبط</label>
                        <select name="customer_id" id="editCustomerId">
                            <option value="">اختر العميل</option>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?php echo $customer['id']; ?>">
                                    <?php echo htmlspecialchars($customer['username']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div style="margin-top: 1.5rem; display: flex; gap: 10px;">
                    <button type="submit" name="update_user" class="btn btn-success">
                        <i class="fas fa-save"></i> حفظ التعديلات
                    </button>
                    <button type="button" onclick="closeEditModal()" class="btn btn-danger">
                        <i class="fas fa-times"></i> إلغاء
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function toggleCustomerField() {
            var roleSelect = document.getElementById('roleSelect');
            var customerField = document.getElementById('customerField');
            
            if (roleSelect.value) {
                var roleText = roleSelect.options[roleSelect.selectedIndex].text;
                if (roleText.includes('customer_manager') || roleText.includes('مدير عميل')) {
                    customerField.style.display = 'block';
                } else {
                    customerField.style.display = 'none';
                }
            }
        }
        
        function toggleCustomerFieldEdit() {
            var roleSelect = document.getElementById('editRoleId');
            var customerField = document.getElementById('customerFieldEdit');
            
            if (roleSelect.value) {
                var roleText = roleSelect.options[roleSelect.selectedIndex].text;
                if (roleText.includes('customer_manager') || roleText.includes('مدير عميل')) {
                    customerField.style.display = 'block';
                } else {
                    customerField.style.display = 'none';
                }
            }
        }
        
        function editUser(user) {
            document.getElementById('editModal').style.display = 'flex';
            document.getElementById('editUserId').value = user.id;
            document.getElementById('editUsername').value = user.username;
            document.getElementById('editEmail').value = user.email;
            document.getElementById('editRoleId').value = user.role_id;
            document.getElementById('editCustomerId').value = user.customer_id || '';
            
            toggleCustomerFieldEdit();
        }
        
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        // إغلاق النافذة عند النقر خارجها
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
    </script>
</body>
</html>