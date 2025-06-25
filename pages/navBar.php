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

  /* تحسين تصميم الـ Dropdown */
  .dropdown-menu {
    min-width: 250px;
    padding: 10px 0;
    border: none;
    border-radius: 15px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    background: linear-gradient(135deg, #ffffff, #f0f4ff);
    font-family: 'Tajawal', sans-serif;
    right: 0 !important;
    left: auto !important;
    transform: translateX(60px);
  }

  .dropdown-item {
    padding: 12px 20px;
    font-size: 16px;
    color: #2d3748;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .dropdown-item:hover {
    background-color: #e0e7ff;
    color: #1e40af;
    transform: translateX(5px);
    border-radius: 10px;
  }

  .dropdown-item i {
    font-size: 18px;
    color: #3b82f6;
    transition: color 0.3s ease;
  }

  .dropdown-item:hover i {
    color: #1e40af;
  }

  .dropdown-toggle {
    background: linear-gradient(90deg, #3b82f6, #1e40af);
    color: white !important;
    border: none;
    padding: 8px 20px;
    border-radius: 10px;
    transition: all 0.3s ease;
  }

  .dropdown-toggle:hover {
    background: linear-gradient(90deg, #1e40af, #1e3a8a);
    transform: scale(1.05);
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
  }

  .dropdown-toggle:focus {
    outline: none;
    box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.5);
  }

  /* فصل الروابط بتاعة الـ admin */
  .admin-links {
    margin-left: auto;
    /* تباعد عن الروابط العادية */
    display: flex;
    gap: 10px;
  }

  .admin-links .nav-link {
    padding: 8px 15px;
    background: #e0e7ff;
    border-radius: 10px;
    transition: all 0.3s ease;
  }

  .admin-links .nav-link:hover {
    background: #d1d9f0;
    color: #1e40af;
    transform: translateY(-2px);
  }

  @media (max-width: 768px) {
    .dropdown-menu {
      min-width: 200px;
      transform: translateX(-10px);
      right: 0 !important;
      left: auto !important;
    }

    .dropdown-item {
      font-size: 14px;
      padding: 10px 15px;
    }

    .dropdown-toggle {
      padding: 6px 15px;
      font-size: 14px;
    }

    .admin-links {
      margin-left: 0;
      /* إزالة التباعد في الموبايل */
      flex-direction: column;
      gap: 5px;
    }

    .admin-links .nav-link {
      padding: 6px 10px;
    }
  }
  </style>
</head>

<body>

  <nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
      <a class="navbar-brand" href="<?= BASE_URL ?>index.php">
        <img src="<?= BASE_URL ?>logo.jpg" alt="Swift Jobs">
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
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
          <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>courses.php">تعلم معنا</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?= BASE_URL ?>jobs_news.php">المستجدات</a>
          </li>
        </ul>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
          <div class="admin-links">
            <a class="nav-link" href="<?= BASE_URL ?>admin/admin_dashboard.php">
              <i class="fas fa-shield-alt me-2"></i> لوحة تحكم الأدمن
            </a>
            <a class="nav-link" href="<?= BASE_URL ?>admin/edit_profile.php">
              <i class="fas fa-user-lock me-2"></i> تعديل الحساب
            </a>
          </div>
        <?php endif; ?>

        <div class="d-flex gap-2">
          <?php if (isset($_SESSION['role'])): ?>
            <?php if ($_SESSION['role'] === 'employee' || $_SESSION['role'] === 'craftsman'): ?>
              <!-- Dropdown للـ Role Access لـ employee و craftsman -->
              <div class="dropdown">
                <button class="btn btn-outline-primary dropdown-toggle" type="button" id="roleDropdown"
                  data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true">
                  <i class="fas fa-user me-1"></i> حسابي
                </button>
                <ul class="dropdown-menu" aria-labelledby="roleDropdown">
                  <?php if ($_SESSION['role'] === 'employee'): ?>
                    <li>
                      <a class="dropdown-item" href="<?= BASE_URL ?>employee/resume.php">
                        <i class="fas fa-file-alt me-2"></i> إدارة السيرة الذاتية
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="<?= BASE_URL ?>employee/myApplications.php">
                        <i class="fas fa-clipboard-list me-2"></i> طلباتي
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="<?= BASE_URL ?>employee/findJobs.php">
                        <i class="fas fa-search-plus me-2"></i> البحث عن وظائف
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="<?= BASE_URL ?>employee/recommended_jobs.php">
                        <i class="fas fa-star me-2"></i> وظائف مخصصة
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="<?= BASE_URL ?>employee/interview_sim.php">
                        <i class="fas fa-user-tie me-2"></i>محاكاة المقابلات
                      </a>
                    <li>
                      <a class="dropdown-item" href="<?= BASE_URL ?>employee/favorite_jobs.php">
                        <i class="fas fa-heart me-2"></i> الوظائف المفضلة
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="<?= BASE_URL ?>employee/compare_jobs.php">
                        <i class="fas fa-balance-scale me-2"></i> مقارنة الوظائف
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="<?= BASE_URL ?>employee/employee_stats.php">
                        <i class="fas fa-chart-bar me-2"></i> إحصائيات الموظف
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="<?= BASE_URL ?>employee/edit_profile.php">
                        <i class="fas fa-user-cog me-2"></i> تعديل الحساب
                      </a>
                    </li>
                  <?php elseif ($_SESSION['role'] === 'craftsman'): ?>
                    <li>
                      <a class="dropdown-item" href="<?= BASE_URL ?>craftsman/special_offers.php">
                        <i class="fas fa-hand-holding-usd me-2"></i> عروضي
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="<?= BASE_URL ?>craftsman/myApplications.php">
                        <i class="fas fa-clipboard-check me-2"></i> طلباتي
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="<?= BASE_URL ?>craftsman/findProjects.php">
                        <i class="fas fa-tools me-2"></i> البحث عن مشاريع
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="<?= BASE_URL ?>craftsman/profile.php">
                        <i class="fas fa-image me-2"></i> معرض أعمالي
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="<?= BASE_URL ?>craftsman/edit_profile.php">
                        <i class="fas fa-user-edit me-2"></i> تعديل الحساب
                      </a>
                    </li>
                  <?php endif; ?>
                </ul>
              </div>
            <?php elseif ($_SESSION['role'] === 'company'): ?>
              <!-- روابط مباشرة لـ company -->
              <a class="btn btn-outline-primary" href="<?= BASE_URL ?>company/company_dashboard.php">
                <i class="fas fa-tachometer-alt me-1"></i> لوحة تحكم الشركة
              </a>
              <a class="btn btn-outline-primary" href="<?= BASE_URL ?>company/edit_profile.php">
                <i class="fas fa-cog me-1"></i> تعديل الحساب
              </a>
            <?php endif; ?>

            <!-- رابط تسجيل الخروج لكل الـ roles -->
            <a class="btn btn-danger" href="<?= BASE_URL ?>logout.php">
              <i class="fas fa-sign-out-alt me-1"></i> تسجيل الخروج
            </a>
          <?php else: ?>
            <!-- أزرار تسجيل الدخول والتسجيل للمستخدم غير المسجل -->
            <a href="<?= BASE_URL ?>login.php" class="btn btn-primary">تسجيل الدخول</a>
            <a href="<?= BASE_URL ?>userSignup.php" class="btn btn-custom-primary">سجل كفرد</a>
            <a href="<?= BASE_URL ?>companiesSignup.php" class="btn btn-custom-primary">سجل كمنشأة</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>

  <!-- تحميل jQuery وBootstrap JavaScript -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const dropdownElements = document.querySelectorAll('.dropdown-toggle');
    dropdownElements.forEach(button => {
      button.addEventListener('click', function(e) {
        e.stopPropagation();
        const dropdown = bootstrap.Dropdown.getOrCreateInstance(this);
        if (dropdown._menu) {
          dropdown.toggle();
        }
      });
    });
  });
  </script>
</body>

</html>