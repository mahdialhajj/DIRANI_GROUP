<?php
// sitemap.php
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
    <title>خريطة الموقع - ديراني غروب</title>

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

        /* Sitemap Content */
        .sitemap-content {
            background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(255,255,255,0.85));
            border-radius: 25px;
            padding: 50px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
            margin-bottom: 40px;
        }

        /* Sitemap Sections */
        .sitemap-section {
            margin-bottom: 50px;
            padding-bottom: 30px;
            border-bottom: 2px solid rgba(67, 97, 238, 0.1);
        }

        .sitemap-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .sitemap-section h2 {
            color: var(--dark-color);
            font-size: 1.8rem;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            position: relative;
            padding-bottom: 15px;
        }

        .sitemap-section h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 100px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            border-radius: 2px;
        }

        .sitemap-section h2 i {
            color: var(--primary-color);
            background: rgba(67, 97, 238, 0.1);
            padding: 12px;
            border-radius: 12px;
        }

        /* Sitemap Grid */
        .sitemap-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        /* Sitemap Card */
        .sitemap-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            transition: var(--transition);
            border: 1px solid rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }

        .sitemap-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
        }

        .sitemap-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            border-color: var(--primary-color);
        }

        .sitemap-card h3 {
            color: var(--dark-color);
            font-size: 1.4rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sitemap-card h3 i {
            color: var(--primary-color);
            background: rgba(67, 97, 238, 0.1);
            padding: 10px;
            border-radius: 10px;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sitemap-links {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .sitemap-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            background: var(--light-color);
            border-radius: 12px;
            color: var(--dark-color);
            text-decoration: none;
            transition: var(--transition);
            border: 1px solid rgba(0,0,0,0.05);
            position: relative;
        }

        .sitemap-link::before {
            content: '→';
            position: absolute;
            left: 15px;
            opacity: 0;
            transition: var(--transition);
            color: var(--primary-color);
        }

        .sitemap-link:hover {
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.1), rgba(115, 9, 183, 0.05));
            transform: translateX(-10px);
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .sitemap-link:hover::before {
            opacity: 1;
            transform: translateX(-5px);
        }

        .sitemap-link i {
            color: var(--primary-color);
            width: 20px;
            font-size: 1.1rem;
        }

        .sitemap-link span {
            flex: 1;
            font-weight: 500;
        }

        .sitemap-link .badge {
            background: linear-gradient(135deg, var(--success-color), #27ae60);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        /* Search Section */
        .search-section {
            background: linear-gradient(135deg, rgba(240, 248, 255, 0.9), rgba(230, 240, 255, 0.9));
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            margin-bottom: 50px;
            border: 2px solid rgba(67, 97, 238, 0.2);
        }

        .search-section h2 {
            color: var(--dark-color);
            font-size: 1.8rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .search-section h2 i {
            color: var(--primary-color);
        }

        .search-box {
            max-width: 600px;
            margin: 0 auto;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 18px 60px 18px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 15px;
            font-size: 1.1rem;
            transition: var(--transition);
            background: white;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .search-button {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            font-size: 1.2rem;
        }

        .search-button:hover {
            transform: translateY(-50%) scale(1.1);
            box-shadow: 0 8px 20px rgba(67, 97, 238, 0.4);
        }

        /* XML Sitemap Section */
        .xml-section {
            background: linear-gradient(135deg, rgba(220, 247, 233, 0.9), rgba(209, 242, 225, 0.9));
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            margin-top: 40px;
            border: 2px solid rgba(46, 204, 113, 0.3);
        }

        .xml-section h3 {
            color: var(--success-color);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 1.3rem;
        }

        .xml-section p {
            color: var(--dark-color);
            margin-bottom: 20px;
        }

        .xml-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, var(--success-color), #27ae60);
            color: white;
            padding: 12px 25px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .xml-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(46, 204, 113, 0.4);
        }

        /* Quick Actions */
        .quick-actions {
            display: flex;
            gap: 20px;
            margin-top: 30px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 25px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            background: var(--light-color);
            color: var(--dark-color);
            border: 2px solid transparent;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .action-btn.print {
            background: linear-gradient(135deg, var(--warning-color), #e67e22);
            color: white;
        }

        .action-btn.download {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
        }

        .action-btn.back {
            background: linear-gradient(135deg, var(--gray-color), #5a6268);
            color: white;
        }

        /* Statistics */
        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 40px;
        }

        .stat-item {
            background: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.05);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--gray-color);
            font-size: 0.9rem;
        }

        /* Last Updated */
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
            .sitemap-content {
                padding: 40px 30px;
            }
            
            .page-header h1 {
                font-size: 2.2rem;
            }
            
            .sitemap-grid {
                grid-template-columns: repeat(2, 1fr);
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
            
            .sitemap-content {
                padding: 30px 20px;
            }
            
            .sitemap-grid {
                grid-template-columns: 1fr;
            }
            
            .sitemap-section h2 {
                font-size: 1.5rem;
            }
            
            .search-section {
                padding: 30px 20px;
            }
            
            .footer-container {
                grid-template-columns: 1fr;
                gap: 25px;
            }
            
            .footer-section {
                padding: 25px;
            }
            
            .quick-actions {
                flex-direction: column;
            }
            
            .action-btn {
                width: 100%;
                justify-content: center;
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
            
            .sitemap-card {
                padding: 25px;
            }
            
            .sitemap-card h3 {
                font-size: 1.2rem;
            }
            
            .stats-section {
                grid-template-columns: 1fr;
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

        .sitemap-card {
            animation: fadeInUp 0.6s ease forwards;
            opacity: 0;
        }

        .sitemap-card:nth-child(1) { animation-delay: 0.1s; }
        .sitemap-card:nth-child(2) { animation-delay: 0.2s; }
        .sitemap-card:nth-child(3) { animation-delay: 0.3s; }
        .sitemap-card:nth-child(4) { animation-delay: 0.4s; }
        .sitemap-card:nth-child(5) { animation-delay: 0.5s; }
        .sitemap-card:nth-child(6) { animation-delay: 0.6s; }

        /* Print Styles */
        @media print {
            .main-header,
            .breadcrumb,
            .search-section,
            .quick-actions,
            .main-footer {
                display: none;
            }
            
            .sitemap-content {
                box-shadow: none;
                border: 1px solid #ddd;
            }
            
            .sitemap-card {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
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
                    <a href="sitemap.php" class="active">
                        <i class="fas fa-sitemap"></i>
                        خريطة الموقع
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
                    <i class="fas fa-sitemap"></i>
                    خريطة الموقع
                </span>
            </div>

            <!-- Page Header -->
            <div class="page-header">
                <h1>خريطة الموقع</h1>
                <p class="hero-subtitle">
                    استكشف جميع صفحات وروابط موقع ديراني غروب بسهولة. تساعدك خريطة الموقع هذه 
                    في العثور على المحتوى الذي تبحث عنه بسرعة وكفاءة.
                </p>
            </div>

            <!-- Search Section -->
            <div class="search-section">
                <h2><i class="fas fa-search"></i> ابحث في الموقع</h2>
                <div class="search-box">
                    <input type="text" class="search-input" placeholder="ابحث عن صفحة معينة..." id="siteSearch">
                    <button class="search-button" onclick="searchSite()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>

            <!-- Last Updated -->
            <div class="last-updated">
                <p>
                    <i class="fas fa-calendar-alt"></i>
                    آخر تحديث للخريطة: ١ يناير ٢٠٢٥
                </p>
            </div>

            <!-- Sitemap Content -->
            <div class="sitemap-content">
                <!-- Main Pages Section -->
                <div class="sitemap-section">
                    <h2><i class="fas fa-home"></i> الصفحات الرئيسية</h2>
                    <div class="sitemap-grid">
                        <div class="sitemap-card">
                            <h3><i class="fas fa-home"></i> الصفحة الرئيسية</h3>
                            <div class="sitemap-links">
                                <a href="index.php" class="sitemap-link">
                                    <i class="fas fa-home"></i>
                                    <span>الرئيسية</span>
                                    <span class="badge">الصفحة الأساسية</span>
                                </a>
                                <a href="index.php#about" class="sitemap-link">
                                    <i class="fas fa-info-circle"></i>
                                    <span>عن ديراني غروب</span>
                                </a>
                                <a href="index.php#features" class="sitemap-link">
                                    <i class="fas fa-star"></i>
                                    <span>المميزات</span>
                                </a>
                                <a href="index.php#statistics" class="sitemap-link">
                                    <i class="fas fa-chart-bar"></i>
                                    <span>الإحصائيات</span>
                                </a>
                            </div>
                        </div>

                        <div class="sitemap-card">
                            <h3><i class="fas fa-th-large"></i> المنتجات والأصناف</h3>
                            <div class="sitemap-links">
                                <a href="all_categories.php" class="sitemap-link">
                                    <i class="fas fa-layer-group"></i>
                                    <span>جميع الأصناف</span>
                                    <span class="badge">رئيسي</span>
                                </a>
                                <a href="all_categories.php?company_id=1" class="sitemap-link">
                                    <i class="fas fa-building"></i>
                                    <span>منتجات الشركات</span>
                                </a>
                                <a href="#" class="sitemap-link">
                                    <i class="fas fa-search"></i>
                                    <span>البحث المتقدم</span>
                                </a>
                                <a href="#" class="sitemap-link">
                                    <i class="fas fa-filter"></i>
                                    <span>التصنيف حسب النوع</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Company & Categories Section -->
                <div class="sitemap-section">
                    <h2><i class="fas fa-building"></i> الشركات والأصناف</h2>
                    <div class="sitemap-grid">
                        <div class="sitemap-card">
                            <h3><i class="fas fa-store"></i> الشركات</h3>
                            <div class="sitemap-links">
                                <a href="company_profile.php?id=1" class="sitemap-link">
                                    <i class="fas fa-building"></i>
                                    <span>الشركة ١</span>
                                </a>
                                <a href="company_profile.php?id=2" class="sitemap-link">
                                    <i class="fas fa-building"></i>
                                    <span>الشركة ٢</span>
                                </a>
                                <a href="company_profile.php?id=3" class="sitemap-link">
                                    <i class="fas fa-building"></i>
                                    <span>الشركة ٣</span>
                                </a>
                                <a href="#" class="sitemap-link">
                                    <i class="fas fa-list"></i>
                                    <span>جميع الشركات</span>
                                </a>
                            </div>
                        </div>

                        <div class="sitemap-card">
                            <h3><i class="fas fa-boxes"></i> الأصناف الرئيسية</h3>
                            <div class="sitemap-links">
                                <a href="category_products.php?main_category_id=1" class="sitemap-link">
                                    <i class="fas fa-box"></i>
                                    <span>الإلكترونيات</span>
                                </a>
                                <a href="category_products.php?main_category_id=2" class="sitemap-link">
                                    <i class="fas fa-tshirt"></i>
                                    <span>الملابس</span>
                                </a>
                                <a href="category_products.php?main_category_id=3" class="sitemap-link">
                                    <i class="fas fa-utensils"></i>
                                    <span>المواد الغذائية</span>
                                </a>
                                <a href="category_products.php?main_category_id=4" class="sitemap-link">
                                    <i class="fas fa-home"></i>
                                    <span>الأثاث</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Legal & Information Section -->
                <div class="sitemap-section">
                    <h2><i class="fas fa-info-circle"></i> المعلومات القانونية</h2>
                    <div class="sitemap-grid">
                        <div class="sitemap-card">
                            <h3><i class="fas fa-file-contract"></i> الشروط والسياسات</h3>
                            <div class="sitemap-links">
                                <a href="terms.php" class="sitemap-link">
                                    <i class="fas fa-file-contract"></i>
                                    <span>شروط الاستخدام</span>
                                    <span class="badge">هام</span>
                                </a>
                                <a href="privacy.php" class="sitemap-link">
                                    <i class="fas fa-shield-alt"></i>
                                    <span>سياسة الخصوصية</span>
                                    <span class="badge">هام</span>
                                </a>
                                <a href="#" class="sitemap-link">
                                    <i class="fas fa-cookie-bite"></i>
                                    <span>سياسة ملفات تعريف الارتباط</span>
                                </a>
                                <a href="#" class="sitemap-link">
                                    <i class="fas fa-gavel"></i>
                                    <span>الشروط والأحكام</span>
                                </a>
                            </div>
                        </div>

                        <div class="sitemap-card">
                            <h3><i class="fas fa-question-circle"></i> المساعدة والدعم</h3>
                            <div class="sitemap-links">
                                <a href="contact.php" class="sitemap-link">
                                    <i class="fas fa-envelope"></i>
                                    <span>اتصل بنا</span>
                                    <span class="badge">دعم</span>
                                </a>
                                <a href="faq.php" class="sitemap-link">
                                    <i class="fas fa-question-circle"></i>
                                    <span>الأسئلة الشائعة</span>
                                </a>
                                <a href="help.php" class="sitemap-link">
                                    <i class="fas fa-life-ring"></i>
                                    <span>مركز المساعدة</span>
                                </a>
                                <a href="tutorials.php" class="sitemap-link">
                                    <i class="fas fa-graduation-cap"></i>
                                    <span>الدروس التعليمية</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Account Section -->
                <div class="sitemap-section">
                    <h2><i class="fas fa-user-circle"></i> حساب المستخدم</h2>
                    <div class="sitemap-grid">
                        <div class="sitemap-card">
                            <h3><i class="fas fa-sign-in-alt"></i> تسجيل الدخول</h3>
                            <div class="sitemap-links">
                                <a href="login.php" class="sitemap-link">
                                    <i class="fas fa-sign-in-alt"></i>
                                    <span>تسجيل الدخول</span>
                                </a>
                                <a href="register.php" class="sitemap-link">
                                    <i class="fas fa-user-plus"></i>
                                    <span>إنشاء حساب جديد</span>
                                </a>
                                <a href="forgot_password.php" class="sitemap-link">
                                    <i class="fas fa-key"></i>
                                    <span>نسيت كلمة المرور</span>
                                </a>
                                <a href="reset_password.php" class="sitemap-link">
                                    <i class="fas fa-redo"></i>
                                    <span>إعادة تعيين كلمة المرور</span>
                                </a>
                            </div>
                        </div>

                        <div class="sitemap-card">
                            <h3><i class="fas fa-user-cog"></i> إدارة الحساب</h3>
                            <div class="sitemap-links">
                                <a href="dashboard.php" class="sitemap-link">
                                    <i class="fas fa-tachometer-alt"></i>
                                    <span>لوحة التحكم</span>
                                </a>
                                <a href="profile.php" class="sitemap-link">
                                    <i class="fas fa-user-edit"></i>
                                    <span>تعديل الملف الشخصي</span>
                                </a>
                                <a href="orders.php" class="sitemap-link">
                                    <i class="fas fa-shopping-cart"></i>
                                    <span>الطلبات السابقة</span>
                                </a>
                                <a href="settings.php" class="sitemap-link">
                                    <i class="fas fa-cog"></i>
                                    <span>الإعدادات</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Business Information Section -->
                <div class="sitemap-section">
                    <h2><i class="fas fa-info"></i> معلومات الشركة</h2>
                    <div class="sitemap-grid">
                        <div class="sitemap-card">
                            <h3><i class="fas fa-building"></i> عن الشركة</h3>
                            <div class="sitemap-links">
                                <a href="about.php" class="sitemap-link">
                                    <i class="fas fa-info-circle"></i>
                                    <span>عن ديراني غروب</span>
                                </a>
                                <a href="mission.php" class="sitemap-link">
                                    <i class="fas fa-bullseye"></i>
                                    <span>الرؤية والرسالة</span>
                                </a>
                                <a href="team.php" class="sitemap-link">
                                    <i class="fas fa-users"></i>
                                    <span>فريق العمل</span>
                                </a>
                                <a href="careers.php" class="sitemap-link">
                                    <i class="fas fa-briefcase"></i>
                                    <span>الوظائف</span>
                                </a>
                            </div>
                        </div>

                        <div class="sitemap-card">
                            <h3><i class="fas fa-handshake"></i> الشركاء والتعاون</h3>
                            <div class="sitemap-links">
                                <a href="partners.php" class="sitemap-link">
                                    <i class="fas fa-handshake"></i>
                                    <span>شركاؤنا</span>
                                </a>
                                <a href="affiliate.php" class="sitemap-link">
                                    <i class="fas fa-link"></i>
                                    <span>برنامج الشركاء</span>
                                </a>
                                <a href="collaboration.php" class="sitemap-link">
                                    <i class="fas fa-users-cog"></i>
                                    <span>فرص التعاون</span>
                                </a>
                                <a href="become_partner.php" class="sitemap-link">
                                    <i class="fas fa-user-plus"></i>
                                    <span>انضم إلينا</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resources Section -->
                <div class="sitemap-section">
                    <h2><i class="fas fa-book"></i> المصادر والموارد</h2>
                    <div class="sitemap-grid">
                        <div class="sitemap-card">
                            <h3><i class="fas fa-newspaper"></i> المدونة والأخبار</h3>
                            <div class="sitemap-links">
                                <a href="blog.php" class="sitemap-link">
                                    <i class="fas fa-newspaper"></i>
                                    <span>المدونة</span>
                                </a>
                                <a href="news.php" class="sitemap-link">
                                    <i class="fas fa-bullhorn"></i>
                                    <span>الأخبار</span>
                                </a>
                                <a href="articles.php" class="sitemap-link">
                                    <i class="fas fa-file-alt"></i>
                                    <span>المقالات</span>
                                </a>
                                <a href="press.php" class="sitemap-link">
                                    <i class="fas fa-microphone"></i>
                                    <span>البيانات الصحفية</span>
                                </a>
                            </div>
                        </div>

                        <div class="sitemap-card">
                            <h3><i class="fas fa-download"></i> التنزيلات</h3>
                            <div class="sitemap-links">
                                <a href="catalog.php" class="sitemap-link">
                                    <i class="fas fa-book"></i>
                                    <span>كتالوج المنتجات</span>
                                </a>
                                <a href="brochures.php" class="sitemap-link">
                                    <i class="fas fa-file-pdf"></i>
                                    <span>الكتيبات</span>
                                </a>
                                <a href="manuals.php" class="sitemap-link">
                                    <i class="fas fa-book-open"></i>
                                    <span>الدليل الإرشادي</span>
                                </a>
                                <a href="resources.php" class="sitemap-link">
                                    <i class="fas fa-folder-open"></i>
                                    <span>المصادر المجانية</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Section -->
                <div class="stats-section">
                    <div class="stat-item">
                        <div class="stat-number">45+</div>
                        <div class="stat-label">صفحة رئيسية</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">12+</div>
                        <div class="stat-label">قسم رئيسي</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">150+</div>
                        <div class="stat-label">رابط نشط</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">100%</div>
                        <div class="stat-label">روابط صالحة</div>
                    </div>
                </div>

                <!-- XML Sitemap Section -->
                <div class="xml-section">
                    <h3><i class="fas fa-code"></i> خريطة الموقع التقنية</h3>
                    <p>للحصول على خريطة الموقع بتنسيق XML للمحركات البحثية:</p>
                    <a href="sitemap.xml" class="xml-link" target="_blank">
                        <i class="fas fa-download"></i>
                        تحميل خريطة الموقع (XML)
                    </a>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <button onclick="window.print()" class="action-btn print">
                    <i class="fas fa-print"></i>
                    طباعة خريطة الموقع
                </button>
                <button onclick="downloadSitemap()" class="action-btn download">
                    <i class="fas fa-download"></i>
                    تحميل كملف PDF
                </button>
                <a href="index.php" class="action-btn back">
                    <i class="fas fa-arrow-right"></i>
                    العودة للرئيسية
                </a>
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
                    <a href="sitemap.php">
                        <i class="fas fa-sitemap"></i>
                        خريطة الموقع
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
    // Search functionality
    function searchSite() {
        const searchTerm = document.getElementById('siteSearch').value.toLowerCase().trim();
        if (!searchTerm) {
            alert('الرجاء إدخال مصطلح البحث');
            return;
        }
        
        // Get all sitemap links
        const allLinks = document.querySelectorAll('.sitemap-link');
        let foundLinks = [];
        
        // Search in all links
        allLinks.forEach(link => {
            const linkText = link.querySelector('span').textContent.toLowerCase();
            const linkHref = link.getAttribute('href');
            
            if (linkText.includes(searchTerm) || linkHref.includes(searchTerm)) {
                foundLinks.push(link);
            }
        });
        
        if (foundLinks.length > 0) {
            // Highlight found links
            allLinks.forEach(link => link.style.backgroundColor = '');
            foundLinks.forEach(link => {
                link.style.backgroundColor = 'rgba(67, 97, 238, 0.1)';
                link.style.borderColor = 'var(--primary-color)';
                
                // Scroll to first found link
                link.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            });
            
            alert(`تم العثور على ${foundLinks.length} نتيجة لبحثك عن "${searchTerm}"`);
        } else {
            alert(`لم يتم العثور على نتائج لبحثك عن "${searchTerm}"`);
        }
    }

    // Download sitemap as PDF (simulated)
    function downloadSitemap() {
        alert('سيتم تحميل خريطة الموقع كملف PDF. هذه ميزة تجريبية.');
        // In a real implementation, this would generate and download a PDF
        // window.location.href = 'generate_sitemap_pdf.php';
    }

    // Print optimized sitemap
    function printSitemap() {
        window.print();
    }

    // Clear search highlights
    function clearSearch() {
        const allLinks = document.querySelectorAll('.sitemap-link');
        allLinks.forEach(link => {
            link.style.backgroundColor = '';
            link.style.borderColor = '';
        });
        document.getElementById('siteSearch').value = '';
    }

    // Enter key support for search
    document.getElementById('siteSearch').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchSite();
        }
    });

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

    // Collapse/expand sections (optional feature)
    function toggleSection(sectionId) {
        const section = document.getElementById(sectionId);
        if (section) {
            section.classList.toggle('collapsed');
        }
    }
    </script>
</body>
</html>