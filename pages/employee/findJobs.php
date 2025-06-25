<?php
require '../db_connection.php'; // الاتصال بقاعدة البيانات
include "../navBar.php";

$user_id = $_SESSION['user_id'] ?? null; // إذا كان المستخدم مسجل دخول
if ($user_id === null) {
  echo "يجب أن تكون مسجلاً للدخول للبحث عن الوظائف.";
  exit;
}

// التحقق من وجود المستخدم في قاعدة البيانات
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
  echo "المستخدم غير موجود.";
  exit;
}

// استلام البيانات من الحقول
$search_keyword = $_GET['keyword'] ?? '';
$search_location = $_GET['location'] ?? '';
$search_specialization = $_GET['specialization'] ?? '';
$search_type = $_GET['job_type'] ?? '';
$search_jobs = $_GET['selected_jobs'] ?? [];

// تنفيذ استعلام البحث
$query = "SELECT * FROM jobs WHERE 1";

// تطبيق الفلاتر حسب الإدخالات
if (!empty($search_keyword)) {
  $query .= " AND (title LIKE '%$search_keyword%' OR description LIKE '%$search_keyword%')";
}
if (!empty($search_location) && $search_location !== 'All') {
  $query .= " AND location = '$search_location'";
}
if (!empty($search_specialization) && $search_specialization !== 'All') {
  $query .= " AND specialization = '$search_specialization'";
}
if (!empty($search_type) && $search_type !== 'All') {
  $query .= " AND job_type = '$search_type'";
}
if (!empty($search_jobs) && is_array($search_jobs)) {
  $jobs_in_query = implode(',', array_map(function ($job_id) {
    return "'" . $job_id . "'";
  }, $search_jobs));
  $query .= " AND job_id IN ($jobs_in_query)";
}

$query .= " ORDER BY created_at DESC";
$result = $conn->query($query);

// إضافة البيانات إلى جدول job_searches بعد تنفيذ البحث
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $search_date = date('Y-m-d H:i:s'); // تاريخ ووقت البحث
  $query = "INSERT INTO job_searches (user_id, keyword, location, specialization, job_type) VALUES (?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("issss", $user_id, $search_keyword, $search_location, $search_specialization, $search_type);
  $stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="ar">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>البحث عن الوظائف</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Tajawal', sans-serif;
      background-color: #f8f9fa;
    }

    .results-title {
      color: #2c3e50;
      font-weight: 700;
      border-bottom: 2px solid #3498db;
      padding-bottom: 10px;
      display: inline-block;
    }

    .filter-card {
      transition: all 0.3s ease;
      border: none;
    }

    .filter-card:hover {
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: #2575fc;
      box-shadow: 0 0 0 0.25rem rgba(37, 117, 252, 0.25);
    }

    .search-btn {
      transition: all 0.3s ease;
    }

    .search-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(37, 117, 252, 0.3);
    }

    .job-card {
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

    .job-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
    }

    .job-card.expired-job {
      opacity: 0.7;
      border-left: 5px solid #ef4444;
    }

    .job-card-body {
      padding: 0;
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      align-items: center;
    }

    .job-header {
      display: flex;
      align-items: center;
      margin-bottom: 10px;
      width: 100%;
    }

    .job-title {
      font-size: 1.4rem;
      font-weight: 700;
      color: #e2e8f0;
      margin: 0 15px 0 0;
    }

    .job-meta {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      margin-bottom: 15px;
      width: 100%;
    }

    .meta-item {
      display: flex;
      align-items: center;
      color: #94a3b8;
      font-size: 0.95rem;
    }

    .meta-item i {
      margin-left: 5px;
      color: #3b82f6;
      transition: transform 0.3s ease;
    }

    .meta-item:hover i {
      transform: scale(1.2);
    }

    .job-secondary-info {
      display: flex;
      gap: 20px;
      margin-top: 10px;
    }

    .salary-info,
    .date-info {
      display: flex;
      align-items: center;
      font-weight: 600;
    }

    .salary-info i {
      color: #34d399;
    }

    .date-info i {
      color: #9b59b6;
    }

    .job-actions {
      display: flex;
      gap: 10px;
      margin-top: 15px;
    }

    .details-btn,
    .apply-btn,
    .favorite-btn {
      border-radius: 8px;
      padding: 8px 20px;
      font-weight: 600;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .details-btn {
      background: transparent;
      border: 2px solid #3b82f6;
      color: #3b82f6;
    }

    .details-btn:hover {
      background: #3b82f6;
      color: white;
      box-shadow: 0 5px 15px rgba(59, 130, 246, 0.5);
    }

    .apply-btn {
      background: linear-gradient(135deg, #1e3a8a, #3b82f6);
      border: none;
      color: white;
    }

    .apply-btn:hover {
      background: linear-gradient(135deg, #163072, #2f69c3);
      box-shadow: 0 5px 15px rgba(59, 130, 246, 0.5);
    }

    .favorite-btn {
      background: transparent;
      border: 2px solid #ff6b6b;
      color: #ff6b6b;
    }

    .favorite-btn:hover {
      background: #ff6b6b;
      color: white;
      box-shadow: 0 5px 15px rgba(255, 107, 107, 0.5);
    }

    @media (max-width: 768px) {
      .job-card-body {
        flex-direction: column;
        align-items: flex-start;
      }

      .job-meta {
        flex-direction: column;
        gap: 10px;
      }

      .job-secondary-info {
        flex-direction: column;
        gap: 10px;
        width: 100%;
        margin: 15px 0;
      }

      .job-actions {
        width: 100%;
        justify-content: flex-start;
      }

      .details-btn,
      .apply-btn,
      .favorite-btn {
        width: 100%;
      }
    }
  </style>
</head>

<body>
  <div class="container mt-5">
    <h2 class="text-center mb-4 fw-bold text-primary" style="font-family: 'Tajawal', sans-serif;">ابحث عن وظيفتك
      المثالية</h2>

    <form method="GET" class="job-search-form">
      <div class="filter-card shadow-lg rounded-4 p-4 bg-white">
        <div class="row g-4">
          <!-- بحث بالكلمات المفتاحية -->
          <div class="col-md-5">
            <div class="form-group">
              <label class="form-label fw-semibold mb-2">ابحث بكلمات مفتاحية</label>
              <div class="input-group border rounded-3 overflow-hidden">
                <span class="input-group-text bg-white border-0"><i class="fas fa-search text-muted"></i></span>
                <input type="text" name="keyword" class="form-control border-0 py-3"
                  placeholder="مثال: مطور ويب، مسوق رقمي، شركة التقنية..." style="box-shadow: none!important;"
                  value="<?= htmlspecialchars($search_keyword); ?>">
              </div>
            </div>
          </div>

          <!-- البحث بالموقع -->
          <div class="col-md-3">
            <div class="form-group">
              <label class="form-label fw-semibold mb-2">الموقع</label>
              <select name="location" class="form-select py-3 border rounded-3"
                value="<?= htmlspecialchars($search_location); ?>">
                <option value="All">جميع المواقع</option>
                <option value="Mansoura" <?= $search_location === 'Mansoura' ? 'selected' : ''; ?>>المنصورة</option>
                <option value="Cairo" <?= $search_location === 'Cairo' ? 'selected' : ''; ?>>القاهره</option>
                <option value="Luxor" <?= $search_location === 'Luxor' ? 'selected' : ''; ?>>الأقصر</option>
              </select>
            </div>
          </div>

          <!-- البحث بالتخصص -->
          <div class="col-md-2">
            <div class="form-group">
              <label class="form-label fw-semibold mb-2">التخصص</label>
              <select name="specialization" class="form-select py-3 border rounded-3"
                value="<?= htmlspecialchars($search_specialization); ?>">
                <option value="All">جميع التخصصات</option>
                <option value="IT" <?= $search_specialization === 'IT' ? 'selected' : ''; ?>>تكنولوجيا المعلومات
                </option>
                <option value="Marketing" <?= $search_specialization === 'Marketing' ? 'selected' : ''; ?>>التسويق
                </option>
                <option value="Finance" <?= $search_specialization === 'Finance' ? 'selected' : ''; ?>>المالية</option>
                <option value="Design" <?= $search_specialization === 'Design' ? 'selected' : ''; ?>>التصميم</option>
              </select>
            </div>
          </div>

          <!-- البحث بنوع الوظيفة -->
          <div class="col-md-2">
            <div class="form-group">
              <label class="form-label fw-semibold mb-2">نوع الوظيفة</label>
              <select name="job_type" class="form-select py-3 border rounded-3"
                value="<?= htmlspecialchars($search_type); ?>">
                <option value="All">جميع الأنواع</option>
                <option value="Full Time" <?= $search_type === 'Full Time' ? 'selected' : ''; ?>>دوام كامل</option>
                <option value="Part Time" <?= $search_type === 'Part Time' ? 'selected' : ''; ?>>دوام جزئي</option>
                <option value="Remote" <?= $search_type === 'Remote' ? 'selected' : ''; ?>>عن بُعد</option>
                <option value="Freelance" <?= $search_type === 'Freelance' ? 'selected' : ''; ?>>عمل حر</option>
              </select>
            </div>
          </div>
        </div>

        <div class="row mt-4">
          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary search-btn px-5 py-3 rounded-pill fw-bold"
              style="background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); border: none;">
              <i class="fas fa-search me-2"></i> ابحث عن الوظائف
            </button>
            <button type="reset" class="btn btn-outline-secondary ms-3 px-4 py-3 rounded-pill fw-bold">
              <i class="fas fa-undo me-2"></i> إعادة تعيين
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>

  <!-- عرض الوظائف -->
  <div class="row mt-4">
    <div class="col-md-12">
      <h4 class="results-title mb-4">نتائج الوظائف <span class="badge bg-primary"><?= $result->num_rows ?> وظيفة</span>
      </h4>

      <?php while ($job = $result->fetch_assoc()): ?>
        <div
          class="job-card <?= ($job['expiration_date'] < date('Y-m-d') || $job['status'] == 0) ? 'expired-job' : '' ?>">
          <div class="job-card-body">
            <div class="job-main-info">
              <div class="job-header">
                <h2 class="job-title"><?= htmlspecialchars($job['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h2>
                <?php if ($job['expiration_date'] < date('Y-m-d') || $job['status'] == 0): ?>
                  <span class="badge bg-danger ms-2 p-2 ">منتهية</span>
                <?php endif; ?>
              </div>

              <div class="job-meta">
                <div class="meta-item">
                  <i class="fas fa-map-marker-alt"></i>
                  <span><?= htmlspecialchars($job['location'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="meta-item">
                  <i class="fas fa-briefcase"></i>
                  <span><?= htmlspecialchars($job['specialization'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
              </div>
            </div>

            <div class="job-secondary-info">
              <div class="salary-info">
                <i class="fas fa-dollar-sign"></i>
                <span><?= number_format((float) $job['salary'], 2); ?> <small>شهريا</small></span>
              </div>
              <div class="date-info">
                <i class="fas fa-calendar-alt"></i>
                <span><?= date('Y-m-d', strtotime($job['created_at'])); ?></span>
              </div>
            </div>

            <div class="job-actions">
              <a href="job_details.php?job_id=<?= $job['job_id']; ?>" class="btn details-btn">
                <i class="fas fa-eye"></i> التفاصيل
              </a>

              <?php if (!($job['expiration_date'] < date('Y-m-d') || $job['status'] == 0)): ?>
                <a href="apply_job.php?job_id=<?= $job['job_id']; ?>" class="btn apply-btn">
                  <i class="fas fa-paper-plane"></i> التقديم
                </a>
              <?php endif; ?>

              <a href="add_to_favorites.php?job_id=<?= $job['job_id']; ?>" class="btn favorite-btn">
                <i class="fas fa-heart"></i> المفضلة
              </a>

              <a href="compare_jobs.php?add=<?= $job['job_id']; ?>" class="btn btn-info btn-sm">إضافة للمقارنة</a>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php $conn->close(); ?>