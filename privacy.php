<?php
// privacy.php
include 'config.php';

// Start session only if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سياسة الخصوصية - ديراني غروب</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS Variables for consistent theming */
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --accent-color: #7209b7;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --dark-color: #2c3e50;
            --light-color: #f8f9fa;
            --gray-color: #6c757d;
            --shadow: 0 10px 30px rgba(0,0,0,0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: #333;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Professional Navigation Bar */
        .main-header {
            background: linear-gradient(135deg, var(--dark-color), #1a252f);
            color: white;
            padding: 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        .navbar {
            max-width: 1400px;
            margin: 0 auto;
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .nav-logo a {
            color: white;
            text-decoration: none;
            font-size: 1.4rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px 0;
            transition: var(--transition);
        }

        .nav-logo a:hover {
            opacity: 0.9;
        }

        .nav-menu {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .nav-menu a {
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 8px;
            transition: var(--transition);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-menu a.active {
            background: var(--primary-color);
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.4);
        }

        .nav-menu a:hover:not(.active) {
            background: rgba(255,255,255,0.1);
            transform: translateY(-2px);
        }

        /* Buttons */
        .btn-primary, .btn-secondary {
            padding: 12px 25px;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--success-color), #27ae60);
            color: white;
            box-shadow: 0 6px 20px rgba(46, 204, 113, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 12px 30px rgba(46, 204, 113, 0.6);
        }

        .btn-secondary {
            background: linear-gradient(135deg, var(--primary-color), #3a56e4);
            color: white;
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
        }

        .btn-secondary:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 12px 30px rgba(67, 97, 238, 0.6);
        }

        /* Main Content Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
            width: 100%;
            flex: 1;
        }

        /* Breadcrumb */
        .breadcrumb {
            background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(255,255,255,0.7));
            padding: 1.2rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 2.5rem;
            box-shadow: var(--shadow);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
        }

        .breadcrumb a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            padding: 4px 8px;
            border-radius: 6px;
        }

        .breadcrumb a:hover {
            background: rgba(67, 97, 238, 0.1);
            color: var(--secondary-color);
        }

        .breadcrumb span {
            color: var(--gray-color);
            font-weight: 500;
        }

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 40px;
            border-radius: 20px;
            margin-bottom: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            transform: translate(100px, -100px);
        }

        .page-header h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .hero-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 25px;
            position: relative;
            z-index: 1;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.8;
        }

        /* Privacy Content */
        .privacy-content {
            background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(255,255,255,0.85));
            border-radius: 25px;
            padding: 50px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
            margin-bottom: 40px;
        }

        .privacy-section {
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 2px solid rgba(67, 97, 238, 0.1);
        }

        .privacy-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .privacy-section h2 {
            color: var(--dark-color);
            font-size: 1.8rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            position: relative;
            padding-bottom: 15px;
        }

        .privacy-section h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 100px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            border-radius: 2px;
        }

        .privacy-section h2 i {
            color: var(--primary-color);
            background: rgba(67, 97, 238, 0.1);
            padding: 12px;
            border-radius: 12px;
        }

        .privacy-section h3 {
            color: var(--secondary-color);
            font-size: 1.4rem;
            margin: 25px 0 15px;
            padding-right: 20px;
            position: relative;
        }

        .privacy-section h3::before {
            content: '▸';
            position: absolute;
            right: 0;
            color: var(--primary-color);
        }

        .privacy-section p {
            color: var(--dark-color);
            line-height: 1.8;
            margin-bottom: 15px;
            font-size: 1.05rem;
            text-align: justify;
        }

        .privacy-section ul {
            list-style-type: none;
            margin: 20px 0;
            padding-right: 30px;
        }

        .privacy-section ul li {
            margin-bottom: 15px;
            padding-right: 25px;
            position: relative;
            color: var(--dark-color);
            line-height: 1.7;
        }

        .privacy-section ul li::before {
            content: '✓';
            position: absolute;
            right: 0;
            color: var(--success-color);
            font-weight: bold;
            background: rgba(46, 204, 113, 0.1);
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }

        .data-type {
            background: linear-gradient(135deg, rgba(240, 248, 255, 0.9), rgba(230, 240, 255, 0.9));
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            border-right: 4px solid var(--primary-color);
        }

        .data-type h4 {
            color: var(--primary-color);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .data-type h4 i {
            font-size: 1.2rem;
        }

        .privacy-note {
            background: linear-gradient(135deg, rgba(220, 247, 233, 0.9), rgba(209, 242, 225, 0.9));
            border-radius: 15px;
            padding: 25px;
            margin: 30px 0;
            border: 2px solid rgba(46, 204, 113, 0.3);
            position: relative;
            overflow: hidden;
        }

        .privacy-note::before {
            content: '🔒';
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 2rem;
            opacity: 0.3;
        }

        .privacy-note p {
            color: #0c5460;
            margin: 0;
            padding-right: 50px;
            font-weight: 500;
        }

        .privacy-warning {
            background: linear-gradient(135deg, rgba(255, 243, 205, 0.9), rgba(255, 235, 178, 0.9));
            border-right: 5px solid var(--warning-color);
            border-radius: 15px;
            padding: 25px;
            margin: 30px 0;
            position: relative;
            overflow: hidden;
        }

        .privacy-warning::before {
            content: '⚠';
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 2rem;
            opacity: 0.3;
        }

        .privacy-warning p {
            color: #856404;
            margin: 0;
            padding-right: 50px;
            font-weight: 500;
        }

        .last-updated {
            background: linear-gradient(135deg, rgba(220, 247, 233, 0.9), rgba(209, 242, 225, 0.9));
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            margin: 30px 0;
            border: 2px solid rgba(46, 204, 113, 0.3);
        }

        .last-updated p {
            color: var(--success-color);
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 1.1rem;
        }

        .last-updated p i {
            font-size: 1.3rem;
        }

        .rights-section {
            text-align: center;
            margin-top: 40px;
            padding: 30px;
            background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(255,255,255,0.7));
            border-radius: 20px;
            box-shadow: var(--shadow);
        }

        .rights-section p {
            margin-bottom: 20px;
            color: var(--dark-color);
            font-size: 1.1rem;
        }

        /* Back Button */
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 15px 30px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            margin-top: 20px;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
        }

        .back-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(67, 97, 238, 0.4);
        }

        /* Enhanced Professional Footer */
        .main-footer {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            color: white;
            padding: 70px 0 30px;
            margin-top: 80px;
            position: relative;
            overflow: hidden;
        }

        .main-footer::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 300px;
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.1), rgba(115, 9, 183, 0.1));
            border-radius: 50%;
            transform: translate(150px, -150px);
        }

        .main-footer::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 200px;
            height: 200px;
            background: linear-gradient(135deg, rgba(46, 204, 113, 0.1), rgba(41, 128, 185, 0.1));
            border-radius: 50%;
            transform: translate(-100px, 100px);
        }

        .footer-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 50px;
            position: relative;
            z-index: 1;
        }

        .footer-section {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: var(--transition);
        }

        .footer-section:hover {
            transform: translateY(-5px);
            border-color: var(--primary-color);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        .footer-section h3, .footer-section h4 {
            color: white;
            margin-bottom: 25px;
            font-size: 1.4rem;
            position: relative;
            padding-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .footer-section h3::after, .footer-section h4::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            border-radius: 2px;
        }

        .footer-section p {
            color: #b0b7c3;
            line-height: 1.8;
            margin-bottom: 20px;
            font-size: 1rem;
        }

        .footer-social {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }

        .social-link {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.2), rgba(115, 9, 183, 0.2));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: var(--transition);
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .social-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            opacity: 0;
            transition: var(--transition);
        }

        .social-link:hover::before {
            opacity: 1;
        }

        .social-link i {
            position: relative;
            z-index: 1;
            font-size: 1.2rem;
        }

        .social-link:hover {
            transform: translateY(-5px) rotate(5deg);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }

        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .contact-info p {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 0;
            padding: 10px 15px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            transition: var(--transition);
        }

        .contact-info p:hover {
            background: rgba(67, 97, 238, 0.1);
            transform: translateX(-5px);
        }

        .contact-info i {
            color: var(--primary-color);
            width: 20px;
            font-size: 1.1rem;
            background: rgba(255, 255, 255, 0.1);
            padding: 8px;
            border-radius: 8px;
        }

        .quick-links {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .quick-links a {
            color: #b0b7c3;
            text-decoration: none;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quick-links a:hover {
            color: white;
            padding-right: 10px;
            border-bottom-color: var(--primary-color);
        }

        .quick-links a::before {
            content: '↪';
            font-size: 0.9rem;
            opacity: 0;
            transition: var(--transition);
        }

        .quick-links a:hover::before {
            opacity: 1;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 40px;
            margin-top: 50px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #8a93a2;
            font-size: 0.95rem;
            position: relative;
            z-index: 1;
        }

        .footer-bottom::before {
            content: '';
            position: absolute;
            top: -1px;
            right: 50%;
            transform: translateX(50%);
            width: 100px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
        }

        .footer-bottom p {
            margin-bottom: 10px;
            font-size: 0.9rem;
        }

        .copyright-links {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
            margin-top: 15px;
        }

        .copyright-links a {
            color: #8a93a2;
            text-decoration: none;
            transition: var(--transition);
            font-size: 0.9rem;
            position: relative;
        }

        .copyright-links a:hover {
            color: white;
        }

        .copyright-links a::after {
            content: '•';
            position: absolute;
            left: -18px;
            color: rgba(255, 255, 255, 0.2);
        }

        .copyright-links a:first-child::after {
            display: none;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--accent-color);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .privacy-content {
                padding: 40px 30px;
            }
            
            .page-header h1 {
                font-size: 2.2rem;
            }
            
            .footer-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 30px;
            }
        }

        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                padding: 15px;
            }
            
            .nav-menu {
                margin-top: 15px;
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .page-header {
                padding: 30px 20px;
            }
            
            .page-header h1 {
                font-size: 1.8rem;
            }
            
            .privacy-content {
                padding: 30px 20px;
            }
            
            .privacy-section h2 {
                font-size: 1.5rem;
            }
            
            .privacy-section h3 {
                font-size: 1.2rem;
            }
            
            .footer-container {
                grid-template-columns: 1fr;
                gap: 25px;
            }
            
            .footer-section {
                padding: 25px;
            }
            
            .copyright-links {
                flex-direction: column;
                gap: 10px;
            }
            
            .copyright-links a::after {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .page-header h1 {
                font-size: 1.6rem;
            }
            
            .hero-subtitle {
                font-size: 1rem;
            }
            
            .privacy-section h2 {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .privacy-section h2 i {
                align-self: flex-start;
            }
            
            .privacy-section ul {
                padding-right: 20px;
            }
            
            .privacy-note::before,
            .privacy-warning::before {
                position: relative;
                left: 0;
                top: 0;
                transform: none;
                margin-bottom: 15px;
                display: block;
            }
            
            .privacy-note p,
            .privacy-warning p {
                padding-right: 0;
            }
        }

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .privacy-section {
            animation: fadeInUp 0.6s ease forwards;
            opacity: 0;
        }

        .privacy-section:nth-child(1) { animation-delay: 0.1s; }
        .privacy-section:nth-child(2) { animation-delay: 0.2s; }
        .privacy-section:nth-child(3) { animation-delay: 0.3s; }
        .privacy-section:nth-child(4) { animation-delay: 0.4s; }
        .privacy-section:nth-child(5) { animation-delay: 0.5s; }
        .privacy-section:nth-child(6) { animation-delay: 0.6s; }
        .privacy-section:nth-child(7) { animation-delay: 0.7s; }
        .privacy-section:nth-child(8) { animation-delay: 0.8s; }
    </style>
</head>
<body>
    <!-- Professional Navigation Bar -->
    <header class="main-header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <a href="index.php">
                        <i class="fas fa-building"></i>
                        ديراني غروب
                    </a>
                </div>
                <div class="nav-menu">
                    <a href="index.php">
                        <i class="fas fa-home"></i>
                        الرئيسية
                    </a>
                    <a href="all_categories.php">
                        <i class="fas fa-th-large"></i>
                        جميع الأصناف
                    </a>
                    <a href="privacy.php" class="active">
                        <i class="fas fa-shield-alt"></i>
                        سياسة الخصوصية
                    </a>
                    <a href="contact.php">
                        <i class="fas fa-envelope"></i>
                        اتصل بنا
                    </a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="logout.php" class="btn-secondary">
                            <i class="fas fa-sign-out-alt"></i>
                            تسجيل خروج
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn-primary">
                            <i class="fas fa-sign-in-alt"></i>
                            تسجيل دخول
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <div class="container">
            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <a href="index.php">
                    <i class="fas fa-home"></i>
                    الرئيسية
                </a> /
                <span>
                    <i class="fas fa-shield-alt"></i>
                    سياسة الخصوصية
                </span>
            </div>

            <!-- Page Header -->
            <div class="page-header">
                <h1>سياسة الخصوصية وحماية البيانات</h1>
                <p class="hero-subtitle">
                    نحن في ديراني غروب نولي أهمية قصوى لخصوصيتك وحماية بياناتك الشخصية. 
                    تشرح هذه السياسة كيفية جمعنا واستخدامنا وحمايتنا لمعلوماتك عند استخدامك لمنصتنا.
                </p>
            </div>

            <!-- Privacy Content -->
            <div class="privacy-content">
                <div class="last-updated">
                    <p>
                        <i class="fas fa-calendar-alt"></i>
                        آخر تحديث: ١ يناير ٢٠٢٥
                    </p>
                </div>

                <!-- Section 1: Introduction -->
                <div class="privacy-section">
                    <h2><i class="fas fa-info-circle"></i> مقدمة</h2>
                    <p>نحن في ديراني غروب ملتزمون بحماية خصوصيتك وضمان أمان بياناتك الشخصية. تشرح سياسة الخصوصية هذه كيفية جمع معلوماتك الشخصية واستخدامها والإفصاح عنها وحمايتها عند استخدامك لمنصتنا وخدماتنا.</p>
                    
                    <h3>نطاق السياسة</h3>
                    <p>تنطبق سياسة الخصوصية هذه على جميع المعلومات التي نجمعها منك عند استخدامك لموقعنا الإلكتروني وتطبيقاتنا والخدمات المرتبطة بها ("الخدمات").</p>
                    
                    <div class="privacy-note">
                        <p>باستخدامك لخدماتنا، فإنك توافق على شروط سياسة الخصوصية هذه وتسمح لنا بجمع واستخدام معلوماتك الشخصية كما هو موضح فيها.</p>
                    </div>
                </div>

                <!-- Section 2: Information We Collect -->
                <div class="privacy-section">
                    <h2><i class="fas fa-database"></i> المعلومات التي نجمعها</h2>
                    
                    <h3>المعلومات الشخصية</h3>
                    <p>عند استخدامك لخدماتنا، قد نجمع الأنواع التالية من المعلومات الشخصية:</p>
                    
                    <div class="data-type">
                        <h4><i class="fas fa-user"></i> معلومات الهوية</h4>
                        <ul>
                            <li>الاسم الكامل</li>
                            <li>عنوان البريد الإلكتروني</li>
                            <li>رقم الهاتف</li>
                            <li>عنوان السكن أو العمل</li>
                        </ul>
                    </div>
                    
                    <div class="data-type">
                        <h4><i class="fas fa-briefcase"></i> معلومات الأعمال</h4>
                        <ul>
                            <li>اسم الشركة</li>
                            <li>رقم السجل التجاري</li>
                            <li>معلومات الفواتير والمدفوعات</li>
                            <li>تفضيلات المنتجات</li>
                        </ul>
                    </div>
                    
                    <div class="data-type">
                        <h4><i class="fas fa-chart-line"></i> معلومات الاستخدام</h4>
                        <ul>
                            <li>سجل التصفح والبحث</li>
                            <li>تفضيلات المنتجات</li>
                            <li>معلومات الجهاز والمتصفح</li>
                            <li>عنوان IP والموقع الجغرافي</li>
                        </ul>
                    </div>
                </div>

                <!-- Section 3: How We Use Your Information -->
                <div class="privacy-section">
                    <h2><i class="fas fa-cogs"></i> كيفية استخدام معلوماتك</h2>
                    
                    <p>نستخدم معلوماتك الشخصية للأغراض التالية:</p>
                    
                    <ul>
                        <li>توفير وتحسين خدماتنا ومنصتنا</li>
                        <li>معالجة طلباتك ومعاملاتك</li>
                        <li>التواصل معك بشأن خدماتنا وعروضنا</li>
                        <li>تحليل استخدام المنصة وتحسين تجربة المستخدم</li>
                        <li>منع الاحتيال وحماية أمن منصتنا</li>
                        <li>الامتثال للالتزامات القانونية والتنظيمية</li>
                    </ul>
                    
                    <div class="privacy-note">
                        <p>نحن لا نبيع أو نؤجر معلوماتك الشخصية لأطراف ثالثة لأغراض التسويق دون موافقتك الصريحة.</p>
                    </div>
                </div>

                <!-- Section 4: Data Sharing and Disclosure -->
                <div class="privacy-section">
                    <h2><i class="fas fa-share-alt"></i> مشاركة البيانات والإفصاح</h2>
                    
                    <h3>مشاركة المعلومات</h3>
                    <p>قد نشارك معلوماتك الشخصية مع:</p>
                    
                    <ul>
                        <li>مزودي الخدمات الذين يساعدوننا في تشغيل منصتنا</li>
                        <li>الشركاء التجاريين المعتمدين</li>
                        <li>الجهات الحكومية عند الاقتضاء قانونيًا</li>
                        <li>المشتري أو الخلف في حال اندماج أو استحواذ أو بيع أصول</li>
                    </ul>
                    
                    <div class="privacy-warning">
                        <p>نشارك فقط المعلومات الضرورية لأداء الخدمات ونضمن أن جميع الأطراف الثالثة تلتزم بمعايير حماية البيانات المماثلة.</p>
                    </div>
                </div>

                <!-- Section 5: Data Security -->
                <div class="privacy-section">
                    <h2><i class="fas fa-lock"></i> أمن البيانات</h2>
                    
                    <h3>تدابير الحماية</h3>
                    <p>نحن نطبق تدابير أمنية فنية وإدارية مناسبة لحماية معلوماتك الشخصية من الوصول غير المصرح به أو التعديل أو الإفصاح أو التدمير.</p>
                    
                    <ul>
                        <li>التشفير أثناء النقل والتخزين</li>
                        <li>جدران الحماية وأنظمة الكشف عن التسلل</li>
                        <li>مراقبة الوصول والتحكم في الصلاحيات</li>
                        <li>تدريب الموظفين على أمن المعلومات</li>
                        <li>عمليات التدقيق والمراجعة المنتظمة</li>
                    </ul>
                    
                    <h3>حدود الأمن</h3>
                    <p>على الرغم من بذلنا قصارى جهدنا، لا يمكن ضمان أمن المعلومات المنقولة عبر الإنترنت بنسبة 100%. يتحمل المستخدم بعض المخاطر المرتبطة بنقل البيانات عبر الإنترنت.</p>
                </div>

                <!-- Section 6: Cookies and Tracking Technologies -->
                <div class="privacy-section">
                    <h2><i class="fas fa-cookie-bite"></i> ملفات تعريف الارتباط والتقنيات</h2>
                    
                    <h3>ما هي ملفات تعريف الارتباط؟</h3>
                    <p>ملفات تعريف الارتباط هي ملفات نصية صغيرة يتم تخزينها على جهازك عندما تزور موقعنا. تساعدنا في تحسين تجربة التصفح وتقديم محتوى مخصص.</p>
                    
                    <h3>أنواع ملفات تعريف الارتباط التي نستخدمها:</h3>
                    
                    <div class="data-type">
                        <h4><i class="fas fa-utensils"></i> ملفات تعريف الارتباط الأساسية</h4>
                        <p>ضرورية لعمل الموقع بشكل صحيح، مثل حفظ تفضيلات اللغة.</p>
                    </div>
                    
                    <div class="data-type">
                        <h4><i class="fas fa-chart-bar"></i> ملفات تعريف الارتباط التحليلية</h4>
                        <p>تساعدنا في فهم كيفية استخدام الزوار لموقعنا وتحسينه.</p>
                    </div>
                    
                    <div class="data-type">
                        <h4><i class="fas fa-ad"></i> ملفات تعريف الارتباط التسويقية</h4>
                        <p>تستخدم لتقديم إعلانات مخصصة بناءً على اهتماماتك.</p>
                    </div>
                    
                    <div class="privacy-note">
                        <p>يمكنك التحكم في ملفات تعريف الارتباط من خلال إعدادات المتصفح، لكن إيقافها قد يؤثر على وظائف الموقع.</p>
                    </div>
                </div>

                <!-- Section 7: Your Rights -->
                <div class="privacy-section">
                    <h2><i class="fas fa-user-check"></i> حقوقك</h2>
                    
                    <p>لديك الحقوق التالية فيما يتعلق ببياناتك الشخصية:</p>
                    
                    <ul>
                        <li>الحق في الاطلاع على بياناتك الشخصية</li>
                        <li>الحق في تصحيح البيانات غير الدقيقة</li>
                        <li>الحق في حذف بياناتك الشخصية ("حق النسيان")</li>
                        <li>الحق في تقييد معالجة بياناتك</li>
                        <li>الحق في نقل البيانات</li>
                        <li>الحق في الاعتراض على المعالجة</li>
                        <li>الحق في سحب الموافقة في أي وقت</li>
                    </ul>
                    
                    <h3>ممارسة حقوقك</h3>
                    <p>لممارسة أي من حقوقك المذكورة أعلاه، يرجى الاتصال بنا عبر البريد الإلكتروني: <strong>privacy@diranigroup.com</strong></p>
                </div>

                <!-- Section 8: Children's Privacy -->
                <div class="privacy-section">
                    <h2><i class="fas fa-child"></i> خصوصية الأطفال</h2>
                    
                    <p>خدماتنا لا تستهدف الأطفال تحت سن 18 سنة. نحن لا نجمع عمدًا معلومات شخصية من الأطفال تحت هذا السن.</p>
                    
                    <div class="privacy-warning">
                        <p>إذا علمنا أننا جمعنا معلومات شخصية من طفل تحت سن 18 سنة دون موافقة الوالدين، سنقوم بحذف هذه المعلومات في أسرع وقت ممكن.</p>
                    </div>
                </div>

                <!-- Section 9: International Data Transfers -->
                <div class="privacy-section">
                    <h2><i class="fas fa-globe"></i> نقل البيانات الدولية</h2>
                    
                    <p>نقوم بتخزين ومعالجة بياناتك في لبنان. ومع ذلك، قد ننقل بياناتك إلى دول أخرى لأغراض المعالجة أو التخزين.</p>
                    
                    <h3>ضمانات الحماية</h3>
                    <p>عند نقل بياناتك خارج لبنان، نتخذ التدابير اللازمة لضمان حماية مناسبة لبياناتك وفقًا للقوانين المعمول بها.</p>
                </div>

                <!-- Section 10: Changes to Privacy Policy -->
                <div class="privacy-section">
                    <h2><i class="fas fa-sync-alt"></i> التغييرات على سياسة الخصوصية</h2>
                    
                    <p>قد نقوم بتحديث سياسة الخصوصية هذه من وقت لآخر. سنقوم بإشعارك بأي تغييرات جوهرية عن طريق نشر الإشعار على موقعنا أو إرسال إشعار مباشر إليك.</p>
                    
                    <h3>الاستمرار في الاستخدام</h3>
                    <p>استمرار استخدامك لخدماتنا بعد التغييرات يعني موافقتك على سياسة الخصوصية المعدلة.</p>
                </div>

                <!-- Section 11: Contact Us -->
                <div class="privacy-section">
                    <h2><i class="fas fa-headset"></i> اتصل بنا</h2>
                    
                    <p>إذا كانت لديك أي أسئلة أو مخاوف بشأن سياسة الخصوصية هذه أو ممارساتنا في التعامل مع البيانات، يرجى الاتصال بنا:</p>
                    
                    <div class="data-type">
                        <h4><i class="fas fa-envelope"></i> البريد الإلكتروني</h4>
                        <p><strong>privacy@diranigroup.com</strong></p>
                    </div>
                    
                    <div class="data-type">
                        <h4><i class="fas fa-phone"></i> الهاتف</h4>
                        <p><strong>+961 1 123 456 (الإثنين - الجمعة: 9 صباحاً - 5 مساءً)</strong></p>
                    </div>
                    
                    <div class="data-type">
                        <h4><i class="fas fa-map-marker-alt"></i> العنوان البريدي</h4>
                        <p><strong>ديراني غروب - قسم حماية البيانات<br>بيروت، شارع الحمراء، لبنان</strong></p>
                    </div>
                </div>

                <!-- Rights Section -->
                <div class="rights-section">
                    <p>نحن ملتزمون بحماية خصوصيتك وضمان الشفافية في كيفية تعاملنا مع بياناتك الشخصية.</p>
                    <button onclick="window.history.back()" class="back-btn">
                        <i class="fas fa-arrow-right"></i>
                        العودة للصفحة السابقة
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- Enhanced Professional Footer -->
    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-section">
                <h3>
                    <i class="fas fa-building"></i>
                    ديراني غروب
                </h3>
                <p>منصة رائدة في عرض وتسويق المنتجات من الشركات الرائدة في السوق. نقدم حلولاً متكاملة لتسهيل الوصول إلى المنتجات والخدمات.</p>
                <div class="footer-social">
                    <a href="https://www.facebook.com/DiraniCompany/" class="social-link" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://www.instagram.com/diranigroup/?hl=ar" class="social-link" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://twitter.com/" class="social-link" title="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://linkedin.com/" class="social-link" title="LinkedIn">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                </div>
            </div>
            
            <div class="footer-section">
                <h4>
                    <i class="fas fa-link"></i>
                    روابط سريعة
                </h4>
                <div class="quick-links">
                    <a href="index.php">
                        <i class="fas fa-home"></i>
                        الرئيسية
                    </a>
                    <a href="all_categories.php">
                        <i class="fas fa-th-large"></i>
                        جميع الأصناف
                    </a>
                    <a href="about.php">
                        <i class="fas fa-info-circle"></i>
                        عن الشركة
                    </a>
                    <a href="contact.php">
                        <i class="fas fa-envelope"></i>
                        اتصل بنا
                    </a>
                    <a href="terms.php">
                        <i class="fas fa-file-contract"></i>
                        شروط الاستخدام
                    </a>
                </div>
            </div>
            
            <div class="footer-section">
                <h4>
                    <i class="fas fa-address-card"></i>
                    معلومات الاتصال
                </h4>
                <div class="contact-info">
                    <p>
                        <i class="fas fa-envelope"></i>
                        <span>info@diranigroup.com</span>
                    </p>
                    <p>
                        <i class="fas fa-phone"></i>
                        <span>+961 1 123 456</span>
                    </p>
                    <p>
                        <i class="fas fa-mobile-alt"></i>
                        <span>+961 70 123 456</span>
                    </p>
                    <p>
                        <i class="fas fa-map-marker-alt"></i>
                        <span>بيروت، شارع الحمراء، لبنان</span>
                    </p>
                    <p>
                        <i class="fas fa-clock"></i>
                        <span>الأحد - الخميس: 8:00 صباحاً - 5:00 مساءً</span>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2025 ديراني غروب. جميع الحقوق محفوظة.</p>
            <p>تم التطوير بواسطة المهندس مهدي الحاج حسن</p>
            <div class="copyright-links">
                <a href="terms.php">شروط الاستخدام</a>
                <a href="privacy.php">سياسة الخصوصية</a>
                <a href="sitemap.php">خريطة الموقع</a>
                <a href="contact.php">الدعم الفني</a>
            </div>
        </div>
    </footer>

    <script>
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Add print functionality
    function printPrivacyPolicy() {
        window.print();
    }
    </script>
</body>
</html>