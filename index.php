<?php
include 'config.php';

try {
    $stmt = $pdo->query("SELECT * FROM companies ORDER BY created_at DESC");
    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Get user role for admin access
$user_role = null;
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
    <title>Company Directory - Discover Amazing Businesses</title>
    <link rel="stylesheet" href="CSS/index.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>

    </style>
</head>
<body>

    

    <!-- Companies Section -->
    <section class="companies-section">
        <div class="container">
            <div class="section-header">
            </div>
            
            <?php if (empty($companies)): ?>
                <div class="empty-state">
                    <i class="fas fa-building empty-icon"></i>
                    <h2>No Companies Yet</h2>
                    <p>Be the first to showcase your business in our premium directory. Start connecting with potential partners today.</p>
                    <a href="add_company.php" class="btn-primary" style="padding: 1rem 2rem;">
                        <i class="fas fa-plus"></i>
                        Add First Company
                    </a>
                </div>
            <?php else: ?>
                <div class="companies-grid" id="companiesGrid">
                    <?php foreach ($companies as $company): ?>
                    <div class="company-card"
                         data-name="<?php echo htmlspecialchars(strtolower($company['name'])); ?>"
                         data-category="<?php echo htmlspecialchars(strtolower($company['category'] ?? '')); ?>"
                         data-manager="<?php echo htmlspecialchars(strtolower($company['manager'])); ?>">
                        
                        <?php if ($company === reset($companies)): ?>
                            
                        <?php endif; ?>
                        
                        <div class="card-image">
                            <?php if (!empty($company['photo']) && file_exists($company['photo'])): ?>
                                <img src="<?php echo $company['photo']; ?>" 
                                     alt="<?php echo htmlspecialchars($company['name']); ?>" 
                                     class="card-img">
                            <?php else: ?>
                                <div class="image-placeholder">
                                    <i class="fas fa-building"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-overlay">
                                <a href="company_profile.php?id=<?php echo $company['id']; ?>" class="overlay-btn">
                                    <i class="fas fa-eye"></i>
                                    View Details
                                </a>
                            </div>
                        </div>
                        
                        <div class="card-content">
                            <div class="card-header">
                                <?php if (!empty($company['category'])): ?>
                                    <span class="card-category"><?php echo htmlspecialchars($company['category']); ?></span>
                                <?php endif; ?>
                                <span class="card-year">Since <?php echo $company['year_created']; ?></span>
                            </div>
                            
                            <h3 class="card-title"><?php echo htmlspecialchars($company['name']); ?></h3>
                            
                            <div class="card-manager">
                                <i class="fas fa-user-tie"></i>
                                <?php echo htmlspecialchars($company['manager']); ?>
                            </div>
                            
                            <div class="card-actions">
                                <a href="company_profile.php?id=<?php echo $company['id']; ?>" class="action-btn action-btn-primary">
                                    <i class="fas fa-eye"></i>
                                    View Profile
                                </a>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <a href="sales_order.php?company_id=<?php echo $company['id']; ?>" class="action-btn action-btn-secondary">
                                        <i class="fas fa-shopping-cart"></i>
                                        Order Now
                                    </a>
                                <?php else: ?>
                                    <button onclick="openCompanyInApp(<?php echo $company['id']; ?>)" class="action-btn action-btn-secondary">
                                        <i class="fas fa-sign-in-alt"></i>
                                        Login to Order
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

  

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Navbar scroll effect
            const navbar = document.getElementById('navbar');
            let lastScroll = 0;
            
            window.addEventListener('scroll', () => {
                const currentScroll = window.pageYOffset;
                
                if (currentScroll <= 0) {
                    navbar.classList.remove('scrolled');
                    return;
                }
                
                if (currentScroll > lastScroll && currentScroll > 100) {
                    navbar.style.transform = 'translateY(-100%)';
                } else {
                    navbar.style.transform = 'translateY(0)';
                    navbar.classList.add('scrolled');
                }
                
                lastScroll = currentScroll;
            });
            
            // Animate company cards on scroll
            const companyCards = document.querySelectorAll('.company-card');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });
            
            companyCards.forEach(card => observer.observe(card));
            
            // Enhanced stats animation
            const statNumbers = document.querySelectorAll('.stat-number');
            statNumbers.forEach(stat => {
                const target = parseInt(stat.textContent);
                let current = 0;
                const increment = target / 100;
                const duration = 1500;
                const startTime = Date.now();
                
                const updateCounter = () => {
                    const elapsed = Date.now() - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    
                    current = target * progress;
                    stat.textContent = Math.floor(current);
                    
                    if (progress < 1) {
                        requestAnimationFrame(updateCounter);
                    } else {
                        stat.textContent = target;
                    }
                };
                
                updateCounter();
            });
            
            // Search functionality with debounce
            const searchInput = document.getElementById('searchInput');
            let searchTimeout;
            
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(searchCompanies, 300);
            });
            
            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Ctrl/Cmd + K to focus search
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    searchInput.focus();
                    searchInput.select();
                }
                
                // Escape to clear search
                if (e.key === 'Escape' && document.activeElement === searchInput) {
                    searchInput.value = '';
                    searchCompanies();
                }
            });
            
            // Add click handler to search button
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    searchCompanies();
                }
            });
            
            // Initialize floating elements
            const floatingElements = document.querySelectorAll('.floating-element');
            floatingElements.forEach((element, index) => {
                element.style.animationDelay = `${index * 2}s`;
            });
        });
        
        function searchCompanies() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();
            const companyCards = document.querySelectorAll('.company-card');
            const companiesGrid = document.getElementById('companiesGrid');
            
            if (!searchTerm) {
                // Show all cards
                companyCards.forEach(card => {
                    card.style.display = 'block';
                    card.style.animation = 'fadeInUp 0.5s ease';
                });
                
                // Hide any existing no-results message
                const noResults = document.querySelector('.no-results');
                if (noResults) noResults.remove();
                return;
            }
            
            let hasResults = false;
            
            companyCards.forEach(card => {
                const name = card.dataset.name;
                const category = card.dataset.category;
                const manager = card.dataset.manager;
                
                if (name.includes(searchTerm) || 
                    category.includes(searchTerm) || 
                    manager.includes(searchTerm)) {
                    card.style.display = 'block';
                    card.style.animation = 'fadeInUp 0.5s ease';
                    hasResults = true;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Show no results message
            const existingNoResults = document.querySelector('.no-results');
            if (existingNoResults) existingNoResults.remove();
            
            if (!hasResults) {
                const noResults = document.createElement('div');
                noResults.className = 'empty-state no-results';
                noResults.innerHTML = `
                    <i class="fas fa-search empty-icon"></i>
                    <h2>No Results Found</h2>
                    <p>No companies match your search for "${searchTerm}". Try different keywords.</p>
                    <button onclick="clearSearch()" class="btn-primary" style="padding: 1rem 2rem;">
                        <i class="fas fa-times"></i>
                        Clear Search
                    </button>
                `;
                companiesGrid.parentNode.insertBefore(noResults, companiesGrid.nextSibling);
            }
        }
        
        function clearSearch() {
            document.getElementById('searchInput').value = '';
            searchCompanies();
            
            const noResults = document.querySelector('.no-results');
            if (noResults) noResults.remove();
        }
        
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => toast.classList.add('show'), 100);
            
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
        
        function openAndroidApp() {
            showToast('Opening application...', 'success');
            
            // Try to open app
            window.location.href = 'companyapp://login';
            
            // Fallback to web login
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 1500);
        }
        
        function openCompanyInApp(companyId) {
            showToast('Redirecting to company...', 'success');
            
            // Try to open app with company ID
            window.location.href = `companyapp://login?company_id=${companyId}`;
            
            // Fallback to web login
            setTimeout(() => {
                window.location.href = `login.php?company_id=${companyId}`;
            }, 1500);
        }
        
        // Make functions globally available
        window.searchCompanies = searchCompanies;
        window.clearSearch = clearSearch;
        window.openAndroidApp = openAndroidApp;
        window.openCompanyInApp = openCompanyInApp;
    </script>
</body>
</html>