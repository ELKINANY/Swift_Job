<?php
require '../db_connection.php';
session_start();

// التحقق من أن المستخدم مسجل دخول وهو أدمن
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$project_id = $_GET['project_id'] ?? null;
if (!$project_id) {
    $_SESSION['error_message'] = 'رقم المشروع غير محدد';
    header('Location: all_projects.php');
    exit();
}

// جلب تفاصيل المشروع
try {
    $stmt = $conn->prepare("SELECT p.*, c.name AS company_name, c.logo 
                           FROM projects p 
                           LEFT JOIN companies c ON p.company_id = c.company_id 
                           WHERE p.project_id = ?");
    $stmt->execute([$project_id]);
    $project = $stmt->fetch();

    if (!$project) {
        $_SESSION['error_message'] = 'المشروع غير موجود';
        header('Location: all_projects.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'خطأ في جلب بيانات المشروع: ' . $e->getMessage();
    header('Location: all_projects.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>تفاصيل المشروع - لوحة تحكم الأدمن</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
  .project-details {
    max-width: 800px;
    margin: 0 auto;
    padding: 2rem;
    background: #fff;
    border-radius: 1rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  }

  .company-logo {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 50%;
  }

  .badge-open {
    background-color: #28a745;
  }

  .badge-closed {
    background-color: #dc3545;
  }

  .budget {
    font-weight: bold;
    color: #4e73df;
  }
  </style>
</head>

<body>
  <?php include 'includes/admin_header.php'; ?>

  <div class="container mt-5">
    <div class="project-details">
      <h2 class="mb-4">تفاصيل المشروع</h2>

      <?php if (isset($_SESSION['error_message'])): ?>
      <div class="alert alert-danger">
        <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
      </div>
      <?php endif; ?>

      <div class="mb-4">
        <h4>عنوان المشروع:</h4>
        <p><?php echo htmlspecialchars($project['title'] ?? ''); ?></p>
      </div>

      <div class="mb-4">
        <h4>الشركة:</h4>
        <div class="d-flex align-items-center">
          <?php if (!empty($project['logo'])): ?>
          <img src="../<?php echo htmlspecialchars($project['logo']); ?>"
            alt="<?php echo htmlspecialchars($project['company_name'] ?? ''); ?>" class="company-logo me-3">
          <?php endif; ?>
          <span><?php echo htmlspecialchars($project['company_name'] ?? 'غير متوفر'); ?></span>
        </div>
      </div>

      <div class="mb-4">
        <h4>الموقع:</h4>
        <p><?php echo htmlspecialchars($project['location'] ?? 'غير متوفر'); ?></p>
      </div>

      <div class="mb-4">
        <h4>الميزانية:</h4>
        <p class="budget"><?php echo number_format($project['budget'] ?? 0, 2); ?> ج.م</p>
      </div>

      <div class="mb-4">
        <h4>الوصف:</h4>
        <p><?php echo nl2br(htmlspecialchars($project['description'] ?? 'غير متوفر')); ?></p>
      </div>

      <a href="all_projects.php" class="btn btn-secondary">العودة</a>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>