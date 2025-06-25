<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header('Location: login.php');
    exit();
}

require '../db_connection.php';

// التحقق من تمرير job_id
if (!isset($_GET['job_id'])) {
    die("لم يتم تحديد الوظيفة.");
}

$job_id = intval($_GET['job_id']);
$company_id = $_SESSION['user_id'];

// معالجة قبول أو رفض المتقدم
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $applicant_id = intval($_POST['user_id']);
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE job_applications SET status = ? WHERE job_id = ? AND user_id = ? AND job_id IN (SELECT job_id FROM jobs WHERE company_id = ?)");
    $stmt->bind_param("siii", $status, $job_id, $applicant_id, $company_id);
    
    if ($stmt->execute()) {
        $success_message = "تم تحديث حالة المتقدم بنجاح.";
    } else {
        $error_message = "حدث خطأ أثناء تحديث الحالة.";
    }
    $stmt->close();
}

// الفلاتر
$order_by = "expected_salary"; // الافتراضي الترتيب حسب الراتب
$order_dir = "ASC"; // الترتيب تصاعدي افتراضيًا

if (isset($_GET['sort_by']) && in_array($_GET['sort_by'], ['name', 'email', 'expected_salary'])) {
    $order_by = $_GET['sort_by'];
}

if (isset($_GET['order']) && in_array($_GET['order'], ['ASC', 'DESC'])) {
    $order_dir = $_GET['order'];
}

// جلب المتقدمين بناءً على الفلترة
$stmt = $conn->prepare("
    SELECT users.user_id, users.name, users.email, users.cv_link, job_applications.expected_salary, job_applications.resume, job_applications.cover_letter, job_applications.why_job, job_applications.why_company, job_applications.applied_at, job_applications.status, job_applications.phone
    FROM job_applications
    JOIN users ON job_applications.user_id = users.user_id
    JOIN jobs ON job_applications.job_id = jobs.job_id
    WHERE job_applications.job_id = ? AND jobs.company_id = ?
    ORDER BY $order_by $order_dir
");
$stmt->bind_param("ii", $job_id, $company_id);
$stmt->execute();
$applicants = $stmt->get_result();

// جلب اسم الشركة
$stmt = $conn->prepare("SELECT name FROM companies WHERE company_id = ?");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$company = $stmt->get_result()->fetch_assoc();
$company_name = $company['name'];

// جلب اسم الوظيفة
$stmt = $conn->prepare("SELECT title FROM jobs WHERE job_id = ?");
$stmt->bind_param("i", $job_id);
$stmt->execute();
$job = $stmt->get_result()->fetch_assoc();
$job_title = $job['title'];
$stmt->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="ltr">

<head>
  <meta charset=" UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>عرض المتقدمين</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
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

  .dashboard-container {
    display: flex;
    min-height: 100vh;
  }

  .content {
    flex: 1;
    padding: 2rem;
    transition: all 0.3s ease;
  }

  h1 {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 2rem;
    position: relative;
    text-align: right;
  }

  h1::after {
    content: "";
    position: absolute;
    bottom: -0.5rem;
    right: 0;
    width: 80px;
    height: 4px;
    background-color: var(--secondary);
    border-radius: 2px;
  }

  /* Filter Form */
  .filter-card {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    margin-bottom: 2rem;
    animation: fadeIn 0.5s ease-out;
  }

  .filter-form {
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
  }

  .form-select {
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
    padding: 0.5rem;
    transition: all 0.3s ease;
  }

  .form-select:focus {
    border-color: var(--secondary);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    outline: none;
  }

  /* Applicants Grid */
  .applicants-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
  }

  .applicant-card {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    animation: fadeIn 0.5s ease-out;
  }

  .applicant-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(10px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .applicant-card h3 {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--dark);
    margin: 0 0 1rem;
  }

  .applicant-card p {
    margin: 0.5rem 0;
    color: #6b7280;
    font-size: 0.95rem;
  }

  .applicant-card .status {
    background: var(--accent);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.85rem;
    display: inline-block;
    margin-bottom: 1rem;
  }

  .applicant-card .status.accepted {
    background: var(--success);
  }

  .applicant-card .status.rejected {
    background: var(--danger);
  }

  .applicant-card .actions {
    display: flex;
    gap: 0.75rem;
    margin-top: 1rem;
    flex-wrap: wrap;
  }

  .btn {
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-weight: 500;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .btn-success {
    background-color: var(--success);
    color: white;
    border: none;
  }

  .btn-success:hover {
    background-color: #059669;
    transform: translateY(-2px);
  }

  .btn-primary {
    background-color: var(--secondary);
    color: white;
    border: none;
  }

  .btn-primary:hover {
    background-color: var(--primary);
    transform: translateY(-2px);
  }

  .btn-accept {
    background-color: var(--success);
    color: white;
    border: none;
  }

  .btn-accept:hover {
    background-color: #059669;
    transform: translateY(-2px);
  }

  .btn-reject {
    background-color: var(--danger);
    color: white;
    border: none;
  }

  .btn-reject:hover {
    background-color: #dc2626;
    transform: translateY(-2px);
  }

  /* Alerts */
  .alert {
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    animation: slideIn 0.5s ease-out;
  }

  .alert-success {
    background-color: #d1fae5;
    color: var(--success);
  }

  .alert-danger {
    background-color: #fee2e2;
    color: var(--danger);
  }

  .alert-warning {
    background-color: #fef3c7;
    color: var(--warning);
  }

  .alert i {
    margin-left: 0.75rem;
    font-size: 1.2rem;
  }

  @keyframes slideIn {
    from {
      opacity: 0;
      transform: translateX(20px);
    }

    to {
      opacity: 1;
      transform: translateX(0);
    }
  }

  /* Empty State */
  .empty-state {
    text-align: center;
    padding: 2rem;
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  }

  .empty-state i {
    font-size: 3rem;
    color: var(--secondary);
    margin-bottom: 1rem;
  }

  .empty-state p {
    font-size: 1.1rem;
    color: #6b7280;
  }

  /* Responsive Design */
  @media (max-width: 992px) {
    .dashboard-container {
      flex-direction: column;
    }

    .content {
      padding: 1.5rem;
    }
  }

  @media (max-width: 768px) {
    .applicants-grid {
      grid-template-columns: 1fr;
    }

    h1 {
      font-size: 1.5rem;
    }

    .filter-form {
      flex-direction: column;
      align-items: stretch;
    }

    .applicant-card .actions {
      flex-direction: column;
    }

    .btn {
      width: 100%;
      justify-content: center;
    }
  }
  </style>
</head>

<body>
  <div class="dashboard-container">
    <!-- الشريط الجانبي -->
    <?php include "./sidebar.php" ?>

    <!-- المحتوى الرئيسي -->
    <main class="content">
      <h1>المتقدمون للوظيفة: <?= htmlspecialchars($job_title); ?></h1>

      <?php if (isset($success_message)): ?>
      <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?= $success_message; ?>
      </div>
      <?php endif; ?>

      <?php if (isset($error_message)): ?>
      <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <?= $error_message; ?>
      </div>
      <?php endif; ?>

      <!-- نموذج الفلترة -->
      <div class="filter-card">
        <form method="GET" class="filter-form">
          <input type="hidden" name="job_id" value="<?= $job_id; ?>">
          <label class="form-label">ترتيب حسب:</label>
          <select name="sort_by" class="form-select">
            <option value="expected_salary" <?= $order_by == 'expected_salary' ? 'selected' : ''; ?>>الراتب</option>
            <option value="name" <?= $order_by == 'name' ? 'selected' : ''; ?>>الاسم</option>
            <option value="email" <?= $order_by == 'email' ? 'selected' : ''; ?>>البريد الإلكتروني</option>
          </select>
          <select name="order" class="form-select">
            <option value="ASC" <?= $order_dir == 'ASC' ? 'selected' : ''; ?>>تصاعدي</option>
            <option value="DESC" <?= $order_dir == 'DESC' ? 'selected' : ''; ?>>تنازلي</option>
          </select>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-filter"></i> تصفية
          </button>
        </form>
      </div>

      <!-- عرض المتقدمين -->
      <?php if ($applicants->num_rows > 0): ?>
      <div class="applicants-grid">
        <?php while ($applicant = $applicants->fetch_assoc()): ?>
        <div class="applicant-card">
          <h3><?= htmlspecialchars($applicant['name']); ?></h3>
          <div class="status <?= strtolower($applicant['status']); ?>">
            الحالة: <?= htmlspecialchars($applicant['status']); ?>
          </div>
          <p><i class="fas fa-envelope"></i> البريد: <?= htmlspecialchars($applicant['email']); ?></p>
          <p><i class="fas fa-phone"></i> الهاتف: <?= htmlspecialchars($applicant['phone']); ?></p>
          <p><i class="fas fa-money-bill-wave"></i> الراتب المتوقع:
            <?= number_format($applicant['expected_salary'], 2); ?> ج.م</p>
          <p><i class="fas fa-calendar-alt"></i> تاريخ التقديم: <?= htmlspecialchars($applicant['applied_at']); ?></p>
          <p><i class="fas fa-file-alt"></i> الخطاب: <?= htmlspecialchars($applicant['cover_letter'] ?: 'غير متوفر'); ?>
          </p>
          <p><i class="fas fa-question-circle"></i> لماذا الوظيفة:
            <?= htmlspecialchars($applicant['why_job'] ?: 'غير متوفر'); ?></p>
          <p><i class="fas fa-building"></i> لماذا الشركة:
            <?= htmlspecialchars($applicant['why_company'] ?: 'غير متوفر'); ?></p>
          <div class="actions">
            <?php if (!empty($applicant['cv_link'])): ?>
            <a href="../<?= htmlspecialchars($applicant['cv_link']); ?>" target="_blank" class="btn btn-success">
              <i class="fas fa-file-pdf"></i> عرض السيرة الذاتية
            </a>
            <?php endif; ?>
            <a href="PHPMailer.php?user_id=<?= $applicant['user_id']; ?>" class="btn btn-primary">
              <i class="fas fa-envelope"></i> تواصل عبر البريد
            </a>
            <a href="whatup.php?user_id=<?= $applicant['user_id']; ?>" class="btn btn-success">
              <i class="fab fa-whatsapp"></i> تواصل عبر واتساب
            </a>
            <form method="POST" onsubmit="return confirm('هل أنت متأكد من قبول هذا المتقدم؟');">
              <input type="hidden" name="user_id" value="<?= $applicant['user_id']; ?>">
              <input type="hidden" name="status" value="accepted">
              <button type="submit" name="update_status" class="btn btn-accept">
                <i class="fas fa-check"></i> قبول
              </button>
            </form>
            <form method="POST" onsubmit="return confirm('هل أنت متأكد من رفض هذا المتقدم؟');">
              <input type="hidden" name="user_id" value="<?= $applicant['user_id']; ?>">
              <input type="hidden" name="status" value="rejected">
              <button type="submit" name="update_status" class="btn btn-reject">
                <i class="fas fa-times"></i> رفض
              </button>
            </form>
          </div>
        </div>
        <?php endwhile; ?>
      </div>
      <?php else: ?>
      <div class="empty-state">
        <i class="fas fa-users"></i>
        <p>لا يوجد متقدمون لهذه الوظيفة حتى الآن.</p>
      </div>
      <?php endif; ?>
    </main>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    console.log('View Applicants Page Loaded');
  });
  </script>
</body>

</html>