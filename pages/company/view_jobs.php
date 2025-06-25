<?php
session_start();

// التحقق من تسجيل الدخول كشركة
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'company') {
    header('Location: ../login.php');
    exit();
}

require '../db_connection.php'; // الاتصال بقاعدة البيانات
$company_id = $_SESSION['user_id'];

// جلب الوظائف الخاصة بالشركة
$stmt = $conn->prepare("SELECT * FROM jobs WHERE company_id = ?");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$jobs = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="ltr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>إدارة الوظائف</title>
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

  /* Jobs Grid */
  .jobs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
  }

  .job-card {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    animation: fadeIn 0.5s ease-out;
  }

  .job-card:hover {
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

  .job-card h3 {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--dark);
    margin: 0 0 1rem;
  }

  .job-card p {
    margin: 0.5rem 0;
    color: #6b7280;
    font-size: 0.95rem;
  }

  .job-card .job-type {
    background: var(--accent);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.85rem;
    display: inline-block;
    margin-bottom: 1rem;
  }

  .job-card .actions {
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

  .btn-info {
    background-color: var(--success);
    color: white;
    border: none;
  }

  .btn-info:hover {
    background-color: #059669;
    transform: translateY(-2px);
  }

  .btn-warning {
    background-color: var(--warning);
    color: white;
    border: none;
  }

  .btn-warning:hover {
    background-color: #d97706;
    transform: translateY(-2px);
  }

  .btn-danger {
    background-color: var(--danger);
    color: white;
    border: none;
  }

  .btn-danger:hover {
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
    .jobs-grid {
      grid-template-columns: 1fr;
    }

    h1 {
      font-size: 1.5rem;
    }

    .job-card .actions {
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
      <h1>إدارة الوظائف</h1>

      <?php if ($jobs->num_rows > 0): ?>
      <div class="jobs-grid">
        <?php while ($job = $jobs->fetch_assoc()): ?>
        <div class="job-card">
          <h3><?= htmlspecialchars($job['title']); ?></h3>
          <div class="job-type"><?= htmlspecialchars($job['job_type']); ?></div>
          <p><i class="fas fa-money-bill-wave"></i> الراتب: <?= number_format($job['salary'], 2); ?> ج.م</p>
          <div class="actions">
            <a href="view_applicants.php?job_id=<?= $job['job_id']; ?>" class="btn btn-info">
              <i class="fas fa-users"></i> عرض المتقدمين
            </a>
            <a href="edit_job.php?job_id=<?= $job['job_id']; ?>" class="btn btn-warning">
              <i class="fas fa-edit"></i> تعديل
            </a>
            <a href="delete_job.php?job_id=<?= $job['job_id']; ?>" class="btn btn-danger"
              onclick="return confirm('هل أنت متأكد من حذف هذه الوظيفة؟');">
              <i class="fas fa-trash"></i> حذف
            </a>
          </div>
        </div>
        <?php endwhile; ?>
      </div>
      <?php else: ?>
      <div class="empty-state">
        <i class="fas fa-briefcase"></i>
        <p>لا توجد وظائف منشورة حاليًا. <a href="add_job.php">أضف وظيفة جديدة</a>.</p>
      </div>
      <?php endif; ?>
    </main>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    console.log('View Jobs Page Loaded');
  });
  </script>
</body>

</html>