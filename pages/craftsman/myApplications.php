<?php
// اتصال بقاعدة البيانات
require '../db_connection.php'; // الاتصال بقاعدة البيانات
include "../navBar.php";
// بدء الجلسة للحصول على معرف المستخدم
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
  die("لم يتم العثور على معرف المستخدم. يرجى تسجيل الدخول.");
}

// استعلام لاسترجاع الطلبات المقدمة من الحرفي مع إضافة company_id
$query = "SELECT projects.project_id, projects.title AS project_title, project_bids.bid_date, project_bids.bid_amount, project_bids.status, projects.company_id 
          FROM project_bids 
          JOIN projects ON project_bids.project_id = projects.project_id
          WHERE project_bids.craftsman_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

// معالجة إرسال التقييم
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rate_project'])) {
  $companyId = isset($_POST['company_id']) ? (int)$_POST['company_id'] : null;
  $rating = (int)$_POST['rating'];
  $comment = trim($_POST['comment']);
  $createdAt = date('Y-m-d H:i:s');

  // التحقق من صحة التقييم ومعرف الشركة
  if ($rating >= 1 && $rating <= 5 && $companyId !== null) {
    $insertQuery = "INSERT INTO company_reviews (user_id, company_id, rating, comment, review_date) VALUES (?, ?, ?, ?, ?)";
    $stmtInsert = $conn->prepare($insertQuery);
    $stmtInsert->bind_param("iiiss", $userId, $companyId, $rating, $comment, $createdAt);
    if ($stmtInsert->execute()) {
      $successMessage = "تم إضافة التقييم بنجاح!";
    } else {
      $errorMessage = "حدث خطأ أثناء إضافة التقييم: " . $conn->error;
    }
    $stmtInsert->close();
  } else {
    $errorMessage = "التقييم أو معرف الشركة غير صالح.";
  }
}
?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>طلباتك المقدمة</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>CSS/navbar.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <style>
  body {
    font-family: 'Tajawal', sans-serif;
    background-color: #f4f7fa;
    color: #333;
  }

  .container {
    padding-top: 2rem;
    padding-bottom: 2rem;
  }

  h1 {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1e3a8a;
    text-align: center;
    margin-bottom: 2rem;
    position: relative;
  }

  h1::after {
    content: '';
    position: absolute;
    width: 50px;
    height: 4px;
    background: linear-gradient(90deg, #1e3a8a, #3b82f6);
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
  }

  .table-responsive {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }

  .table {
    background-color: white;
    border: none;
  }

  .table th {
    background: linear-gradient(90deg, #1e3a8a, #3b82f6);
    color: white;
    font-weight: 600;
    padding: 1rem;
    text-align: center;
    border: none;
  }

  .table td {
    padding: 1rem;
    text-align: center;
    vertical-align: middle;
    border-bottom: 1px solid #e2e8f0;
  }

  .table tr {
    transition: all 0.3s ease;
  }

  .table tr:hover {
    background-color: #e6f0fa;
    transform: translateY(-2px);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
  }

  .status-pending {
    color: #f59e0b;
    font-weight: 500;
  }

  .status-accepted {
    color: #10b981;
    font-weight: 500;
  }

  .status-rejected {
    color: #ef4444;
    font-weight: 500;
  }

  .rate-btn {
    background: linear-gradient(90deg, #1e3a8a, #3b82f6);
    border: none;
    padding: 0.5rem 1.5rem;
    border-radius: 10px;
    color: white;
    font-weight: 500;
    transition: all 0.3s ease;
  }

  .rate-btn:hover {
    background: linear-gradient(90deg, #163072, #2f69c3);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(59, 130, 246, 0.5);
  }

  .modal-content {
    border-radius: 15px;
    padding: 1.5rem;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
  }

  .star-rating .star {
    font-size: 1.5rem;
    color: #ccc;
    cursor: pointer;
    transition: color 0.3s ease;
  }

  .star-rating .star.active {
    color: #f59e0b;
  }

  .alert-success {
    background-color: #d1fae5;
    color: #065f46;
    border: 1px solid #10b981;
    border-radius: 10px;
    padding: 0.75rem;
    margin-bottom: 1rem;
  }

  .alert-danger {
    background-color: #fee2e2;
    color: #991b1b;
    border: 1px solid #ef4444;
    border-radius: 10px;
    padding: 0.75rem;
    margin-bottom: 1rem;
  }

  @media (max-width: 768px) {
    h1 {
      font-size: 2rem;
    }

    .table th,
    .table td {
      padding: 0.75rem;
      font-size: 0.9rem;
    }

    .rate-btn {
      padding: 0.4rem 1rem;
      font-size: 0.9rem;
    }
  }
  </style>
</head>

<body>
  <div class="container">
    <h1>طلباتك المقدمة <i class="fas fa-briefcase"></i></h1>

    <?php if (isset($successMessage)): ?>
    <div class="alert alert-success text-center"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>
    <?php if (isset($errorMessage)): ?>
    <div class="alert alert-danger text-center"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <?php if ($result && $result->num_rows > 0): ?>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>اسم المشروع</th>
            <th>تاريخ التقديم</th>
            <th>الحالة</th>
            <th>الإجراء</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['project_title']) ?></td>
            <td><?= htmlspecialchars($row['bid_date']) ?></td>
            <td class="status-<?= strtolower(str_replace(' ', '-', $row['status'])) ?>">
              <?= htmlspecialchars($row['status']) ?>
            </td>
            <td>
              <?php if (in_array(strtolower($row['status']), ['accepted', 'rejected'])): ?>
              <button type="button" class="rate-btn" data-bs-toggle="modal" data-bs-target="#rateModal"
                data-company-id="<?= isset($row['company_id']) ? htmlspecialchars($row['company_id']) : '' ?>"
                data-project-id="<?= htmlspecialchars($row['project_id']) ?>">
                <i class="fas fa-star me-2"></i> تقييم
              </button>
              <?php endif; ?>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
    <p class="text-center text-muted">لا توجد طلبات مقدمة حاليًا.</p>
    <?php endif; ?>

    <!-- Modal للتقييم -->
    <div class="modal fade" id="rateModal" tabindex="-1" aria-labelledby="rateModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="rateModalLabel">تقييم الشركة</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form method="POST" id="rateForm">
              <input type="hidden" name="company_id" id="rateCompanyId">
              <input type="hidden" name="project_id" id="rateProjectId">
              <div class="mb-3">
                <label for="rating" class="form-label">التقييم (1-5 نجوم)</label>
                <div class="star-rating mb-2" id="starRating">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                  <span class="star" data-value="<?= $i ?>">★</span>
                  <?php endfor; ?>
                </div>
                <input type="hidden" name="rating" id="ratingInput" value="0">
              </div>
              <div class="mb-3">
                <label for="comment" class="form-label">تعليقك</label>
                <textarea name="comment" id="comment" class="form-control" rows="3" placeholder="اكتب تعليقك هنا..."
                  required></textarea>
              </div>
              <button type="submit" name="rate_project" class="btn btn-primary w-100">إرسال التقييم</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <?php $conn->close(); ?>
  </div>

  <script>
  // التحكم في نجمات التقييم
  document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.star');
    const ratingInput = document.getElementById('ratingInput');
    let selectedRating = 0;

    stars.forEach(star => {
      star.addEventListener('click', function() {
        selectedRating = this.getAttribute('data-value');
        ratingInput.value = selectedRating;
        stars.forEach(s => s.classList.remove('active'));
        for (let i = 0; i < selectedRating; i++) {
          stars[i].classList.add('active');
        }
      });

      star.addEventListener('mouseover', function() {
        const value = this.getAttribute('data-value');
        stars.forEach((s, index) => {
          if (index < value) s.classList.add('active');
          else s.classList.remove('active');
        });
      });

      star.addEventListener('mouseout', function() {
        stars.forEach(s => s.classList.remove('active'));
        for (let i = 0; i < selectedRating; i++) {
          stars[i].classList.add('active');
        }
      });
    });

    // إعداد الـ Modal مع معرف الشركة والمشروع
    const rateModal = document.getElementById('rateModal');
    rateModal.addEventListener('show.bs.modal', function(event) {
      const button = event.relatedTarget;
      const companyId = button.getAttribute('data-company-id');
      const projectId = button.getAttribute('data-project-id');
      if (companyId) {
        document.getElementById('rateCompanyId').value = companyId;
      } else {
        console.error('Company ID is undefined');
      }
      document.getElementById('rateProjectId').value = projectId;
      ratingInput.value = 0;
      stars.forEach(star => star.classList.remove('active'));
      document.getElementById('comment').value = '';
    });
  });
  </script>
</body>

</html>