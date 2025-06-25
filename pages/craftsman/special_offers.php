<?php
require '../db_connection.php';
include "../navBar.php";

// تفعيل عرض الأخطاء
error_reporting(E_ALL);
ini_set('display_errors', 1);

// التحقق من أن المستخدم مسجل دخول وهو حرفي
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'craftsman') {
    header("Location: login.php");
    exit();
}

$craftsman_id = $_SESSION['user_id'];

// جلب تخصص الحرفي من جدول users
$stmt = $conn->prepare("SELECT specialization FROM users WHERE user_id = ?");
$stmt->bind_param("i", $craftsman_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $craftsman = $result->fetch_assoc();
    $specialization = $craftsman['specialization'];
} else {
    header("Location: login.php?error=user_not_found");
    exit();
}
$stmt->close();

// جلب المشاريع التي تتطابق مع تخصص الحرفي
$query = "SELECT project_id, title, description, budget, project_type, location, status, created_at, deadline, company_id 
          FROM projects 
          WHERE title = ? AND status = 'open'
          ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $specialization);
$stmt->execute();
$projectsResult = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>العروض الخاصة</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
  :root {
    --primary-color: #3498db;
    --secondary-color: #2c3e50;
    --accent-color: #e74c3c;
    --dark-bg: #1a1a2e;
    --light-bg: #f8f9fa;
    --gradient-bg: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
  }

  body {
    font-family: 'Tajawal', sans-serif;
    background-color: #f5f7fa;
    color: #333;
  }

  .offers-header {
    background: var(--gradient-bg);
    color: white;
    padding: 5rem 0;
    margin-bottom: 3rem;
    position: relative;
    overflow: hidden;
  }

  .offers-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path fill="rgba(255,255,255,0.05)" d="M0,0 L100,0 L100,100 L0,100 Z" /></svg>');
    background-size: cover;
    opacity: 0.2;
  }

  .offers-title {
    font-weight: 800;
    font-size: 3rem;
    margin-bottom: 1rem;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
  }

  .offers-subtitle {
    font-size: 1.2rem;
    opacity: 0.9;
  }

  .offers-container {
    padding: 0 15px;
  }

  .table {
    background: white;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
  }

  .table th {
    background: var(--primary-color);
    color: white;
    font-weight: 600;
  }

  .table td {
    vertical-align: middle;
  }

  .action-btn {
    margin: 0 5px;
  }

  .apply-btn {
    background: var(--primary-color);
    color: white;
    border: none;
    padding: 5px 15px;
    border-radius: 20px;
    transition: all 0.3s;
  }

  .apply-btn:hover {
    background: #2980b9;
  }

  .empty-offers {
    text-align: center;
    padding: 5rem 0;
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
  }

  .empty-offers i {
    font-size: 4rem;
    color: #ddd;
    margin-bottom: 20px;
  }

  .empty-offers h4 {
    color: #666;
    margin-bottom: 15px;
  }

  .alert-message {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    min-width: 300px;
  }

  @media (max-width: 768px) {
    .offers-title {
      font-size: 2rem;
    }

    .table {
      font-size: 0.9rem;
    }
  }
  </style>
</head>

<body>
  <!-- رسائل التنبيه -->
  <?php if (isset($_GET['applied'])): ?>
  <div class="alert alert-success alert-message alert-dismissible fade show" role="alert">
    تم تقديم عرضك بنجاح
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  <?php elseif (isset($_GET['error'])): ?>
  <div class="alert alert-danger alert-message alert-dismissible fade show" role="alert">
    <?php
    $errors = [
        'not_found' => 'المشروع غير موجود',
        'apply_failed' => 'فشل تقديم العرض'
    ];
    echo $errors[$_GET['error']] ?? 'حدث خطأ غير متوقع';
    ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  <?php endif; ?>

  <!-- رأس الصفحة -->
  <div class="offers-header text-center">
    <div class="container position-relative">
      <h1 class="offers-title">العروض الخاصة</h1>
      <p class="offers-subtitle">المشاريع المتاحة التي تتطابق مع تخصصك (<?= htmlspecialchars($specialization) ?>)</p>
    </div>
  </div>

  <!-- جدول العروض -->
  <div class="container offers-container">
    <?php if ($projectsResult->num_rows > 0): ?>
    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <thead>
          <tr>
            <th>#</th>
            <th>عنوان المشروع</th>
            <th>الوصف</th>
            <th>الميزانية</th>
            <th>نوع المشروع</th>
            <th>الموقع</th>
            <th>الحالة</th>
            <th>تاريخ الإنشاء</th>
            <th>الموعد النهائي</th>
            <th>الإجراءات</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($project = $projectsResult->fetch_assoc()): ?>
          <tr>
            <td><?= $project['project_id'] ?></td>
            <td><?= htmlspecialchars($project['title']) ?></td>
            <td><?= htmlspecialchars($project['description']) ?></td>
            <td><?= $project['budget'] ?></td>
            <td><?= htmlspecialchars($project['project_type']) ?></td>
            <td><?= htmlspecialchars($project['location']) ?></td>
            <td><?= htmlspecialchars($project['status']) ?></td>
            <td><?= date('Y-m-d H:i:s', strtotime($project['created_at'])) ?></td>
            <td><?= $project['deadline'] ?></td>
            <td>
              <a href="apply_job.php?project_id=<?= $project['project_id'] ?>" class="btn apply-btn">
                <i class="fas fa-handshake"></i> تقديم عرض
              </a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
    <div class="empty-offers">
      <i class="fas fa-briefcase"></i>
      <h4>لا توجد مشاريع متاحة تتطابق مع تخصصك</h4>
      <p>سيتم عرض المشاريع الجديدة التي تتطابق مع تخصصك فور توفرها</p>
    </div>
    <?php endif; ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  // إخفاء رسائل التنبيه بعد 5 ثواني
  setTimeout(() => {
    const alerts = document.querySelectorAll('.alert-message');
    alerts.forEach(alert => {
      const bsAlert = new bootstrap.Alert(alert);
      bsAlert.close();
    });
  }, 5000);
  </script>
</body>

</html>