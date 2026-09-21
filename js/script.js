// Company Profile Enhanced Functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('🏢 Company Profile Enhanced Loaded');
    
    // Initialize slideshow
    initSlideshow();
    
    // Initialize animations
    initializeTeamAnimations();
    initializePremiumShowcase();
    
    // Initialize form validation
    initFeedbackForm();
    
    // Initialize smooth scrolling
    initSmoothScrolling();
});

// ===== SLIDESHOW FUNCTIONALITY =====
let currentSlide = 0;
let slides = [];
let slideInterval;

function initSlideshow() {
    slides = document.querySelectorAll('.slide');
    if (slides.length === 0) return;
    
    // Auto slide every 5 seconds
    slideInterval = setInterval(() => {
        changeSlide(1);
    }, 5000);
    
    // Show first slide
    showSlide(currentSlide);
    
    // Add keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') changeSlide(-1);
        if (e.key === 'ArrowRight') changeSlide(1);
        if (e.key === ' ') {
            e.preventDefault();
            changeSlide(1);
        }
    });
    
    // Pause slideshow on hover
    const slideshowContainer = document.querySelector('.slideshow-container');
    if (slideshowContainer) {
        slideshowContainer.addEventListener('mouseenter', () => {
            clearInterval(slideInterval);
        });
        
        slideshowContainer.addEventListener('mouseleave', () => {
            slideInterval = setInterval(() => {
                changeSlide(1);
            }, 5000);
        });
    }
}

function changeSlide(direction) {
    currentSlide += direction;
    
    if (currentSlide < 0) {
        currentSlide = slides.length - 1;
    } else if (currentSlide >= slides.length) {
        currentSlide = 0;
    }
    
    showSlide(currentSlide);
}

function goToSlide(index) {
    currentSlide = index;
    showSlide(currentSlide);
}

function showSlide(index) {
    // Hide all slides
    slides.forEach(slide => {
        slide.classList.remove('active');
        slide.style.zIndex = '1';
    });
    
    // Update dots
    const dots = document.querySelectorAll('.slide-dot');
    dots.forEach(dot => dot.classList.remove('active'));
    
    // Show current slide
    if (slides[index]) {
        slides[index].classList.add('active');
        slides[index].style.zIndex = '2';
    }
    
    // Update current dot
    if (dots[index]) {
        dots[index].classList.add('active');
    }
    
    // Reset autoplay timer
    clearInterval(slideInterval);
    slideInterval = setInterval(() => {
        changeSlide(1);
    }, 5000);
}

// ===== FORM VALIDATION =====
function initFeedbackForm() {
    const feedbackForm = document.getElementById('feedbackForm');
    if (!feedbackForm) return;
    
    feedbackForm.addEventListener('submit', function(e) {
        let isValid = true;
        const name = this.querySelector('#name');
        const email = this.querySelector('#email');
        const message = this.querySelector('#message');
        const rating = this.querySelector('#rating');
        
        // Clear previous errors
        this.querySelectorAll('.input-error').forEach(error => error.remove());
        this.querySelectorAll('.form-group input, .form-group textarea, .form-group select').forEach(field => {
            field.style.borderColor = '';
        });
        
        // Name validation
        if (name.value.trim().length < 2) {
            showInputError(name, 'Name must be at least 2 characters long');
            isValid = false;
        }
        
        // Email validation
        if (!isValidEmail(email.value)) {
            showInputError(email, 'Please enter a valid email address');
            isValid = false;
        }
        
        // Rating validation
        if (!rating.value) {
            showInputError(rating, 'Please select a rating');
            isValid = false;
        }
        
        // Message validation
        if (message.value.trim().length < 10) {
            showInputError(message, 'Message must be at least 10 characters long');
            isValid = false;
        }
        
        if (message.value.trim().length > 1000) {
            showInputError(message, 'Message cannot exceed 1000 characters');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            // Scroll to first error
            const firstError = this.querySelector('.input-error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
}

function showInputError(field, message) {
    field.style.borderColor = '#e74c3c';
    const errorDiv = document.createElement('div');
    errorDiv.className = 'input-error';
    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
    field.parentNode.appendChild(errorDiv);
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// ===== SMOOTH SCROLLING =====
function initSmoothScrolling() {
    // Smooth scrolling for navigation links
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            
            if (targetSection) {
                const headerHeight = document.querySelector('.main-header').offsetHeight;
                const targetPosition = targetSection.offsetTop - headerHeight - 20;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
                
                // Update active navigation
                document.querySelectorAll('.nav-link').forEach(nav => nav.classList.remove('active'));
                this.classList.add('active');
                
                // Update URL hash
                history.pushState(null, null, targetId);
            }
        });
    });
    
    // Update active navigation on scroll
    window.addEventListener('scroll', function() {
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.nav-link');
        const scrollPos = window.scrollY + 100;
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.offsetHeight;
            const sectionId = section.getAttribute('id');
            
            if (scrollPos >= sectionTop && scrollPos < sectionTop + sectionHeight) {
                navLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === '#' + sectionId) {
                        link.classList.add('active');
                    }
                });
            }
        });
    });
}

// ===== ANIMATIONS =====
// Team animations
function initializeTeamAnimations() {
    const teamMembers = document.querySelectorAll('.team-member');
    if (teamMembers.length === 0) return;
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, index * 200);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    
    teamMembers.forEach((member, index) => {
        member.style.opacity = '0';
        member.style.transform = 'translateY(30px)';
        member.style.transition = `all 0.6s ease ${index * 0.1}s`;
        observer.observe(member);
    });
}

// Product showcase animations
function initializePremiumShowcase() {
    const productCards = document.querySelectorAll('.product-showcase-card');
    if (productCards.length === 0) return;
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, index * 150);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    
    productCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = `all 0.6s ease ${index * 0.1}s`;
        observer.observe(card);
    });
}

// ===== PREMIUM PRODUCT MODAL =====
window.showPremiumDetails = function(productName, productDescription, productCategory, productPrice) {
    // Remove existing modal if present
    const existingModal = document.querySelector('.premium-modal-overlay');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Create modal overlay
    const modalOverlay = document.createElement('div');
    modalOverlay.className = 'premium-modal-overlay';
    
    // Create modal content
    modalOverlay.innerHTML = `
        <div class="premium-modal">
            <div class="modal-header">
                <span class="modal-badge">
                    <i class="fas fa-gem"></i>
                    PREMIUM DETAILS
                </span>
                <button class="modal-close" onclick="closePremiumModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-content">
                <div class="modal-visual">
                    <div class="modal-image-container">
                        <i class="fas fa-gem"></i>
                        <div class="modal-glow"></div>
                    </div>
                </div>
                <div class="modal-details">
                    <div class="modal-category">
                        <i class="fas fa-tag"></i>
                        ${productCategory}
                    </div>
                    <h2 class="modal-title">${productName}</h2>
                    <p class="modal-description">${productDescription}</p>
                    
                    <div class="modal-features">
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Premium Quality Materials</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Expert Craftsmanship</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Customer Satisfaction Guarantee</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Fast & Secure Delivery</span>
                        </div>
                    </div>
                    
                    <div class="price-display" style="margin: 25px 0; padding: 15px; background: rgba(52, 152, 219, 0.1); border-radius: 10px;">
                        <div style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 5px;">Premium Investment</div>
                        <div style="font-size: 1.8rem; font-weight: 700; color: #2ecc71;">${productPrice}</div>
                    </div>
                    
                    <div class="modal-actions">
                        <button class="modal-btn primary" onclick="inquireAboutProduct('${productName}')">
                            <i class="fas fa-envelope"></i>
                            Request Information
                        </button>
                        <button class="modal-btn secondary" onclick="closePremiumModal()">
                            <i class="fas fa-times"></i>
                            Close Preview
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add to body
    document.body.appendChild(modalOverlay);
    
    // Prevent body scroll
    document.body.style.overflow = 'hidden';
    
    // Add escape key listener
    const escapeHandler = (e) => {
        if (e.key === 'Escape') {
            closePremiumModal();
            document.removeEventListener('keydown', escapeHandler);
        }
    };
    document.addEventListener('keydown', escapeHandler);
};

function closePremiumModal() {
    const modal = document.querySelector('.premium-modal-overlay');
    if (modal) {
        modal.style.opacity = '0';
        modal.style.transform = 'scale(0.95)';
        
        setTimeout(() => {
            modal.remove();
            document.body.style.overflow = '';
        }, 300);
    }
}

function inquireAboutProduct(productName) {
    alert(`Thank you for your interest in ${productName}! Our team will contact you shortly with more information.`);
    closePremiumModal();
}

// ===== BRAND DETAILS =====
window.showBrandDetails = function(brandName) {
    alert(`Premium Brand: ${brandName}\n\nThis brand represents the highest quality standards and exceptional value in our collection.`);
};

// ===== IMAGE PRELOADER =====
function preloadImages() {
    const images = document.querySelectorAll('img[data-src]');
    images.forEach(img => {
        const src = img.getAttribute('data-src');
        if (src) {
            const image = new Image();
            image.src = src;
            image.onload = () => {
                img.src = src;
                img.classList.add('loaded');
            };
        }
    });
}

// Call image preloader
setTimeout(preloadImages, 1000);