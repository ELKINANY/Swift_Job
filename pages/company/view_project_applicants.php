<?php
session_start();

// التحقق من أن المستخدم مسجل دخول وهو شركة
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'company') {
    header("Location: ../login.php");
    exit();
}

require '../db_connection.php';

$project_id = $_GET['project_id'];
$company_id = $_SESSION['user_id'];

// معالجة قبول أو رفض المتقدم
$success_message = "";
$error_message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $bid_id = intval($_POST['bid_id']);
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE project_bids SET status = ? WHERE bid_id = ? AND project_id = ? AND project_id IN (SELECT project_id FROM projects WHERE company_id = ?)");
    $stmt->bind_param("siii", $status, $bid_id, $project_id, $company_id);
    
    if ($stmt->execute()) {
        $success_message = "تم تحديث حالة المتقدم بنجاح.";
    } else {
        $error_message = "حدث خطأ أثناء تحديث الحالة.";
    }
    $stmt->close();
}

// التحقق من أن المشروع يخص الشركة
$stmt = $conn->prepare("SELECT * FROM projects WHERE project_id = ? AND company_id = ?");
$stmt->bind_param("ii", $project_id, $company_id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$project) {
    header("Location: view_projects.php");
    exit();
}

// جلب المتقدمين للحرفه
$sql = "SELECT u.user_id, u.name, u.phone, u.specialization, u.profile_pic, 
               pb.bid_id, pb.bid_amount, pb.proposal, pb.bid_date, pb.status
        FROM project_bids pb
        JOIN users u ON pb.craftsman_id = u.user_id
        WHERE pb.project_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $project_id);
$stmt->execute();
$applicants = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="ltr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>المتقدمون للحرفه</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
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
    background: var(--warning);
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

  .profile-pic {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 1rem;
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
    background-color: var(--accent);
    color: white;
    border: none;
  }

  .btn-info:hover {
    background-color: #2563eb;
    transform: translateY(-2px);
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

  .alert-info {
    background-color: #e0f2fe;
    color: #075985;
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
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>المتقدمون للحرفه: <?= htmlspecialchars($project['title']); ?></h1>
        <a href="view_projects.php" class="btn btn-primary">
          <i class="fas fa-arrow-right"></i> رجوع
        </a>
      </div>

      <?php if (empty($applicants)): ?>
      <div class="empty-state">
        <i class="fas fa-users"></i>
        <p>لا يوجد متقدمون لهذا المشروع حتى الآن.</p>
      </div>
      <?php else: ?>
      <div class="applicants-grid">
        <?php foreach ($applicants as $applicant): ?>
        <div class="applicant-card">
          <h3><?= htmlspecialchars($applicant['name']); ?></h3>
          <div class="status <?= strtolower($applicant['status']); ?>">
            الحالة: <?= htmlspecialchars($applicant['status']); ?>
          </div>
          <p><i class="fas fa-briefcase"></i> التخصص:
            <?= htmlspecialchars($applicant['specialization'] ?: 'غير متوفر'); ?></p>
          <p><i class="fas fa-phone"></i> الهاتف: <?= htmlspecialchars($applicant['phone'] ?: 'غير متوفر'); ?></p>
          <p><i class="fas fa-money-bill-wave"></i> المبلغ المقترح: <?= number_format($applicant['bid_amount'], 2); ?>
            ج.م</p>
          <p><i class="fas fa-file-alt"></i> العرض:
            <?= nl2br(htmlspecialchars($applicant['proposal'] ?: 'غير متوفر')); ?></p>
          <p><i class="fas fa-calendar-alt"></i> تاريخ التقديم:
            <?= date('Y-m-d H:i', strtotime($applicant['bid_date'])); ?></p>
          <div class="actions">
            <a href="view_craftsman.php?user_id=<?= $applicant['user_id']; ?>" class="btn btn-info">
              <i class="fas fa-user"></i> عرض الملف الشخصي
            </a>
            <a href="PHPMailer.php?user_id=<?= $applicant['user_id']; ?>" class="btn btn-primary">
              <i class="fas fa-envelope"></i> تواصل عبر البريد
            </a>
            <a href="whatup.php?user_id=<?= $applicant['user_id']; ?>" class="btn btn-success">
              <i class="fab fa-whatsapp"></i> تواصل عبر واتساب
            </a>
            <?php if ($applicant['status'] == 'pending'): ?>
            <form method="POST" onsubmit="return confirm('هل أنت متأكد من قبول هذا المتقدم؟');">
              <input type="hidden" name="bid_id" value="<?= $applicant['bid_id']; ?>">
              <input type="hidden" name="status" value="accepted">
              <button type="submit" name="update_status" class="btn btn-accept">
                <i class="fas fa-check"></i> قبول
              </button>
            </form>
            <form method="POST" onsubmit="return confirm('هل أنت متأكد من رفض هذا المتقدم؟');">
              <input type="hidden" name="bid_id" value="<?= $applicant['bid_id']; ?>">
              <input type="hidden" name="status" value="rejected">
              <button type="submit" name="update_status" class="btn btn-reject">
                <i class="fas fa-times"></i> رفض
              </button>
            </form>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </main>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    console.log('View Project Applicants Page Loaded');
  });
  </script>
</body>

</html>