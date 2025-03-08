<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php'; // استدعاء `BASE_URL`
?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Swift Jobs</title>
    <link rel="shortcut icon" href="<?= BASE_URL ?>logo.jpg" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>CSS/main.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>CSS/login.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>CSS/userSignup.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        .navbar {
            background-color: #f8f9fa;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
        }
        .navbar-brand img {
            height: 70px;
        }
        .nav-link {
            font-weight: 500;
            color: #333;
            transition: 0.3s;
        }
        .nav-link:hover {
            color: #007bff;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= BASE_URL ?>index.php">
            <img src="<?= BASE_URL ?>logo.jpg" alt="Swift Jobs">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2">
                <li class="nav-item">
                    <a class="nav-link active" href="<?= BASE_URL ?>index.php">الرئيسية</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>about.php">من نحن</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>faq.php">الأسئلة الشائعة</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>contact.php">اتصل بنا</a>
                </li>
                <!-- <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>employee/search_companies.php">
                        <i class="fas fa-building"></i> البحث عن الشركات
                    </a> -->
                </li>
            </ul>

            <div class="d-flex gap-2">
                <?php if (isset($_SESSION['role'])): ?>
                    <?php if ($_SESSION['role'] === 'employee'): ?>
                      
                        <a href="<?= BASE_URL ?>employee/myApplications.php" class="btn btn-outline-primary">   
                            <i class="fas fa-briefcase"></i> طلباتي
                        </a>
                        <a href="<?= BASE_URL ?>employee/findJobs.php" class="btn btn-outline-secondary">
                            <i class="fas fa-briefcase"></i> البحث عن وظائف
                        </a>
                        <a href="<?= BASE_URL ?>employee/recommended_jobs.php" class="btn btn-outline-success">
                            <i class="fas fa-briefcase"></i> وظائف مخصصة
                        </a>
                        <a href="<?= BASE_URL ?>employee/edit_profile.php" class="btn btn-outline-info">
                            <i class="fas fa-user-edit"></i> تعديل الحساب
                        </a>
                  
                
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>logout.php" class="btn btn-danger">
                        <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>login.php" class="btn btn-primary">تسجيل الدخول</a>
                    <a href="<?= BASE_URL ?>userSignup.php" class="btn btn-custom-primary">سجل كفرد</a>
                    <a href="<?= BASE_URL ?>companiesSignup.php" class="btn btn-custom-primary">سجل كمنشأة</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
