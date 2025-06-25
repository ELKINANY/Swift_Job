<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

require '../db_connection.php';

// جلب عدد الوظائف
$stmt = $conn->prepare("SELECT COUNT(*) AS job_count FROM jobs");
$stmt->execute();
$job_count = $stmt->get_result()->fetch_assoc()['job_count'];

// جلب عدد الشركات
$stmt = $conn->prepare("SELECT COUNT(*) AS company_count FROM companies");
$stmt->execute();
$company_count = $stmt->get_result()->fetch_assoc()['company_count'];

// جلب عدد الباحثين عن العمل
$stmt = $conn->prepare("SELECT COUNT(*) AS job_seeker_count FROM users WHERE role = 'employee'");
$stmt->execute();
$job_seeker_count = $stmt->get_result()->fetch_assoc()['job_seeker_count'];

// جلب عدد المستخدمين الإداريين
$stmt = $conn->prepare("SELECT COUNT(*) AS admin_count FROM users WHERE role = 'admin'");
$stmt->execute();
$admin_count = $stmt->get_result()->fetch_assoc()['admin_count'];

// جلب عدد الوظائف المهنية من جدول projects
$stmt = $conn->prepare("SELECT COUNT(*) AS project_count FROM projects");
$stmt->execute();
$project_count = $stmt->get_result()->fetch_assoc()['project_count'];
?>

<?php include "../navBar.php"; ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>لوحة تحكم الأدمن</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <style>
  body {
    background-color: #f0f4ff;
    font-family: 'Tajawal', sans-serif;
  }

  .dashboard-stats {
    background: linear-gradient(135deg, #3b82f6, #1e40af);
    padding: 3rem 0;
    margin-bottom: 3rem;
  }

  .dashboard-stats h2 {
    color: #ffffff;
    font-weight: 700;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  }

  .stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    min-height: 200px;
    /* ارتفاع ثابت */
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    border: 1px solid #e0e7ff;
  }

  .stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
    background: linear-gradient(135deg, #f0f4ff, #ffffff);
  }

  .stat-card i {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    color: #3b82f6;
    transition: color 0.3s ease;
  }

  .stat-card:hover i {
    color: #1e40af;
  }

  .stat-card h3 {
    font-size: 2rem;
    color: #2d3748;
    margin-bottom: 0.5rem;
  }

  .stat-card p {
    color: #718096;
    font-size: 1.1rem;
    margin: 0;
  }

  .card {
    background: #ffffff;
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    min-height: 220px;
    /* ارتفاع ثابت */
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    border: 1px solid #e0e7ff;
  }

  .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
    background: linear-gradient(135deg, #f0f4ff, #ffffff);
  }

  .card-body i {
    color: #3b82f6;
    transition: color 0.3s ease;
  }

  .card:hover i {
    color: #1e40af;
  }

  .card-title {
    color: #2d3748;
    font-weight: 600;
  }

  .card-text {
    color: #718096;
  }

  /* التمركز في المنتصف */
  .row.g-4 {
    display: flex;
    justify-content: center;
    /* التمركز أفقياً */
    flex-wrap: wrap;
    /* التفاف البطاقات */
    gap: 2rem;
    /* تباعد متساوي */
    padding: 0;
  }

  .row.g-4>div {
    flex: 0 0 auto;
    /* ضبط العرض تلقائياً */
    width: 200px;
    /* عرض ثابت للبطاقات */
  }

  @media (max-width: 768px) {
    .stat-card {
      min-height: 180px;
      padding: 1rem;
      width: 160px;
      /* تقليل العرض في الموبايل */
    }

    .stat-card h3 {
      font-size: 1.5rem;
    }

    .stat-card p {
      font-size: 1rem;
    }

    .card {
      min-height: 200px;
      width: 160px;
      /* تقليل العرض في الموبايل */
    }

    .card-body i {
      font-size: 1.5rem;
    }

    .card-title {
      font-size: 1.2rem;
    }

    .card-text {
      font-size: 0.9rem;
    }

    .row.g-4 {
      gap: 1rem;
    }

    .row.g-4>div {
      width: 160px;
      /* تقليل العرض في الموبايل */
    }
  }
  </style>
</head>

<body>

  <div class="container-fluid px-0">
    <div class="dashboard-stats text-white">
      <div class="container">
        <h2 class="text-center mb-4">لوحة تحكم الأدمن</h2>
        <div class="row g-4">
          <div>
            <div class="stat-card">
              <i class="fas fa-briefcase"></i>
              <h3><?= $job_count; ?></h3>
              <p>الوظائف</p>
            </div>
          </div>
          <div>
            <div class="stat-card">
              <i class="fas fa-building"></i>
              <h3><?= $company_count; ?></h3>
              <p>الشركات</p>
            </div>
          </div>
          <div>
            <div class="stat-card">
              <i class="fas fa-users"></i>
              <h3><?= $job_seeker_count; ?></h3>
              <p>الباحثين عن عمل</p>
            </div>
          </div>
          <div>
            <div class="stat-card">
              <i class="fas fa-tools"></i>
              <h3><?= $project_count; ?></h3>
              <p>الوظائف المهنية</p>
            </div>
          </div>
          <div>
            <div class="stat-card">
              <i class="fas fa-user-shield"></i>
              <h3><?= $admin_count; ?></h3>
              <p>الإداريين</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="container mt-4">
      <div class="row g-4">
        <div>
          <a href="all_jobs.php" class="card text-decoration-none">
            <div class="card-body text-center">
              <i class="fas fa-briefcase fa-2x mb-3"></i>
              <h5 class="card-title">إدارة الوظائف</h5>
              <p class="card-text">عرض وإدارة جميع الوظائف المتاحة</p>
            </div>
          </a>
        </div>
        <div>
          <a href="all_companies.php" class="card text-decoration-none">
            <div class="card-body text-center">
              <i class="fas fa-building fa-2x mb-3"></i>
              <h5 class="card-title">إدارة الشركات</h5>
              <p class="card-text">عرض وإدارة حسابات الشركات</p>
            </div>
          </a>
        </div>
        <div>
          <a href="all_job_seekers.php" class="card text-decoration-none">
            <div class="card-body text-center">
              <i class="fas fa-users fa-2x mb-3"></i>
              <h5 class="card-title">إدارة الباحثين عن عمل</h5>
              <p class="card-text">عرض وإدارة حسابات الباحثين عن عمل</p>
            </div>
          </a>
        </div>
        <div>
          <a href="all_projects.php" class="card text-decoration-none">
            <div class="card-body text-center">
              <i class="fas fa-tools fa-2x mb-3"></i>
              <h5 class="card-title">إدارة الوظائف المهنية</h5>
              <p class="card-text">عرض وإدارة الوظائف المهنية</p>
            </div>
          </a>
        </div>
        <div>
          <a href="all_admins.php" class="card text-decoration-none">
            <div class="card-body text-center">
              <i class="fas fa-user-shield fa-2x mb-3"></i>
              <h5 class="card-title">إدارة المشرفين</h5>
              <p class="card-text">عرض وإدارة حسابات المشرفين</p>
            </div>
          </a>
        </div>
      </div>
    </div>
  </div>

</body>

</html>