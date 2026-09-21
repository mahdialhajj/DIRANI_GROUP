<?php
// terms.php
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
    <title>شروط الاستخدام - ديراني غروب</title>

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

        /* Terms Content */
        .terms-content {
            background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(255,255,255,0.85));
            border-radius: 25px;
            padding: 50px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
            margin-bottom: 40px;
        }

        .terms-section {
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 2px solid rgba(67, 97, 238, 0.1);
        }

        .terms-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .terms-section h2 {
            color: var(--dark-color);
            font-size: 1.8rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            position: relative;
            padding-bottom: 15px;
        }

        .terms-section h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 100px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            border-radius: 2px;
        }

        .terms-section h2 i {
            color: var(--primary-color);
            background: rgba(67, 97, 238, 0.1);
            padding: 12px;
            border-radius: 12px;
        }

        .terms-section h3 {
            color: var(--secondary-color);
            font-size: 1.4rem;
            margin: 25px 0 15px;
            padding-right: 20px;
            position: relative;
        }

        .terms-section h3::before {
            content: '▸';
            position: absolute;
            right: 0;
            color: var(--primary-color);
        }

        .terms-section p {
            color: var(--dark-color);
            line-height: 1.8;
            margin-bottom: 15px;
            font-size: 1.05rem;
            text-align: justify;
        }

        .terms-section ul {
            list-style-type: none;
            margin: 20px 0;
            padding-right: 30px;
        }

        .terms-section ul li {
            margin-bottom: 15px;
            padding-right: 25px;
            position: relative;
            color: var(--dark-color);
            line-height: 1.7;
        }

        .terms-section ul li::before {
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

        .terms-section ol {
            margin: 20px 0;
            padding-right: 40px;
        }

        .terms-section ol li {
            margin-bottom: 15px;
            color: var(--dark-color);
            line-height: 1.7;
            padding-right: 10px;
        }

        .important-note {
            background: linear-gradient(135deg, rgba(255, 243, 205, 0.9), rgba(255, 235, 178, 0.9));
            border-right: 5px solid var(--warning-color);
            border-radius: 15px;
            padding: 25px;
            margin: 30px 0;
            position: relative;
            overflow: hidden;
        }

        .important-note::before {
            content: '!';
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--warning-color);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .important-note p {
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

        .accept-terms {
            text-align: center;
            margin-top: 40px;
            padding: 30px;
            background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(255,255,255,0.7));
            border-radius: 20px;
            box-shadow: var(--shadow);
        }

        .accept-terms p {
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
            .terms-content {
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
            
            .terms-content {
                padding: 30px 20px;
            }
            
            .terms-section h2 {
                font-size: 1.5rem;
            }
            
            .terms-section h3 {
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
            
            .terms-section h2 {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .terms-section h2 i {
                align-self: flex-start;
            }
            
            .terms-section ul, .terms-section ol {
                padding-right: 20px;
            }
            
            .important-note::before {
                position: relative;
                left: 0;
                top: 0;
                transform: none;
                margin-bottom: 15px;
                align-self: flex-start;
            }
            
            .important-note p {
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

        .terms-section {
            animation: fadeInUp 0.6s ease forwards;
            opacity: 0;
        }

        .terms-section:nth-child(1) { animation-delay: 0.1s; }
        .terms-section:nth-child(2) { animation-delay: 0.2s; }
        .terms-section:nth-child(3) { animation-delay: 0.3s; }
        .terms-section:nth-child(4) { animation-delay: 0.4s; }
        .terms-section:nth-child(5) { animation-delay: 0.5s; }
        .terms-section:nth-child(6) { animation-delay: 0.6s; }
        .terms-section:nth-child(7) { animation-delay: 0.7s; }
        .terms-section:nth-child(8) { animation-delay: 0.8s; }
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
                    <a href="terms.php" class="active">
                        <i class="fas fa-file-contract"></i>
                        شروط الاستخدام
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
                    <i class="fas fa-file-contract"></i>
                    شروط الاستخدام
                </span>
            </div>

            <!-- Page Header -->
            <div class="page-header">
                <h1>شروط وأحكام الاستخدام</h1>
                <p class="hero-subtitle">
                    يرجى قراءة الشروط والأحكام التالية بعناية قبل استخدام منصة ديراني غروب. 
                    يعد استخدامك للموقع موافقة على هذه الشروط والأحكام.
                </p>
            </div>

            <!-- Terms Content -->
            <div class="terms-content">
                <div class="last-updated">
                    <p>
                        <i class="fas fa-calendar-alt"></i>
                        آخر تحديث: ١ يناير ٢٠٢٥
                    </p>
                </div>

                <!-- Section 1: Introduction -->
                <div class="terms-section">
                    <h2><i class="fas fa-info-circle"></i> المقدمة والموافقة</h2>
                    <p>مرحبًا بكم في منصة ديراني غروب. تشكل هذه الشروط والأحكام ("الشروط") اتفاقًا قانونيًا بينك ("المستخدم") وبين ديراني غروب ("الشركة") فيما يتعلق باستخدامك للموقع الإلكتروني والتطبيقات والخدمات المرتبطة به ("المنصة").</p>
                    
                    <h3>القبول والموافقة</h3>
                    <p>باستخدامك لهذه المنصة، فإنك توافق على الالتزام بهذه الشروط والأحكام بالكامل. إذا كنت لا توافق على أي جزء من هذه الشروط، فيرجى عدم استخدام المنصة.</p>
                    
                    <h3>تعديل الشروط</h3>
                    <p>تحتفظ الشركة بالحق في تعديل أو تحديث هذه الشروط في أي وقت دون إشعار مسبق. يتحمل المستخدم مسؤولية مراجعة هذه الشروط بشكل دوري. استمرار استخدامك للمنصة بعد التعديلات يعني موافقتك على الشروط المعدلة.</p>
                </div>

                <!-- Section 2: Definitions -->
                <div class="terms-section">
                    <h2><i class="fas fa-book"></i> التعريفات</h2>
                    <p>في هذه الشروط، يكون للكلمات والعبارات التالية المعاني المخصصة لها أدناه ما لم يقتض السياق خلاف ذلك:</p>
                    
                    <ul>
                        <li><strong>"المنصة"</strong>: تشير إلى موقع ديراني غروب الإلكتروني وتطبيقاته وخدماته المرتبطة به.</li>
                        <li><strong>"المستخدم"</strong>: أي شخص يصل إلى المنصة أو يستخدمها بأي شكل من الأشكال.</li>
                        <li><strong>"المحتوى"</strong>: جميع المعلومات والنصوص والصور والفيديوهات والبيانات والمواد المتاحة على المنصة.</li>
                        <li><strong>"الحساب"</strong>: الحساب الشخصي الذي ينشئه المستخدم للوصول إلى خدمات المنصة.</li>
                        <li><strong>"الخدمات"</strong>: جميع الخدمات المقدمة من خلال المنصة بما في ذلك عرض المنتجات والمعلومات التجارية.</li>
                    </ul>
                </div>

                <!-- Section 3: User Registration and Accounts -->
                <div class="terms-section">
                    <h2><i class="fas fa-user-circle"></i> التسجيل والحسابات</h2>
                    
                    <h3>إنشاء الحساب</h3>
                    <p>للاستفادة من بعض ميزات المنصة، قد يُطلب منك إنشاء حساب مستخدم. أنت مسؤول عن:</p>
                    <ol>
                        <li>توفير معلومات دقيقة وكاملة عند إنشاء الحساب</li>
                        <li>الحفاظ على سرية معلومات تسجيل الدخول</li>
                        <li>جميع الأنشطة التي تتم تحت حسابك</li>
                        <li>إبلاغنا فورًا عن أي استخدام غير مصرح به لحسابك</li>
                    </ol>
                    
                    <h3>متطلبات العمر</h3>
                    <p>يجب أن يكون عمرك 18 سنة على الأقل لإنشاء حساب على المنصة. باستخدام المنصة، تؤكد أنك تبلغ من العمر 18 سنة أو أكثر.</p>
                    
                    <div class="important-note">
                        <p>أنت مسؤول بالكامل عن الحفاظ على سرية كلمة مرور حسابك وجميع الأنشطة التي تتم تحت حسابك.</p>
                    </div>
                </div>

                <!-- Section 4: Acceptable Use -->
                <div class="terms-section">
                    <h2><i class="fas fa-check-circle"></i> الاستخدام المقبول</h2>
                    <p>يوافق المستخدم على استخدام المنصة فقط للأغراض القانونية وبطريقة لا تنتهك حقوق الآخرين أو تقيد أو تمنع استخدام الآخرين للمنصة.</p>
                    
                    <h3>المحظورات</h3>
                    <p>يمنع على المستخدم:</p>
                    <ul>
                        <li>استخدام المنصة بطريقة غير قانونية أو احتيالية أو لأي غرض غير قانوني</li>
                        <li>نشر أو نقل أي محتوى غير قانوني أو ضار أو مسيء أو تشهيري</li>
                        <li>التدخل في أو تعطيل سلامة أو أداء المنصة</li>
                        <li>محاولة الوصول غير المصرح به إلى أي جزء من المنصة</li>
                        <li>استخدام المنصة لإرسال أي إعلانات غير مرغوب فيها أو غير مصرح بها</li>
                        <li>جمع معلومات عن مستخدمين آخرين دون موافقتهم</li>
                    </ul>
                </div>

                <!-- Section 5: Intellectual Property -->
                <div class="terms-section">
                    <h2><i class="fas fa-copyright"></i> الملكية الفكرية</h2>
                    
                    <h3>حقوق الملكية</h3>
                    <p>جميع الحقوق المحفوظة والمحتوى والتصميمات والبرامج والعلامات التجارية وكل ما يتعلق بالمنصة هي ملك لشركة ديراني غروب أو مرخص لها باستخدامها، ما لم يُنص على خلاف ذلك.</p>
                    
                    <h3>التراخيص المحدودة</h3>
                    <p>تمنحك الشركة ترخيصًا محدودًا وغير حصري وغير قابل للتحويل للوصول إلى المنصة واستخدامها للأغراض الشخصية والتجارية المشروعة فقط. هذا الترخيص لا يشمل:</p>
                    <ul>
                        <li>إعادة بيع أو استخدام تجاري للمحتوى</li>
                        <li>جمع واستخدام أي قوائم منتجات أو وصفات أو بيانات أخرى</li>
                        <li>تعديل أو إنشاء أعمال مشتقة من المحتوى</li>
                        <li>استخدام أي تقنيات تجميع البيانات أو الروبوتات أو أدناء مماثلة</li>
                    </ul>
                </div>

                <!-- Section 6: Privacy Policy -->
                <div class="terms-section">
                    <h2><i class="fas fa-shield-alt"></i> الخصوصية والبيانات</h2>
                    
                    <h3>سياسة الخصوصية</h3>
                    <p>يخضع جمع واستخدام معلوماتك الشخصية لسياسة الخصوصية الخاصة بنا، والتي تشكل جزءًا لا يتجزأ من هذه الشروط. يرجى مراجعة سياسة الخصوصية لفهم كيفية جمعنا واستخدامنا وحمايتنا لمعلوماتك.</p>
                    
                    <h3>بيانات المنتجات</h3>
                    <p>نحن نبذل قصارى جهدنا لضمان دقة معلومات المنتجات المعروضة على المنصة. ومع ذلك، لا نتحمل مسؤولية أي أخطاء أو سهو في محتوى المنتجات.</p>
                </div>

                <!-- Section 7: Limitation of Liability -->
                <div class="terms-section">
                    <h2><i class="fas fa-exclamation-triangle"></i> حدود المسؤولية</h2>
                    
                    <h3>عدم الضمان</h3>
                    <p>تقدم المنصة "كما هي" و"حسب التوفر". لا تقدم الشركة أي ضمانات، صريحة أو ضمنية، بما في ذلك على سبيل المثال لا الحصر ضمانات الملاءمة لغرض معين أو عدم الانتهاك.</p>
                    
                    <h3>تحديد المسؤولية</h3>
                    <p>لن تكون الشركة مسؤولة عن أي أضرار مباشرة أو غير مباشرة أو تبعية أو عرضية أو خاصة تنشأ عن أو تتعلق باستخدام أو عدم القدرة على استخدام المنصة، حتى إذا كانت الشركة قد أُبلغت بإمكانية حدوث هذه الأضرار.</p>
                    
                    <div class="important-note">
                        <p>في الحد الأقصى الذي يسمح به القانون، تكون المسؤولية الإجمالية للشركة تجاهك عن أي مطالبات متعلقة بهذه الشروط أو استخدام المنصة محدودة بالمبلغ الذي دفعته للشركة، إن وجد.</p>
                    </div>
                </div>

                <!-- Section 8: Termination -->
                <div class="terms-section">
                    <h2><i class="fas fa-ban"></i> الإنهاء</h2>
                    
                    <h3>حقوق الإنهاء</h3>
                    <p>تحتفظ الشركة بالحق في إنهاء أو تعليق وصولك إلى المنصة فورًا، دون إشعار مسبق أو مسؤولية، لأي سبب بما في ذلك على سبيل المثال لا الحصر انتهاك هذه الشروط.</p>
                    
                    <h3>آثار الإنهاء</h3>
                    <p>عند الإنهاء، سينتهي حقك في استخدام المنصة فورًا. إذا كنت ترغب في إنهاء حسابك، يمكنك ببساطة التوقف عن استخدام المنصة.</p>
                </div>

                <!-- Section 9: Governing Law -->
                <div class="terms-section">
                    <h2><i class="fas fa-balance-scale"></i> القانون الحاكم والتسوية</h2>
                    
                    <h3>القانون الحاكم</h3>
                    <p>تخضع هذه الشروط وتفسر وفقًا لقوانين الجمهورية اللبنانية، دون اعتبار لأحكام تنازع القوانين.</p>
                    
                    <h3>تسوية المنازعات</h3>
                    <p>يوافق الطرفان على أن أي نزاع ينشأ عن أو يتعلق بهذه الشروط يجب أن يحاول الطرفان حله ودياً أولاً. في حالة عدم التوصل إلى حل ودي، يحال النزاع إلى المحاكم المختصة في بيروت، لبنان.</p>
                </div>

                <!-- Section 10: Contact Information -->
                <div class="terms-section">
                    <h2><i class="fas fa-headset"></i> معلومات الاتصال</h2>
                    
                    <p>إذا كان لديك أي أسئلة حول هذه الشروط والأحكام، يرجى الاتصال بنا:</p>
                    
                    <ul>
                        <li><strong>البريد الإلكتروني:</strong> legal@diranigroup.com</li>
                        <li><strong>الهاتف:</strong> +961 1 123 456</li>
                        <li><strong>العنوان:</strong> بيروت، شارع الحمراء، لبنان</li>
                        <li><strong>ساعات العمل:</strong> الأحد - الخميس: 8:00 صباحاً - 5:00 مساءً</li>
                    </ul>
                </div>

                <!-- Accept Terms Section -->
                <div class="accept-terms">
                    <p>باستخدامك لمنصة ديراني غروب، فإنك تؤكد أنك قد قرأت وفهمت ووافقت على الالتزام بشروط الاستخدام هذه.</p>
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
                    <a href="privacy.php">
                        <i class="fas fa-shield-alt"></i>
                        سياسة الخصوصية
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
    function printTerms() {
        window.print();
    }
    </script>
</body>
</html>