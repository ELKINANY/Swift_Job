<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require '../db_connection.php';

$craftsman_id = $_GET['user_id'];

// جلب بيانات الحرفي
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? AND role = 'craftsman'");
$stmt->bind_param("i", $craftsman_id);
$stmt->execute();
$craftsman = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$craftsman) {
    header("Location: view_projects.php");
    exit();
}

// جلب مشاريع الحرفي السابقة
$stmt = $conn->prepare("SELECT p.* FROM projects p
                       JOIN project_bids pb ON p.project_id = pb.project_id
                       WHERE pb.craftsman_id = ? AND pb.status = 'accepted'");
$stmt->bind_param("i", $craftsman_id);
$stmt->execute();
$completed_projects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>الملف الشخصي للحرفي</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">

  <style>
  body {
    font-family: 'Tajawal', sans-serif;
    background: linear-gradient(135deg, #f4f7fa, #e0e7ff);
    min-height: 100vh;
    margin: 0;
    padding: 0;
  }

  .container {
    padding: 3rem 1rem;
  }

  .profile-container {
    max-width: 900px;
    margin: 0 auto;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .profile-container:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
  }

  .profile-img {
    width: 180px;
    height: 180px;
    border-radius: 50%;
    object-fit: cover;
    border: 5px solid #3b82f6;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .profile-img:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
  }

  h2 {
    font-size: 2rem;
    font-weight: 700;
    color: #1e3a8a;
    margin-top: 1rem;
  }

  .text-muted.specialization {
    font-size: 1.2rem;
    color: #6b7280;
    background: #e0e7ff;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    display: inline-block;
  }

  h4 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1e3a8a;
    margin-bottom: 1.5rem;
    position: relative;
  }

  h4::after {
    content: '';
    position: absolute;
    width: 40px;
    height: 3px;
    background: linear-gradient(90deg, #1e3a8a, #3b82f6);
    bottom: -5px;
    right: 0;
  }

  .info-card {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 15px;
    padding: 1.5rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .info-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
  }

  .info-item {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
    color: #2d3748;
  }

  .info-item i {
    color: #3b82f6;
    margin-left: 0.5rem;
    font-size: 1.2rem;
    transition: transform 0.3s ease;
  }

  .info-item:hover i {
    transform: scale(1.2);
  }

  .project-card {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 15px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
  }

  .project-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
  }

  .project-card h5 {
    font-size: 1.3rem;
    font-weight: 600;
    color: #e2e8f0;
    margin-bottom: 0.5rem;
  }

  .project-card p {
    color: #94a3b8;
    margin-bottom: 0.5rem;
  }

  .project-card .text-muted {
    color: #6b7280 !important;
    font-size: 0.9rem;
  }

  .btn-secondary {
    background: linear-gradient(90deg, #1e3a8a, #3b82f6);
    border: none;
    padding: 0.75rem 2rem;
    border-radius: 10px;
    color: white;
    font-weight: 500;
    transition: all 0.3s ease;
  }

  .btn-secondary:hover {
    background: linear-gradient(90deg, #163072, #2f69c3);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(59, 130, 246, 0.5);
  }

  @media (max-width: 768px) {
    .profile-img {
      width: 140px;
      height: 140px;
    }

    h2 {
      font-size: 1.8rem;
    }

    h4 {
      font-size: 1.3rem;
    }

    .project-card {
      padding: 1rem;
    }

    .info-card {
      padding: 1rem;
    }

    .btn-secondary {
      padding: 0.5rem 1.5rem;
    }
  }
  </style>
</head>

<body>
  <div class="container py-5">
    <div class="profile-container">
      <div class="text-center mb-4">
        <h2><?= htmlspecialchars($craftsman['name']) ?> <i class="fas fa-user-tie"></i></h2>
        <p class="text-muted specialization"><?= htmlspecialchars($craftsman['specialization']) ?></p>
      </div>

      <div class="row mb-4">
        <div class="col-md-6">
          <h4><i class="fas fa-info-circle me-2"></i> المعلومات الأساسية</h4>
          <div class="info-card">
            <div class="info-item">
              <i class="fas fa-phone"></i>
              <span><strong>الهاتف:</strong> <?= htmlspecialchars($craftsman['phone']) ?: 'غير متوفر' ?></span>
            </div>
            <div class="info-item">
              <i class="fas fa-map-marker-alt"></i>
              <span><strong>الموقع:</strong> <?= htmlspecialchars($craftsman['location']) ?: 'غير متوفر' ?></span>
            </div>
          </div>
        </div>
      </div>

      <div class="mb-4">
        <h4><i class="fas fa-briefcase me-2"></i> المشاريع المكتملة</h4>
        <?php if (empty($completed_projects)): ?>
        <p class="text-muted">لا يوجد مشاريع مكتملة بعد</p>
        <?php else: ?>
        <div class="projects-list">
          <?php foreach ($completed_projects as $project): ?>
          <div class="project-card">
            <h5><?= htmlspecialchars($project['title']) ?></h5>
            <p><?= htmlspecialchars(substr($project['description'], 0, 100)) ?>...</p>
            <p class="text-muted">تم التسليم في: <?= date('Y-m-d', strtotime($project['deadline'])) ?></p>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <div class="text-center">
        <a href="javascript:history.back()" class="btn btn-secondary">
          <i class="fas fa-arrow-right me-2"></i> رجوع
        </a>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php $conn->close(); ?>