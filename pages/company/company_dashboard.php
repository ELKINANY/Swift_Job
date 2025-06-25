<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: ../login.php');
  exit();
}

require '../db_connection.php';

$company_id = $_SESSION['user_id'];

// جلب عدد الوظائف
$stmt = $conn->prepare("SELECT COUNT(*) AS job_count FROM jobs WHERE company_id = ?");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$job_result = $stmt->get_result()->fetch_assoc();
$job_count = $job_result['job_count'];
$stmt->close();

// جلب عدد المشاريع
$stmt = $conn->prepare("SELECT COUNT(*) AS project_count FROM projects WHERE company_id = ?");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$project_result = $stmt->get_result()->fetch_assoc();
$project_count = $project_result['project_count'];
$stmt->close();

// جلب عدد التقييمات
$stmt = $conn->prepare("SELECT COUNT(*) AS review_count FROM company_reviews WHERE company_id = ?");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$review_result = $stmt->get_result()->fetch_assoc();
$review_count = $review_result['review_count'];
$stmt->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="ltr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>لوحة تحكم الشركة</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
  :root {
    --primary: #1e3a8a;
    --secondary: #3b82f6;
    --accent: #60a5fa;
    --dark: #1f2937;
    --light: #f9fafb;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
  }

  body {
    font-family: 'Tajawal', sans-serif;
    background-color: var(--light);
    margin: 0;
    padding: 0;
    color: var(--dark);
  }

  .dashboard-layout {
    display: flex;
    min-height: 100vh;
  }

  .main-content {
    flex: 1;
    padding: 2rem;
    transition: all 0.3s ease;
  }

  /* Welcome Card */
  .welcome-card {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    color: white;
    border-radius: 1rem;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    position: relative;
    overflow: hidden;
    animation: slideIn 0.5s ease-out;
  }

  @keyframes slideIn {
    from {
      transform: translateY(20px);
      opacity: 0;
    }

    to {
      transform: translateY(0);
      opacity: 1;
    }
  }

  .welcome-card h1 {
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0 0 0.5rem;
  }

  .welcome-card p {
    font-size: 1rem;
    opacity: 0.85;
    margin: 0;
  }

  .welcome-card .wave {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 80px;
    background: url('data:image/svg+xml;utf8,<svg viewBox="0 0 1200 120" xmlns="http://www.w3.org/2000/svg"><path d="M0,0V46c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" fill="rgba(255,255,255,0.15)"/></svg>');
    background-size: cover;
  }

  /* Stats Grid */
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
  }

  .stat-card {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
  }

  .stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
  }

  .stat-card .icon {
    font-size: 2.5rem;
    color: var(--primary);
    margin-bottom: 1rem;
  }

  .stat-card .value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 0.5rem;
  }

  .stat-card .label {
    font-size: 1rem;
    color: #6b7280;
  }

  .stat-card .progress-circle {
    width: 60px;
    height: 60px;
    margin: 0 auto 1rem;
    position: relative;
  }

  .stat-card .progress-circle svg {
    width: 100%;
    height: 100%;
    transform: rotate(-90deg);
  }

  .stat-card .progress-circle circle {
    fill: none;
    stroke-width: 6;
  }

  .stat-card .progress-circle .bg {
    stroke: #e5e7eb;
  }

  .stat-card .progress-circle .fg {
    stroke: var(--primary);
    stroke-dasharray: 188;
    stroke-dashoffset: calc(188 - (188 * var(--progress)) / 100);
  }

  /* Quick Actions */
  .quick-actions {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  }

  .quick-actions h3 {
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0 0 1.5rem;
  }

  .action-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1rem;
  }

  .action-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    background: var(--light);
    border-radius: 0.75rem;
    padding: 1.5rem;
    text-decoration: none;
    color: var(--dark);
    transition: all 0.3s ease;
    border: 1px solid #e5e7eb;
  }

  .action-item:hover {
    background: white;
    border-color: var(--secondary);
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
  }

  .action-item i {
    font-size: 1.8rem;
    color: var(--secondary);
    margin-bottom: 0.75rem;
  }

  .action-item .title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
  }

  .action-item .desc {
    font-size: 0.9rem;
    color: #6b7280;
    text-align: center;
  }

  /* Responsive Design */
  @media (max-width: 992px) {
    .dashboard-layout {
      flex-direction: column;
    }

    .main-content {
      padding: 1.5rem;
    }
  }

  @media (max-width: 768px) {
    .welcome-card h1 {
      font-size: 1.5rem;
    }

    .welcome-card p {
      font-size: 0.9rem;
    }

    .stats-grid {
      grid-template-columns: 1fr;
    }

    .action-grid {
      grid-template-columns: 1fr;
    }
  }
  </style>
</head>

<body>
  <div class="dashboard-layout">
    <!-- الشريط الجانبي -->
    <?php include "./sidebar.php" ?>
    <!-- المحتوى الرئيسي -->
    <div class="main-content">
      <!-- بطاقة الترحيب -->
      <div class="welcome-card">
        <h1>مرحبًا بك في لوحة التحكم</h1>
        <p>إدارة شركتك بكفاءة وسهولة من مكان واحد</p>
        <div class="wave"></div>
      </div>

      <!-- إحصائيات -->
      <div class="stats-grid">
        <div class="stat-card">
          <i class="fas fa-briefcase icon"></i>
          <div class="progress-circle">
            <svg>
              <circle class="bg" cx="30" cy="30" r="27"></circle>
              <circle class="fg" cx="30" cy="30" r="27" style="--progress: <?php echo min($job_count * 10, 100); ?>">
              </circle>
            </svg>
          </div>
          <div class="value"><?php echo $job_count; ?></div>
          <div class="label">الوظائف المنشورة</div>
        </div>
        <div class="stat-card">
          <i class="fas fa-hammer icon"></i>
          <div class="progress-circle">
            <svg>
              <circle class="bg" cx="30" cy="30" r="27"></circle>
              <circle class="fg" cx="30" cy="30" r="27"
                style="--progress: <?php echo min($project_count * 10, 100); ?>"></circle>
            </svg>
          </div>
          <div class="value"><?php echo $project_count; ?></div>
          <div class="label">الحرف المنشورة</div>
        </div>
        <div class="stat-card">
          <i class="fas fa-star icon"></i>
          <div class="progress-circle">
            <svg>
              <circle class="bg" cx="30" cy="30" r="27"></circle>
              <circle class="fg" cx="30" cy="30" r="27" style="--progress: <?php echo min($review_count * 10, 100); ?>">
              </circle>
            </svg>
          </div>
          <div class="value"><?php echo $review_count; ?></div>
          <div class="label">التقييمات</div>
        </div>
      </div>

      <!-- إجراءات سريعة -->
      <div class="quick-actions">
        <h3>إجراءات سريعة</h3>
        <div class="action-grid">
          <a href="add_job.php" class="action-item">
            <i class="fas fa-plus-circle"></i>
            <div class="title">إضافة وظيفة</div>
            <div class="desc">نشر وظيفة جديدة للباحثين</div>
          </a>
          <a href="view_jobs.php" class="action-item">
            <i class="fas fa-tasks"></i>
            <div class="title">إدارة الوظائف</div>
            <div class="desc">عرض وتعديل الوظائف المنشورة</div>
          </a>
          <a href="add_project.php" class="action-item">
            <i class="fas fa-briefcase"></i>
            <div class="title">إضافة حرفه</div>
            <div class="desc">إنشاء تصنيف مهني جديد</div>
          </a>
          <a href="view_projects.php" class="action-item">
            <i class="fas fa-project-diagram"></i>
            <div class="title">إدارة الحرف</div>
            <div class="desc">عرض وتعديل الحرف المنشورة</div>
          </a>
        </div>
      </div>
    </div>
  </div>

  <script>
  // JavaScript لتفعيل أي تفاعلات إضافية إذا لزم الأمر
  document.addEventListener('DOMContentLoaded', () => {
    // يمكن إضافة تفاعلات JavaScript هنا
  });
  </script>
</body>

</html>