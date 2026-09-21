<?php
// contact.php
include 'config.php';

// Start session only if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Handle form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
    $department = filter_input(INPUT_POST, 'department', FILTER_SANITIZE_STRING);
    
    // Basic validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_message = 'الرجاء ملء جميع الحقول الإلزامية (الاسم، البريد الإلكتروني، الموضوع، الرسالة)';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'البريد الإلكتروني غير صالح';
    } else {
        // In a real application, you would:
        // 1. Save to database
        // 2. Send email notification
        // 3. Send auto-reply to user
        
        // For now, just show success message
        $success_message = 'شكراً لتواصلك معنا! سنقوم بالرد على رسالتك في أقرب وقت ممكن.';
        
        // Clear form data
        $name = $email = $phone = $subject = $message = $department = '';
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اتصل بنا - ديراني غروب</title>

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

        /* Contact Content */
        .contact-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 50px;
        }

        @media (max-width: 992px) {
            .contact-content {
                grid-template-columns: 1fr;
            }
        }

        /* Contact Information Cards */
        .contact-info-section {
            background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(255,255,255,0.85));
            border-radius: 25px;
            padding: 40px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
        }

        .contact-info-section h2 {
            color: var(--dark-color);
            font-size: 1.8rem;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            position: relative;
            padding-bottom: 15px;
        }

        .contact-info-section h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 100px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            border-radius: 2px;
        }

        .contact-info-section h2 i {
            color: var(--primary-color);
            background: rgba(67, 97, 238, 0.1);
            padding: 12px;
            border-radius: 12px;
        }

        /* Contact Methods */
        .contact-methods {
            display: grid;
            gap: 25px;
        }

        .contact-method {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            padding: 25px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: var(--transition);
            border: 1px solid rgba(0,0,0,0.05);
        }

        .contact-method:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            border-color: var(--primary-color);
        }

        .contact-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .contact-details h3 {
            color: var(--dark-color);
            margin-bottom: 10px;
            font-size: 1.3rem;
        }

        .contact-details p {
            color: var(--gray-color);
            line-height: 1.7;
            margin-bottom: 5px;
        }

        .contact-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            display: inline-block;
            margin-top: 5px;
        }

        .contact-link:hover {
            color: var(--secondary-color);
            transform: translateX(-5px);
        }

        /* Business Hours */
        .business-hours {
            background: linear-gradient(135deg, rgba(240, 248, 255, 0.9), rgba(230, 240, 255, 0.9));
            border-radius: 20px;
            padding: 25px;
            margin-top: 30px;
            border-right: 5px solid var(--primary-color);
        }

        .business-hours h3 {
            color: var(--primary-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .hours-list {
            display: grid;
            gap: 15px;
        }

        .hour-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            background: white;
            border-radius: 10px;
            transition: var(--transition);
        }

        .hour-item:hover {
            transform: translateX(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .day {
            color: var(--dark-color);
            font-weight: 600;
        }

        .time {
            color: var(--primary-color);
            font-weight: 500;
        }

        /* Contact Form */
        .contact-form-section {
            background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(255,255,255,0.85));
            border-radius: 25px;
            padding: 40px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
        }

        .contact-form-section h2 {
            color: var(--dark-color);
            font-size: 1.8rem;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            position: relative;
            padding-bottom: 15px;
        }

        .contact-form-section h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 100px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            border-radius: 2px;
        }

        .contact-form-section h2 i {
            color: var(--primary-color);
            background: rgba(67, 97, 238, 0.1);
            padding: 12px;
            border-radius: 12px;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--dark-color);
            font-weight: 500;
            font-size: 1rem;
        }

        .form-label .required {
            color: var(--danger-color);
        }

        .form-control {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            transition: var(--transition);
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .form-control.error {
            border-color: var(--danger-color);
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
        }

        .form-select {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            transition: var(--transition);
            background: white;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: left 20px center;
            background-size: 20px;
        }

        .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }

        /* Form Row */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        /* Submit Button */
        .submit-btn {
            width: 100%;
            padding: 18px 30px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(67, 97, 238, 0.3);
        }

        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255,255,255,0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .submit-btn:hover::before {
            width: 300px;
            height: 300px;
        }

        /* Messages */
        .success-message {
            background: linear-gradient(135deg, rgba(46, 204, 113, 0.1), rgba(39, 174, 96, 0.1));
            border: 2px solid var(--success-color);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: center;
            animation: fadeIn 0.5s ease;
        }

        .success-message p {
            color: var(--success-color);
            margin: 0;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 1.1rem;
        }

        .error-message {
            background: linear-gradient(135deg, rgba(231, 76, 60, 0.1), rgba(192, 57, 43, 0.1));
            border: 2px solid var(--danger-color);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: center;
            animation: fadeIn 0.5s ease;
        }

        .error-message p {
            color: var(--danger-color);
            margin: 0;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 1.1rem;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Map Section */
        .map-section {
            margin-top: 50px;
            background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(255,255,255,0.85));
            border-radius: 25px;
            padding: 40px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
        }

        .map-section h2 {
            color: var(--dark-color);
            font-size: 1.8rem;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            position: relative;
            padding-bottom: 15px;
        }

        .map-section h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 100px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            border-radius: 2px;
        }

        .map-section h2 i {
            color: var(--primary-color);
            background: rgba(67, 97, 238, 0.1);
            padding: 12px;
            border-radius: 12px;
        }

        .map-container {
            width: 100%;
            height: 400px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: relative;
        }

        .map-placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            padding: 20px;
        }

        .map-placeholder i {
            font-size: 4rem;
            margin-bottom: 20px;
        }

        .map-placeholder h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .map-placeholder p {
            opacity: 0.9;
            max-width: 500px;
        }

        /* FAQ Section */
        .faq-section {
            margin-top: 50px;
            background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(255,255,255,0.85));
            border-radius: 25px;
            padding: 40px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
        }

        .faq-section h2 {
            color: var(--dark-color);
            font-size: 1.8rem;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            position: relative;
            padding-bottom: 15px;
            text-align: center;
            justify-content: center;
        }

        .faq-section h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 50%;
            transform: translateX(50%);
            width: 100px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            border-radius: 2px;
        }

        .faq-section h2 i {
            color: var(--primary-color);
            background: rgba(67, 97, 238, 0.1);
            padding: 12px;
            border-radius: 12px;
        }

        .faq-list {
            display: grid;
            gap: 20px;
        }

        .faq-item {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: var(--transition);
            border: 1px solid rgba(0,0,0,0.05);
            cursor: pointer;
        }

        .faq-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            border-color: var(--primary-color);
        }

        .faq-question {
            color: var(--dark-color);
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        .faq-question i {
            color: var(--primary-color);
            transition: var(--transition);
        }

        .faq-item.active .faq-question i {
            transform: rotate(180deg);
        }

        .faq-answer {
            color: var(--gray-color);
            line-height: 1.7;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .faq-item.active .faq-answer {
            max-height: 500px;
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
            
            .contact-info-section,
            .contact-form-section,
            .map-section,
            .faq-section {
                padding: 30px 20px;
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
            
            .contact-method {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .contact-icon {
                margin: 0 auto;
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

        .contact-method,
        .faq-item,
        .form-group {
            animation: fadeInUp 0.6s ease forwards;
            opacity: 0;
        }

        .contact-method:nth-child(1) { animation-delay: 0.1s; }
        .contact-method:nth-child(2) { animation-delay: 0.2s; }
        .contact-method:nth-child(3) { animation-delay: 0.3s; }
        .contact-method:nth-child(4) { animation-delay: 0.4s; }
        .faq-item:nth-child(1) { animation-delay: 0.1s; }
        .faq-item:nth-child(2) { animation-delay: 0.2s; }
        .faq-item:nth-child(3) { animation-delay: 0.3s; }
        .faq-item:nth-child(4) { animation-delay: 0.4s; }
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
                    <a href="privacy.php">
                        <i class="fas fa-shield-alt"></i>
                        سياسة الخصوصية
                    </a>
                    <a href="contact.php" class="active">
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
                    <i class="fas fa-envelope"></i>
                    اتصل بنا
                </span>
            </div>

            <!-- Page Header -->
            <div class="page-header">
                <h1>اتصل بنا</h1>
                <p class="hero-subtitle">
                    نحن هنا لمساعدتك! سواء كان لديك سؤال، استفسار، أو ترغب في التعاون معنا، 
                    فريق ديراني غروب جاهز للرد على استفساراتك في أي وقت.
                </p>
            </div>

            <!-- Messages -->
            <?php if ($success_message): ?>
                <div class="success-message">
                    <p><i class="fas fa-check-circle"></i> <?php echo $success_message; ?></p>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="error-message">
                    <p><i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?></p>
                </div>
            <?php endif; ?>

            <!-- Contact Content -->
            <div class="contact-content">
                <!-- Contact Information -->
                <div class="contact-info-section">
                    <h2><i class="fas fa-address-book"></i> معلومات الاتصال</h2>
                    
                    <div class="contact-methods">
                        <div class="contact-method">
                            <div class="contact-icon">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <div class="contact-details">
                                <h3>الهاتف</h3>
                                <p>+961 1 123 456</p>
                                <p>+961 70 123 456</p>
                                <a href="tel:+9611123456" class="contact-link">
                                    <i class="fas fa-phone"></i>
                                    اتصل الآن
                                </a>
                            </div>
                        </div>

                        <div class="contact-method">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-details">
                                <h3>البريد الإلكتروني</h3>
                                <p>info@diranigroup.com</p>
                                <p>support@diranigroup.com</p>
                                <a href="mailto:info@diranigroup.com" class="contact-link">
                                    <i class="fas fa-paper-plane"></i>
                                    أرسل بريدًا إلكترونيًا
                                </a>
                            </div>
                        </div>

                        <div class="contact-method">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-details">
                                <h3>العنوان</h3>
                                <p>بيروت، شارع الحمراء</p>
                                <p>لبنان</p>
                                <a href="#map" class="contact-link">
                                    <i class="fas fa-directions"></i>
                                    اطلع على الخريطة
                                </a>
                            </div>
                        </div>

                        <div class="contact-method">
                            <div class="contact-icon">
                                <i class="fas fa-headset"></i>
                            </div>
                            <div class="contact-details">
                                <h3>الدعم الفني</h3>
                                <p>متاح 24/7 للاستفسارات العاجلة</p>
                                <p>support@diranigroup.com</p>
                                <a href="mailto:support@diranigroup.com" class="contact-link">
                                    <i class="fas fa-life-ring"></i>
                                    احصل على دعم فني
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Business Hours -->
                    <div class="business-hours">
                        <h3><i class="fas fa-clock"></i> ساعات العمل</h3>
                        <div class="hours-list">
                            <div class="hour-item">
                                <span class="day">الأحد - الخميس</span>
                                <span class="time">8:00 صباحاً - 5:00 مساءً</span>
                            </div>
                            <div class="hour-item">
                                <span class="day">الجمعة</span>
                                <span class="time">8:00 صباحاً - 1:00 ظهراً</span>
                            </div>
                            <div class="hour-item">
                                <span class="day">السبت</span>
                                <span class="time">عطلة</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="contact-form-section">
                    <h2><i class="fas fa-comment-dots"></i> أرسل رسالة</h2>
                    
                    <form method="POST" action="" id="contactForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name" class="form-label">
                                    الاسم الكامل <span class="required">*</span>
                                </label>
                                <input type="text" 
                                       id="name" 
                                       name="name" 
                                       class="form-control" 
                                       value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>"
                                       required
                                       placeholder="أدخل اسمك الكامل">
                            </div>

                            <div class="form-group">
                                <label for="email" class="form-label">
                                    البريد الإلكتروني <span class="required">*</span>
                                </label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       class="form-control" 
                                       value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                                       required
                                       placeholder="example@email.com">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone" class="form-label">
                                    رقم الهاتف
                                </label>
                                <input type="tel" 
                                       id="phone" 
                                       name="phone" 
                                       class="form-control" 
                                       value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>"
                                       placeholder="+961 70 123 456">
                            </div>

                            <div class="form-group">
                                <label for="department" class="form-label">
                                    القسم المعني
                                </label>
                                <select id="department" name="department" class="form-select">
                                    <option value="">اختر القسم المناسب</option>
                                    <option value="sales" <?php echo (isset($department) && $department == 'sales') ? 'selected' : ''; ?>>المبيعات</option>
                                    <option value="support" <?php echo (isset($department) && $department == 'support') ? 'selected' : ''; ?>>الدعم الفني</option>
                                    <option value="billing" <?php echo (isset($department) && $department == 'billing') ? 'selected' : ''; ?>>الفواتير والمدفوعات</option>
                                    <option value="partnership" <?php echo (isset($department) && $department == 'partnership') ? 'selected' : ''; ?>>الشراكات والتعاون</option>
                                    <option value="general" <?php echo (isset($department) && $department == 'general') ? 'selected' : ''; ?>>استفسارات عامة</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="subject" class="form-label">
                                موضوع الرسالة <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   id="subject" 
                                   name="subject" 
                                   class="form-control" 
                                   value="<?php echo isset($subject) ? htmlspecialchars($subject) : ''; ?>"
                                   required
                                   placeholder="موضوع رسالتك">
                        </div>

                        <div class="form-group">
                            <label for="message" class="form-label">
                                الرسالة <span class="required">*</span>
                            </label>
                            <textarea id="message" 
                                      name="message" 
                                      class="form-control" 
                                      required
                                      placeholder="اكتب رسالتك هنا..."><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                        </div>

                        <button type="submit" class="submit-btn">
                            <i class="fas fa-paper-plane"></i>
                            إرسال الرسالة
                        </button>
                    </form>
                </div>
            </div>

            <!-- Map Section -->
            <div class="map-section">
                <h2><i class="fas fa-map-marked-alt"></i> موقعنا على الخريطة</h2>
                <div class="map-container">
                    <div class="map-placeholder" id="map">
                        <i class="fas fa-map-marker-alt"></i>
                        <h3>ديراني غروب - بيروت</h3>
                        <p>شارع الحمراء، بيروت، لبنان</p>
                        <p>يمكنك استخدام خرائط جوجل للوصول إلى موقعنا</p>
                    </div>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="faq-section">
                <h2><i class="fas fa-question-circle"></i> الأسئلة الشائعة</h2>
                
                <div class="faq-list">
                    <div class="faq-item" onclick="toggleFAQ(this)">
                        <div class="faq-question">
                            <span>كم من الوقت يستغرق الرد على رسالتي؟</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>نحن نحرص على الرد على جميع الرسائل في غضون 24 ساعة عمل. قد يستغرق الرد وقتاً أطول خلال عطلات نهاية الأسبوع والعطل الرسمية.</p>
                        </div>
                    </div>

                    <div class="faq-item" onclick="toggleFAQ(this)">
                        <div class="faq-question">
                            <span>هل تقدمون دعم فني على مدار الساعة؟</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>نعم، نحن نقدم دعم فني على مدار الساعة للاستفسارات العاجلة عبر البريد الإلكتروني: support@diranigroup.com</p>
                        </div>
                    </div>

                    <div class="faq-item" onclick="toggleFAQ(this)">
                        <div class="faq-question">
                            <span>كيف يمكنني التعاون مع ديراني غروب؟</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>إذا كنت ترغب في التعاون معنا أو عرض منتجاتك على منصتنا، يرجى التواصل مع قسم الشراكات عبر البريد الإلكتروني: partnerships@diranigroup.com</p>
                        </div>
                    </div>

                    <div class="faq-item" onclick="toggleFAQ(this)">
                        <div class="faq-question">
                            <span>هل يمكنني زيارة مقركم؟</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>نعم، يمكنك زيارة مقرنا خلال ساعات العمل الرسمية. يرجى تحديد موعد مسبقاً عبر الهاتف للتأكد من توفر الفريق المناسب للقائك.</p>
                        </div>
                    </div>
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
                    <a href="privacy.php">
                        <i class="fas fa-shield-alt"></i>
                        سياسة الخصوصية
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
    // Form validation
    document.getElementById('contactForm').addEventListener('submit', function(e) {
        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const subject = document.getElementById('subject').value.trim();
        const message = document.getElementById('message').value.trim();
        
        let isValid = true;
        
        // Clear previous errors
        document.querySelectorAll('.form-control').forEach(input => {
            input.classList.remove('error');
        });
        
        // Validate name
        if (!name) {
            document.getElementById('name').classList.add('error');
            isValid = false;
        }
        
        // Validate email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email || !emailRegex.test(email)) {
            document.getElementById('email').classList.add('error');
            isValid = false;
        }
        
        // Validate subject
        if (!subject) {
            document.getElementById('subject').classList.add('error');
            isValid = false;
        }
        
        // Validate message
        if (!message) {
            document.getElementById('message').classList.add('error');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            alert('الرجاء ملء جميع الحقول الإلزامية بشكل صحيح.');
        }
    });

    // FAQ toggle functionality
    function toggleFAQ(item) {
        const currentlyActive = document.querySelector('.faq-item.active');
        
        if (currentlyActive && currentlyActive !== item) {
            currentlyActive.classList.remove('active');
        }
        
        item.classList.toggle('active');
    }

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

    // Auto-resize textarea
    const textarea = document.getElementById('message');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    }
    </script>
</body>
</html>