<?php
require '../db_connection.php';

// التحقق من وجود معرف الوظيفة
if (!isset($_GET['job_id'])) {
    header('Location: jobs.php');
    exit();
}

$job_id = $_GET['job_id'];

// استخدام prepared statement مع JOIN لجلب بيانات الشركة
$stmt = $conn->prepare("
    SELECT j.job_id, j.title, j.location, j.job_type, j.salary, j.description,
           c.company_id, c.name AS company_name, c.logo AS company_logo
    FROM jobs j
    LEFT JOIN companies c ON j.company_id = c.company_id
    WHERE j.job_id = ?
");
$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result();
$job = $result->fetch_assoc();

if (!$job) {
    header('Location: jobs.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($job['title']) ?> - تفاصيل الوظيفة</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
  :root {
    --primary-color: #3498db;
    --secondary-color: #2c3e50;
    --accent-color: #e74c3c;
    --light-bg: #f8f9fa;
    --dark-text: #2c3e50;
    --light-text: #7f8c8d;
  }

  body {
    font-family: 'Tajawal', sans-serif;
    background-color: #f5f7fa;
    color: var(--dark-text);
  }

  .job-header {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(52, 152, 219, 0.2);
    position: relative;
    overflow: hidden;
  }

  .company-logo {
    width: 100px;
    height: 100px;
    object-fit: contain;
    background: white;
    padding: 10px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
  }

  .job-title {
    font-weight: 800;
    font-size: 2.2rem;
    margin-bottom: 10px;
  }

  .company-name {
    font-size: 1.3rem;
    opacity: 0.9;
  }

  .job-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin: 20px 0;
  }

  .meta-item {
    background: rgba(255, 255, 255, 0.2);
    padding: 8px 15px;
    border-radius: 50px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .job-details-card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    margin-bottom: 30px;
  }

  .section-title {
    font-weight: 700;
    color: var(--primary-color);
    border-right: 4px solid var(--primary-color);
    padding-right: 15px;
    margin-bottom: 20px;
  }

  .job-description {
    line-height: 1.8;
    font-size: 1.1rem;
  }

  .apply-btn {
    background: var(--primary-color);
    border: none;
    padding: 12px 30px;
    font-weight: 600;
    border-radius: 50px;
    transition: all 0.3s;
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
  }

  .apply-btn:hover {
    background: #2980b9;
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(52, 152, 219, 0.4);
  }

  @media (max-width: 768px) {
    .job-title {
      font-size: 1.8rem;
    }

    .job-header {
      padding: 20px;
    }

    .job-meta {
      flex-direction: column;
      gap: 10px;
    }

    .company-logo {
      width: 80px;
      height: 80px;
      margin-bottom: 15px;
    }
  }
  </style>
</head>

<body>
  <div class="container py-5">
    <!-- رأس صفحة الوظيفة -->
    <div class="job-header">
      <div class="row align-items-center">
        <div class="col-md-2 text-center text-md-start">
          <?php if (!empty($job['company_logo'])): ?>
          <img src="../uploads/company_logos/<?= htmlspecialchars($job['company_logo']) ?>"
            alt="<?= htmlspecialchars($job['company_name']) ?>" class="company-logo mb-3 mb-md-0">
          <?php else: ?>
          <div class="company-logo mb-3 mb-md-0 d-flex align-items-center justify-content-center">
            <i class="fas fa-building text-muted" style="font-size: 2rem;"></i>
          </div>
          <?php endif; ?>
        </div>
        <div class="col-md-8">
          <h1 class="job-title"><?= htmlspecialchars($job['title']) ?></h1>
          <h3 class="company-name"><?= htmlspecialchars($job['company_name'] ?? 'غير محدد') ?></h3>

          <div class="job-meta">
            <div class="meta-item">
              <i class="fas fa-map-marker-alt"></i>
              <?= htmlspecialchars($job['location']) ?>
            </div>
            <div class="meta-item">
              <i class="fas fa-briefcase"></i>
              <?= htmlspecialchars($job['job_type']) ?>
            </div>
            <div class="meta-item">
              <i class="fas fa-money-bill-wave"></i>
              <?= htmlspecialchars($job['salary']) ?>
            </div>
          </div>
        </div>
        <div class="col-md-2 text-center text-md-end">
          <a href="apply_job.php?job_id=<?= $job['job_id'] ?>" class="btn apply-btn">
            <i class="fas fa-paper-plane me-2"></i> التقديم الآن
          </a>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- التفاصيل الرئيسية -->
      <div class="col-lg-8">
        <div class="job-details-card">
          <h3 class="section-title">وصف الوظيفة</h3>
          <div class="job-description">
            <?= nl2br(htmlspecialchars($job['description'])) ?>
          </div>
        </div>
      </div>

      <!-- المعلومات الجانبية -->
      <div class="col-lg-4">
        <div class="job-details-card">
          <h3 class="section-title">معلومات الوظيفة</h3>
          <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span><i class="fas fa-building me-2 text-primary"></i> الشركة</span>
              <span><?= htmlspecialchars($job['company_name'] ?? 'غير محدد') ?></span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span><i class="fas fa-map-marker-alt me-2 text-primary"></i> الموقع</span>
              <span><?= htmlspecialchars($job['location']) ?></span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span><i class="fas fa-briefcase me-2 text-primary"></i> نوع الوظيفة</span>
              <span><?= htmlspecialchars($job['job_type']) ?></span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span><i class="fas fa-money-bill-wave me-2 text-primary"></i> الراتب</span>
              <span><?= htmlspecialchars($job['salary']) ?></span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>