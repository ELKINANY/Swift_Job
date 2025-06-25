<?php
require '../db_connection.php';
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$company_id = $_GET['company_id'] ?? null;
if (!$company_id) {
    $_SESSION['error_message'] = 'رقم الشركة غير محدد';
    header('Location: all_companies.php');
    exit();
}

// Fetch company details
try {
    $stmt = $conn->prepare("SELECT c.*, 
                           COUNT(j.job_id) as total_jobs,
                           COUNT(DISTINCT ja.user_id) as unique_applicants,
                           COUNT(ja.application_id) as total_applications,
                           COUNT(p.project_id) as total_projects
                           FROM companies c
                           LEFT JOIN jobs j ON c.company_id = j.company_id
                           LEFT JOIN job_applications ja ON j.job_id = ja.job_id
                           LEFT JOIN projects p ON c.company_id = p.company_id
                           WHERE c.company_id = ?
                           GROUP BY c.company_id");
    $stmt->bind_param("i", $company_id);
    $stmt->execute();
    $company = $stmt->get_result()->fetch_assoc();

    if (!$company) {
        $_SESSION['error_message'] = 'الشركة غير موجودة';
        header('Location: all_companies.php');
        exit();
    }
} catch (mysqli_sql_exception $e) {
    $_SESSION['error_message'] = 'خطأ في جلب بيانات الشركة: ' . $e->getMessage();
    header('Location: all_companies.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>تفاصيل الشركة - لوحة تحكم الأدمن</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
  .company-details {
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
  </style>
</head>

<body>
  <?php include 'includes/admin_header.php'; ?>

  <div class="container mt-5">
    <div class="company-details">
      <h2 class="mb-4">تفاصيل الشركة</h2>

      <?php if (isset($_SESSION['error_message'])): ?>
      <div class="alert alert-danger">
        <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
      </div>
      <?php endif; ?>

      <div class="mb-4">
        <h4>اسم الشركة:</h4>
        <p><?php echo htmlspecialchars($company['name'] ?? ''); ?></p>
      </div>

      <div class="mb-4">
        <h4>البريد الإلكتروني:</h4>
        <p><?php echo htmlspecialchars($company['email'] ?? ''); ?></p>
      </div>

      <div class="mb-4">
        <h4>رقم الهاتف:</h4>
        <p><?php echo htmlspecialchars($company['phone'] ?? ''); ?></p>
      </div>

      <div class="mb-4">
        <h4>الموقع:</h4>
        <p><?php echo htmlspecialchars($company['location'] ?? ''); ?></p>
      </div>

      <div class="mb-4">
        <h4>التصنيف:</h4>
        <p><?php echo htmlspecialchars($company['category'] ?? ''); ?></p>
      </div>

      <div class="mb-4">
        <h4>الوصف:</h4>
        <p><?php echo nl2br(htmlspecialchars($company['description'] ?? '')); ?></p>
      </div>

      <div class="mb-4">
        <h4>عدد الوظائف المنشورة:</h4>
        <p><?php echo !empty($company['total_jobs']) ? htmlspecialchars($company['total_jobs']) : '0'; ?></p>
      </div>

      <div class="mb-4">
        <h4>عدد الطلبات:</h4>
        <p>
          <?php echo !empty($company['total_applications']) ? htmlspecialchars($company['total_applications']) : '0'; ?>
        </p>
      </div>

      <div class="mb-4">
        <h4>عدد المشروعات:</h4>
        <p><?php echo !empty($company['total_projects']) ? htmlspecialchars($company['total_projects']) : '0'; ?></p>
      </div>

      <a href="all_companies.php" class="btn btn-secondary">العودة</a>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>