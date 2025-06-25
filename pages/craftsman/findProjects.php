<?php
require '../db_connection.php'; // الاتصال بقاعدة البيانات
include "../navBar.php";

$user_id = $_SESSION['user_id'] ?? null;
if ($user_id === null) {
  echo "<div class='alert alert-warning text-center'>يجب أن تكون مسجلاً للدخول للبحث عن المشاريع.</div>";
  exit;
}

// التحقق من وجود المستخدم في قاعدة البيانات
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
  echo "<div class='alert alert-danger text-center'>المستخدم غير موجود.</div>";
  exit;
}

// استلام البيانات من الحقول
$search_keyword = $_GET['keyword'] ?? '';
$search_location = $_GET['location'] ?? '';
$search_project_type = $_GET['project_type'] ?? '';

// تنفيذ استعلام البحث
$query = "SELECT * FROM projects WHERE status = 'open'";

// تطبيق الفلاتر حسب الإدخالات
if (!empty($search_keyword)) {
  $query .= " AND (title LIKE '%$search_keyword%' OR description LIKE '%$search_keyword%')";
}
if (!empty($search_location) && $search_location !== 'All') {
  $query .= " AND location = '$search_location'";
}
if (!empty($search_project_type) && $search_project_type !== 'All') {
  $query .= " AND project_type = '$search_project_type'";
}

$query .= " ORDER BY created_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>البحث عن مشاريع</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
  :root {
    --primary-color: #2c3e50;
    --secondary-color: #3498db;
    --accent-color: #e74c3c;
    --light-color: #f8f9fa;
    --dark-color: #343a40;
  }

  body {
    font-family: 'Tajawal', sans-serif;
    background-color: #f5f7fa;
    color: var(--primary-color);
  }

  .search-header {
    background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
    color: white;
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  }

  .filter-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    border: none;
    padding: 1.5rem;
  }

  .filter-card:hover {
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    transform: translateY(-5px);
  }

  .form-control,
  .form-select {
    padding: 12px 15px;
    border-radius: 10px;
    border: 1px solid #e0e0e0;
    transition: all 0.3s;
  }

  .form-control:focus,
  .form-select:focus {
    border-color: var(--secondary-color);
    box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
  }

  .search-btn {
    background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
    border: none;
    padding: 12px 30px;
    font-weight: 600;
    letter-spacing: 0.5px;
    transition: all 0.3s;
  }

  .search-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(37, 117, 252, 0.3);
  }

  .reset-btn {
    border: 2px solid var(--secondary-color);
    color: var(--secondary-color);
    background: transparent;
    padding: 10px 25px;
    transition: all 0.3s;
  }

  .reset-btn:hover {
    background: var(--secondary-color);
    color: white;
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

  .project-card.expired {
    opacity: 0.7;
    border-left: 5px solid #ef4444;
  }

  .project-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #e2e8f0;
    margin-bottom: 0.5rem;
  }

  .badge-expired {
    background: #ef4444;
    color: white;
    font-size: 0.85rem;
    padding: 0.3rem 0.75rem;
    border-radius: 20px;
  }

  .project-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1rem;
  }

  .meta-item {
    display: flex;
    align-items: center;
    color: #94a3b8;
    font-size: 0.95rem;
  }

  .meta-item i {
    margin-left: 0.5rem;
    color: var(--secondary-color);
    transition: transform 0.3s ease;
  }

  .meta-item:hover i {
    transform: scale(1.2);
  }

  .project-budget {
    font-size: 1.3rem;
    font-weight: 700;
    color: #34d399;
    margin-bottom: 1rem;
  }

  .project-actions {
    display: flex;
    gap: 1rem;
  }

  .details-btn,
  .bid-btn {
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    font-weight: 500;
    transition: all 0.3s ease;
  }

  .details-btn {
    background: transparent;
    border: 2px solid var(--secondary-color);
    color: var(--secondary-color);
  }

  .details-btn:hover {
    background: var(--secondary-color);
    color: white;
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.5);
  }

  .bid-btn {
    background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
    color: white;
    border: none;
  }

  .bid-btn:hover {
    background: linear-gradient(135deg, #5400b2, #1e40af);
    box-shadow: 0 5px 15px rgba(37, 117, 252, 0.5);
  }

  .results-count {
    background: var(--secondary-color);
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-weight: 600;
  }

  .no-results {
    text-align: center;
    padding: 3rem;
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
  }

  .no-results i {
    font-size: 3rem;
    color: #bdc3c7;
    margin-bottom: 1rem;
  }

  @media (max-width: 768px) {
    .project-meta {
      flex-direction: column;
      gap: 0.5rem;
    }

    .project-actions {
      flex-direction: column;
    }

    .details-btn,
    .bid-btn {
      width: 100%;
      text-align: center;
    }
  }
  </style>
</head>

<body>
  <div class="container py-5">
    <!-- عنوان الصفحة -->
    <div class="search-header text-center">
      <h1 class="fw-bold mb-3">ابحث عن مشاريع تناسب مهاراتك</h1>
      <p class="mb-0">اطلع على أحدث المشاريع المتاحة وقدم عرضك لتحصل على فرصة العمل</p>
    </div>

    <!-- نموذج البحث -->
    <form method="GET" class="mb-5">
      <div class="filter-card">
        <div class="row g-3">
          <!-- بحث بالكلمات المفتاحية -->
          <div class="col-md-5">
            <div class="form-group">
              <label class="form-label fw-semibold mb-2">ابحث بكلمات مفتاحية</label>
              <div class="input-group">
                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                <input type="text" name="keyword" class="form-control"
                  placeholder="مثال: أعمال نجارة، تمديدات صحية، دهانات..."
                  value="<?= htmlspecialchars($search_keyword) ?>">
              </div>
            </div>
          </div>

          <!-- البحث بالموقع -->
          <div class="col-md-3">
            <div class="form-group">
              <label class="form-label fw-semibold mb-2">الموقع</label>
              <select name="location" class="form-select">
                <option value="All">جميع المواقع</option>
                <option value="Mansoura" <?= $search_location === 'Mansoura' ? 'selected' : '' ?>>المنصورة</option>
                <option value="Cairo" <?= $search_location === 'Cairo' ? 'selected' : '' ?>>القاهرة</option>
                <option value="Luxor" <?= $search_location === 'Luxor' ? 'selected' : '' ?>>الأقصر</option>
                <option value="Alexandria" <?= $search_location === 'Alexandria' ? 'selected' : '' ?>>الإسكندرية
                </option>
              </select>
            </div>
          </div>

          <!-- البحث بنوع المشروع -->
          <div class="col-md-2">
            <div class="form-group">
              <label class="form-label fw-semibold mb-2">نوع المشروع</label>
              <select name="project_type" class="form-select">
                <option value="All">جميع الأنواع</option>
                <option value="construction" <?= $search_project_type === 'construction' ? 'selected' : '' ?>>إنشاءات
                </option>
                <option value="plumbing" <?= $search_project_type === 'plumbing' ? 'selected' : '' ?>>تمديدات صحية
                </option>
                <option value="electrical" <?= $search_project_type === 'electrical' ? 'selected' : '' ?>>أعمال كهربائية
                </option>
                <option value="painting" <?= $search_project_type === 'painting' ? 'selected' : '' ?>>دهانات</option>
                <option value="carpentry" <?= $search_project_type === 'carpentry' ? 'selected' : '' ?>>نجارة</option>
              </select>
            </div>
          </div>

          <!-- أزرار البحث -->
          <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn search-btn w-100">
              <i class="fas fa-search me-2"></i> بحث
            </button>
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-12 text-center">
            <button type="reset" class="btn reset-btn">
              <i class="fas fa-undo me-2"></i> إعادة تعيين
            </button>
          </div>
        </div>
      </div>
    </form>

    <!-- عرض المشاريع -->
    <div class="row">
      <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h3 class="fw-bold mb-0">نتائج البحث</h3>
          <span class="results-count"><?= $result->num_rows ?> مشروع</span>
        </div>

        <?php if ($result->num_rows > 0): ?>
        <?php while ($project = $result->fetch_assoc()): ?>
        <div class="project-card <?= ($project['deadline'] < date('Y-m-d')) ? 'expired' : '' ?>">
          <div class="card-body">
            <div class="d-md-flex justify-content-between">
              <div class="mb-3 mb-md-0">
                <h3 class="project-title">
                  <?= htmlspecialchars($project['title']) ?>
                  <?php if ($project['deadline'] < date('Y-m-d')): ?>
                  <span class="badge badge-expired ms-2">منتهي</span>
                  <?php endif; ?>
                </h3>

                <div class="project-meta">
                  <div class="meta-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span><?= htmlspecialchars($project['location']) ?></span>
                  </div>
                  <div class="meta-item">
                    <i class="fas fa-briefcase"></i>
                    <span><?= htmlspecialchars($project['project_type']) ?></span>
                  </div>
                  <div class="meta-item">
                    <i class="fas fa-clock"></i>
                    <span>ينتهي في <?= date('Y-m-d', strtotime($project['deadline'])) ?></span>
                  </div>
                </div>

                <p class="text-muted"><?= substr(htmlspecialchars($project['description']), 0, 150) ?>...</p>
              </div>

              <div class="text-md-end">
                <div class="project-budget">
                  <i class="fas fa-money-bill-wave"></i>
                  <?= number_format($project['budget'], 2) ?> ج.م
                </div>

                <div class="project-actions">
                  <a href="project_details.php?project_id=<?= $project['project_id'] ?>" class="btn details-btn">
                    <i class="fas fa-eye"></i> التفاصيل
                  </a>

                  <?php if (!($project['deadline'] < date('Y-m-d'))): ?>
                  <a href="apply_job.php?project_id=<?= $project['project_id'] ?>" class="btn bid-btn">
                    <i class="fas fa-paper-plane"></i> تقديم عرض
                  </a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php endwhile; ?>
        <?php else: ?>
        <div class="no-results">
          <i class="fas fa-folder-open"></i>
          <h3 class="mb-3">لا توجد مشاريع متاحة</h3>
          <p class="text-muted">لا توجد مشاريع تطابق معايير البحث الخاصة بك. حاول تعديل معايير البحث.</p>
          <a href="find_projects.php" class="btn btn-primary mt-2">
            <i class="fas fa-undo"></i> عرض جميع المشاريع
          </a>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php $conn->close(); ?>