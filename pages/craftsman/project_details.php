<?php
require '../db_connection.php';

// التحقق من وجود معرف المشروع
if (!isset($_GET['project_id'])) {
    header('Location: project_details.php');
    exit();
}

$project_id = $_GET['project_id'];

// استخدام prepared statement مع JOIN لجلب بيانات العميل
$stmt = $conn->prepare("
      SELECT p.project_id , p.title, p.location, p.project_type, p.status , p.created_at , p.budget, p.description, p.deadline ,
           c.company_id, c.name AS company_name, c.logo AS company_logo
    FROM projects p
    LEFT JOIN companies c ON p.company_id = c.company_id
    WHERE project_id = ?
");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();
$project = $result->fetch_assoc();

if (!$project) {
    header('Location: project_details.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($project['title']) ?> - تفاصيل المشروع</title>
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

  .project-header {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(52, 152, 219, 0.2);
    position: relative;
    overflow: hidden;
  }

  .client-picture {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
  }

  .project-title {
    font-weight: 800;
    font-size: 2.2rem;
    margin-bottom: 10px;
  }

  .client-name {
    font-size: 1.3rem;
    opacity: 0.9;
  }

  .project-meta {
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

  .project-details-card {
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

  .project-description {
    line-height: 1.8;
    font-size: 1.1rem;
  }

  .bid-btn {
    background: var(--primary-color);
    border: none;
    padding: 12px 30px;
    font-weight: 600;
    border-radius: 50px;
    transition: all 0.3s;
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
  }

  .bid-btn:hover {
    background: #2980b9;
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(52, 152, 219, 0.4);
  }

  .deadline-warning {
    color: var(--accent-color);
    font-weight: 600;
  }

  .budget-amount {
    font-size: 1.3rem;
    font-weight: 700;
    color: #2ecc71;
  }

  @media (max-width: 768px) {
    .project-title {
      font-size: 1.8rem;
    }

    .project-header {
      padding: 20px;
    }

    .project-meta {
      flex-direction: column;
      gap: 10px;
    }

    .client-picture {
      width: 80px;
      height: 80px;
      margin-bottom: 15px;
    }
  }
  </style>
</head>

<body>
  <div class="container py-5">
    <!-- رأس صفحة المشروع -->
    <div class="project-header">
      <div class="row align-items-center">
        <div class="col-md-2 text-center text-md-start">
          <?php if (!empty($project['client_picture'])): ?>
          <img src="../uploads/profile_pictures/<?= htmlspecialchars($project['client_picture']) ?>"
            alt="<?= htmlspecialchars($project['client_name']) ?>" class="client-picture mb-3 mb-md-0">
          <?php else: ?>
          <div class="client-picture mb-3 mb-md-0 d-flex align-items-center justify-content-center bg-white">
            <i class="fas fa-user text-muted" style="font-size: 2rem;"></i>
          </div>
          <?php endif; ?>
        </div>
        <div class="col-md-8">
          <h1 class="project-title"><?= htmlspecialchars($project['title']) ?></h1>
          <h3 class="client-name"><?= htmlspecialchars($project['client_name'] ?? 'عميل خاص') ?></h3>

          <div class="project-meta">
            <div class="meta-item">
              <i class="fas fa-map-marker-alt"></i>
              <?= htmlspecialchars($project['location']) ?>
            </div>
            <div class="meta-item">
              <i class="fas fa-tools"></i>
              <?= htmlspecialchars($project['project_type']) ?>
            </div>
            <div class="meta-item">
              <i class="fas fa-money-bill-wave"></i>
              <span class="budget-amount"><?= number_format($project['budget'], 2) ?> ج.م</span>
            </div>
            <?php if ($project['deadline'] < date('Y-m-d')): ?>
            <div class="meta-item deadline-warning">
              <i class="fas fa-exclamation-triangle"></i>
              منتهي الصلاحية
            </div>
            <?php else: ?>
            <div class="meta-item">
              <i class="fas fa-clock"></i>
              ينتهي في <?= date('Y-m-d', strtotime($project['deadline'])) ?>
            </div>
            <?php endif; ?>
          </div>
        </div>
        <div class="col-md-2 text-center text-md-end">
          <?php if ($project['deadline'] >= date('Y-m-d') && $project['status'] == 'open'): ?>
          <a href="apply_job.php?project_id=<?= $project['project_id'] ?>" class="btn bid-btn">
            <i class="fas fa-handshake me-2"></i> تقديم عرض
          </a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- التفاصيل الرئيسية -->
      <div class="col-lg-8">
        <div class="project-details-card">
          <h3 class="section-title">تفاصيل المشروع</h3>
          <div class="project-description">
            <?= nl2br(htmlspecialchars($project['description'])) ?>
          </div>
        </div>

        <!-- متطلبات المشروع -->
        <div class="project-details-card">
          <h3 class="section-title">متطلبات المشروع</h3>
          <div class="project-description">
            <?= nl2br(htmlspecialchars($project['requirements'] ?? 'لا توجد متطلبات محددة')) ?>
          </div>
        </div>
      </div>

      <!-- المعلومات الجانبية -->
      <div class="col-lg-4">
        <div class="project-details-card">
          <h3 class="section-title">معلومات المشروع</h3>
          <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span><i class="fas fa-user me-2 text-primary"></i> العميل</span>
              <span><?= htmlspecialchars($project['client_name'] ?? 'عميل خاص') ?></span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span><i class="fas fa-map-marker-alt me-2 text-primary"></i> الموقع</span>
              <span><?= htmlspecialchars($project['location']) ?></span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span><i class="fas fa-tools me-2 text-primary"></i> نوع المشروع</span>
              <span><?= htmlspecialchars($project['project_type']) ?></span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span><i class="fas fa-money-bill-wave me-2 text-primary"></i> الميزانية</span>
              <span class="budget-amount"><?= number_format($project['budget'], 2) ?> ج.م</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span><i class="fas fa-calendar-alt me-2 text-primary"></i> تاريخ النشر</span>
              <span><?= date('Y-m-d', strtotime($project['created_at'])) ?></span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span><i class="fas fa-clock me-2 text-primary"></i> تاريخ الانتهاء</span>
              <span><?= date('Y-m-d', strtotime($project['deadline'])) ?></span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span><i class="fas fa-info-circle me-2 text-primary"></i> الحالة</span>
              <span
                class="badge bg-<?= $project['status'] == 'open' ? 'success' : ($project['status'] == 'in_progress' ? 'warning' : 'secondary') ?>">
                <?= $project['status'] == 'open' ? 'مفتوح' : ($project['status'] == 'in_progress' ? 'قيد التنفيذ' : 'مكتمل') ?>
              </span>
            </li>
          </ul>
        </div>

        <!-- معلومات التواصل -->
        <div class="project-details-card">
          <h3 class="section-title">معلومات التواصل</h3>
          <div class="d-grid gap-2">
            <a href="contact_client.php?project_id=<?= $project['project_id'] ?>" class="btn btn-outline-primary">
              <i class="fas fa-envelope me-2"></i> مراسلة العميل
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>