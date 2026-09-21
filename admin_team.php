<?php
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$company_id = $_GET['company_id'] ?? null;

if (!$company_id) {
    header("Location: index.php");
    exit;
}

// Handle team member addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_team_member'])) {
    $name = $_POST['name'];
    $position = $_POST['position'];
    $bio = $_POST['bio'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO company_team (company_id, name, position, bio, email, phone) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$company_id, $name, $position, $bio, $email, $phone]);
        $success = "Team member added successfully!";
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle team member deletion
if (isset($_GET['delete_member'])) {
    $member_id = $_GET['delete_member'];
    try {
        $stmt = $pdo->prepare("DELETE FROM company_team WHERE id = ? AND company_id = ?");
        $stmt->execute([$member_id, $company_id]);
        $success = "Team member deleted successfully!";
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch existing team members
try {
    $stmt = $pdo->prepare("SELECT * FROM company_team WHERE company_id = ? ORDER BY position ASC");
    $stmt->execute([$company_id]);
    $team_members = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $team_members = [];
}

// Get company name
try {
    $stmt = $pdo->prepare("SELECT name FROM companies WHERE id = ?");
    $stmt->execute([$company_id]);
    $company = $stmt->fetch();
} catch(PDOException $e) {
    $company = ['name' => 'Unknown Company'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Team - <?php echo htmlspecialchars($company['name']); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Manage Team - <?php echo htmlspecialchars($company['name']); ?></h1>
        
        <a href="company_profile.php?id=<?php echo $company_id; ?>" class="btn">← Back to Company</a>
        
        <?php if (isset($success)): ?>
            <div class="alert success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Add Team Member Form -->
        <div class="admin-form">
            <h2>Add Team Member</h2>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="position">Position:</label>
                        <input type="text" id="position" name="position" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="bio">Bio:</label>
                    <textarea id="bio" name="bio" rows="4"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone:</label>
                        <input type="text" id="phone" name="phone">
                    </div>
                </div>
                
                <button type="submit" name="add_team_member" class="btn-primary">Add Team Member</button>
            </form>
        </div>

        <!-- Existing Team Members -->
        <div class="admin-list">
            <h2>Existing Team Members</h2>
            
            <?php if (empty($team_members)): ?>
                <p>No team members found. Add your first team member above.</p>
            <?php else: ?>
                <div class="team-list">
                    <?php foreach ($team_members as $member): ?>
                    <div class="team-item">
                        <div class="team-info">
                            <h3><?php echo htmlspecialchars($member['name']); ?></h3>
                            <p><strong>Position:</strong> <?php echo htmlspecialchars($member['position']); ?></p>
                            <?php if (!empty($member['email'])): ?>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($member['email']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($member['phone'])): ?>
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($member['phone']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($member['bio'])): ?>
                                <p><strong>Bio:</strong> <?php echo htmlspecialchars($member['bio']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="team-actions">
                            <a href="admin_team.php?company_id=<?php echo $company_id; ?>&delete_member=<?php echo $member['id']; ?>" 
                               class="btn-secondary" 
                               onclick="return confirm('Are you sure you want to delete this team member?')">Delete</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>