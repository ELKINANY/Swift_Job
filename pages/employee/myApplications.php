<?php
ob_start();
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

// استعلام لاسترجاع الطلبات المقدمة من المستخدم
$query = "SELECT jobs.job_id, jobs.title AS job_title, job_applications.applied_at, job_applications.expected_salary, job_applications.status, jobs.company_id, jobs.description
          FROM job_applications 
          JOIN jobs ON job_applications.job_id = jobs.job_id
          WHERE job_applications.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

// معالجة إلغاء الطلب
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_application'])) {
  $job_id = (int)$_POST['job_id'];
  $stmtCancel = $conn->prepare("DELETE FROM job_applications WHERE user_id = ? AND job_id = ?");
  $stmtCancel->bind_param("ii", $userId, $job_id);
  if ($stmtCancel->execute()) {
    $successMessage = "تم إلغاء الطلب بنجاح!";
    header("Location: myApplications.php?success=1");
    exit;
  } else {
    $errorMessage = "حدث خطأ أثناء إلغاء الطلب: " . $conn->error;
  }
  $stmtCancel->close();
}

// معالجة تعديل الراتب المتوقع
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_salary'])) {
  $job_id = (int)$_POST['job_id'];
  $new_salary = (float)$_POST['expected_salary'];
  $stmtUpdate = $conn->prepare("UPDATE job_applications SET expected_salary = ? WHERE user_id = ? AND job_id = ?");
  $stmtUpdate->bind_param("dii", $new_salary, $userId, $job_id);
  if ($stmtUpdate->execute()) {
    $successMessage = "تم تحديث الراتب المتوقع بنجاح!";
    header("Refresh:0"); // إعادة تحميل الصفحة
  } else {
    $errorMessage = "حدث خطأ أثناء تحديث الراتب: " . $conn->error;
  }
  $stmtUpdate->close();
}

// معالجة إرسال التقييم
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rate_job'])) {
  $companyId = isset($_POST['company_id']) ? (int)$_POST['company_id'] : null;
  $rating = (int)$_POST['rating'];
  $comment = trim($_POST['comment']);
  $createdAt = date('Y-m-d H:i:s');

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

// التحقق من تغيير الحالة
$statusCheckQuery = "SELECT job_id, status FROM job_applications WHERE user_id = ? AND status != ?";
$stmtStatus = $conn->prepare($statusCheckQuery);
$lastStatus = $_SESSION['last_status'] ?? [];
$statusToCompare = $lastStatus[$userId] ?? '';
$stmtStatus->bind_param("is", $userId, $statusToCompare);
$stmtStatus->execute();
$resultStatus = $stmtStatus->get_result();
$statusChanged = false;
while ($row = $resultStatus->fetch_assoc()) {
  if (!isset($lastStatus[$row['job_id']]) || $lastStatus[$row['job_id']] !== $row['status']) {
    $statusChanged = true;
    $lastStatus[$row['job_id']] = $row['status'];
  }
}
$_SESSION['last_status'] = $lastStatus;
$stmtStatus->close();
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

  .cancel-btn {
    background: #ef4444;
    border: none;
    padding: 0.5rem 1.5rem;
    border-radius: 10px;
    color: white;
    font-weight: 500;
    transition: all 0.3s ease;
  }

  .cancel-btn:hover {
    background: #dc2626;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(239, 68, 68, 0.5);
  }

  .edit-salary-btn {
    background: #3b82f6;
    border: none;
    padding: 0.5rem 1.5rem;
    border-radius: 10px;
    color: white;
    font-weight: 500;
    transition: all 0.3s ease;
  }

  .edit-salary-btn:hover {
    background: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(59, 130, 246, 0.5);
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

  .alert-info {
    background-color: #dbeafe;
    color: #1e40af;
    border: 1px solid #3b82f6;
    border-radius: 10px;
    padding: 0.75rem;
    margin-bottom: 1rem;
  }

  .popover {
    max-width: 300px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }

  .popover-body {
    padding: 1rem;
    font-size: 0.9rem;
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

    .cancel-btn,
    .edit-salary-btn,
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
    <?php if ($statusChanged): ?>
    <div class="alert alert-info text-center">تم تغيير حالة أحد الطلبات! يرجى التحقق.</div>
    <?php endif; ?>

    <?php if ($result && $result->num_rows > 0): ?>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>اسم الوظيفة</th>
            <th>تاريخ التقديم</th>
            <th>الراتب المتوقع</th>
            <th>الحالة</th>
            <th>الإجراءات</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td>
              <?= htmlspecialchars($row['job_title']) ?>
              <button type="button" class="btn btn-link p-0 ms-2" data-bs-toggle="popover" data-bs-trigger="hover focus"
                data-bs-content="<?= htmlspecialchars($row['description'] ?? 'لا توجد تفاصيل') ?>">
                <i class="fas fa-info-circle text-primary"></i>
              </button>
            </td>
            <td><?= htmlspecialchars($row['applied_at']) ?></td>
            <td>
              <?= number_format((float)$row['expected_salary'], 2) ?> جنيه
              <button type="button" class="edit-salary-btn ms-2" data-bs-toggle="modal" data-bs-target="#editSalaryModal"
                data-job-id="<?= $row['job_id'] ?>" data-current-salary="<?= $row['expected_salary'] ?>">
                <i class="fas fa-edit"></i>
              </button>
            </td>
            <td class="status-<?= strtolower(str_replace(' ', '-', $row['status'])) ?>">
              <?= htmlspecialchars($row['status']) ?>
            </td>
            <td>
              <?php if (in_array(strtolower($row['status']), ['waiting'])): ?>
              <form method="POST" style="display:inline;" onsubmit="return confirm('هل أنت متأكد من إلغاء الطلب؟');">
                <input type="hidden" name="job_id" value="<?= $row['job_id'] ?>">
                <button type="submit" name="cancel_application" class="cancel-btn"><i class="fas fa-trash"></i> إلغاء
                  الطلب</button>
              </form>
              <?php elseif (in_array(strtolower($row['status']), ['accepted', 'rejected'])): ?>
              <button type="button" class="rate-btn" data-bs-toggle="modal" data-bs-target="#rateModal"
                data-company-id="<?= isset($row['company_id']) ? htmlspecialchars($row['company_id']) : '' ?>"
                data-job-id="<?= htmlspecialchars($row['job_id']) ?>">
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

    <!-- Modal لتعديل الراتب -->
    <div class="modal fade" id="editSalaryModal" tabindex="-1" aria-labelledby="editSalaryModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editSalaryModalLabel">تعديل الراتب المتوقع</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form method="POST">
              <input type="hidden" name="job_id" id="editJobId">
              <div class="mb-3">
                <label for="expected_salary" class="form-label">الراتب المتوقع (جنيه)</label>
                <input type="number" class="form-control" id="expected_salary" name="expected_salary" step="0.01"
                  required>
              </div>
              <button type="submit" name="update_salary" class="btn btn-primary w-100">حفظ التعديل</button>
            </form>
          </div>
        </div>
      </div>
    </div>

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
              <input type="hidden" name="job_id" id="rateJobId">
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
              <button type="submit" name="rate_job" class="btn btn-primary w-100">إرسال التقييم</button>
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

    // إعداد الـ Modal مع معرف الشركة والوظيفة
    const rateModal = document.getElementById('rateModal');
    rateModal.addEventListener('show.bs.modal', function(event) {
      const button = event.relatedTarget;
      const companyId = button.getAttribute('data-company-id');
      const jobId = button.getAttribute('data-job-id');
      if (companyId) {
        document.getElementById('rateCompanyId').value = companyId;
      } else {
        console.error('Company ID is undefined');
      }
      document.getElementById('rateJobId').value = jobId;
      ratingInput.value = 0;
      stars.forEach(star => star.classList.remove('active'));
      document.getElementById('comment').value = '';
    });

    // إعداد الـ Modal لتعديل الراتب
    const editSalaryModal = document.getElementById('editSalaryModal');
    editSalaryModal.addEventListener('show.bs.modal', function(event) {
      const button = event.relatedTarget;
      const jobId = button.getAttribute('data-job-id');
      const currentSalary = button.getAttribute('data-current-salary');
      document.getElementById('editJobId').value = jobId;
      document.getElementById('expected_salary').value = currentSalary;
    });

    // تهيئة Popover
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
  });
  </script>
</body>

</html>
<?php ob_end_flush(); ?>