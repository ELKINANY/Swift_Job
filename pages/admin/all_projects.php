<?php
require '../db_connection.php';
session_start();
// التحقق من أن المستخدم مسجل دخول وهو أدمن
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: login.php');
  exit();
}

// معالجة حذف المشروع
if (isset($_GET['delete'])) {
  $project_id = $_GET['delete'];
  try {
    // التحقق من وجود المشروع أولاً
    $check_stmt = $conn->prepare("SELECT project_id FROM projects WHERE project_id = ?");
    $check_stmt->bind_param("i", $project_id);
    $check_stmt->execute();
    $check_stmt->store_result();
    if ($check_stmt->num_rows > 0) {
      $stmt = $conn->prepare("DELETE FROM projects WHERE project_id = ?");
      $stmt->execute([$project_id]);
      $_SESSION['success_message'] = 'تم حذف المشروع بنجاح';
    } else {
      $_SESSION['error_message'] = 'المشروع غير موجود';
    }
    header('Location: all_projects.php');
    exit();
  } catch (Exception $e) {
    $_SESSION['error_message'] = 'خطأ في حذف المشروع: ' . $e->getMessage();
    header('Location: all_projects.php');
    exit();
  }
}

// معالجة تحديث حالة المشروع
if (isset($_GET['status'])) {
  $project_id = $_GET['project_id'];
  $new_status = $_GET['status'] === 'active' ? 'open' : 'closed';

  try {
    $stmt = $conn->prepare("UPDATE projects SET status = ? WHERE project_id = ?");
    $stmt->execute([$new_status, $project_id]);
    $_SESSION['success_message'] = 'تم تحديث حالة المشروع بنجاح';
    header('Location: all_projects.php');
    exit();
  } catch (Exception $e) {
    $_SESSION['error_message'] = 'خطأ في تحديث حالة المشروع: ' . $e->getMessage();
    header('Location: all_projects.php');
    exit();
  }
}

// بناء استعلام المشاريع
$query = "SELECT p.*, c.name AS company_name, c.logo 
          FROM projects p 
          JOIN companies c ON p.company_id = c.company_id
          WHERE 1 = 1";

// إضافة عوامل التصفية
$search = $_GET['search'] ?? '';
if (!empty($search)) {
  $query .= " AND (p.title LIKE ? OR p.description LIKE ? OR c.name LIKE ?)";
}

// إضافة الترتيب
$sort_by = $_GET['sort_by'] ?? 'created_at';
$sort_order = $_GET['sort_order'] ?? 'desc';
$query .= " ORDER BY p." . $sort_by . " " . $sort_order;

// التقسيم إلى صفحات
$limit = 10;
$page = $_GET['page'] ?? 1;
$offset = ($page - 1) * $limit;
$query .= " LIMIT ? OFFSET ?";

// حساب العدد الكلي للمشاريع للتقسيم
$count_query = "SELECT COUNT(*) as total FROM projects";
$stmt = $conn->query($count_query);
$total_projects = $stmt->num_rows;
$total_pages = ceil($total_projects / $limit);

// تنفيذ الاستعلام الرئيسي
$stmt = $conn->prepare($query);
$params = [];

// إضافة معاملات البحث إذا وجدت
if (!empty($search)) {
  $search_term = "%$search%";
  $params[] = $search_term;
  $params[] = $search_term;
  $params[] = $search_term;
}

// إضافة معاملات التقسيم إلى صفحات
$params[] = $limit;
$params[] = $offset;

try {
  $stmt->execute($params);
} catch (Exception $e) {
  if (strpos($e->getMessage(), 'Unknown column') !== false) {
    $query = str_replace('u.company_name', 'c.name', $query);
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
  } else {
    throw $e;
  }
}

// جلب النتائج
$result = $stmt->get_result();
$projects = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>جميع المشاريع - لوحة تحكم الأدمن</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
  .company-logo {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 50%;
  }

  .badge-open {
    background-color: #28a745;
  }

  .badge-closed {
    background-color: #dc3545;
  }

  .budget {
    font-weight: bold;
    color: #4e73df;
  }
  </style>
</head>

<body>
  <?php include 'includes/admin_header.php'; ?>

  <div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>جميع المشاريع</h2>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success">
      <?php
        echo $_SESSION['success_message'];
        unset($_SESSION['success_message']);
        ?>
    </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger">
      <?php
        echo $_SESSION['error_message'];
        unset($_SESSION['error_message']);
        ?>
    </div>
    <?php endif; ?>

    <!-- البحث والتصفية -->
    <form class="mb-4" method="GET">
      <div class="row">
        <div class="col-md-4">
          <div class="input-group">
            <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>"
              placeholder="ابحث في المشاريع...">
            <button class="btn btn-outline-secondary" type="submit">بحث</button>
          </div>
        </div>
        <div class="col-md-4">
          <select class="form-select" name="sort_by">
            <option value="created_at" <?php echo $sort_by === 'created_at' ? 'selected' : ''; ?>>تاريخ الإضافة</option>
            <option value="title" <?php echo $sort_by === 'title' ? 'selected' : ''; ?>>عنوان المشروع</option>
            <option value="budget" <?php echo $sort_by === 'budget' ? 'selected' : ''; ?>>الميزانية</option>
          </select>
        </div>
        <div class="col-md-2">
          <select class="form-select" name="sort_order">
            <option value="asc" <?php echo $sort_order === 'asc' ? 'selected' : ''; ?>>تصاعدي</option>
            <option value="desc" <?php echo $sort_order === 'desc' ? 'selected' : ''; ?>>تنازلي</option>
          </select>
        </div>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>صورة الشركة</th>
            <th>عنوان المشروع</th>
            <th>الشركة</th>
            <th>الموقع</th>
            <th>الميزانية</th>
            <th>الحالة</th>
            <th>الإجراءات</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($projects as $project): ?>
          <tr>
            <td>
              <?php if (!empty($project['logo'])): ?>
              <img src="../<?php echo htmlspecialchars($project['logo']); ?>"
                alt="<?php echo htmlspecialchars($project['company_name']); ?>" class="company-logo me-2">
              <?php endif; ?>
            </td>
            <td>
              <a href="project_details.php?project_id=<?php echo htmlspecialchars($project['project_id']); ?>"
                target="_blank"><?php echo htmlspecialchars($project['title']); ?></a>
            </td>
            <td><?php echo htmlspecialchars($project['company_name']); ?></td>
            <td><?php echo htmlspecialchars($project['location']); ?></td>
            <td class="budget"><?php echo number_format($project['budget'], 2); ?> ج.م</td>
            <td>
              <span class="badge <?php echo ($project['status'] === 'open') ? 'badge-open' : 'badge-closed'; ?>">
                <?php echo ($project['status'] === 'open') ? 'مفتوح' : 'مغلق'; ?>
              </span>
            </td>
            <td>
              <div class="btn-group" role="group">
                <a href="project_details.php?project_id=<?php echo htmlspecialchars($project['project_id']); ?>"
                  class="btn btn-sm btn-info" title="مشاهدة التفاصيل">
                  <i class="fas fa-eye"></i>
                </a>
                <?php if ($project['status'] === 'open'): ?>
                <a href="?status=inactive&project_id=<?php echo htmlspecialchars($project['project_id']); ?>"
                  class="btn btn-sm btn-warning" onclick="return confirm('هل أنت متأكد أنك تريد إغلاق هذا المشروع؟')"
                  title="إغلاق المشروع">
                  <i class="fas fa-lock"></i>
                </a>
                <?php else: ?>
                <a href="?status=active&project_id=<?php echo htmlspecialchars($project['project_id']); ?>"
                  class="btn btn-sm btn-success" onclick="return confirm('هل أنت متأكد أنك تريد فتح هذا المشروع؟')"
                  title="فتح المشروع">
                  <i class="fas fa-check"></i>
                </a>
                <?php endif; ?>

                <a href="?delete=<?php echo htmlspecialchars($project['project_id']); ?>" class="btn btn-sm btn-danger"
                  onclick="return confirm('هل أنت متأكد أنك تريد حذف هذا المشروع؟')" title="حذف المشروع">
                  <i class="fas fa-trash"></i>
                </a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- التقسيم إلى صفحات -->
    <nav aria-label="Page navigation" class="mt-4">
      <ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
          <a class="page-link"
            href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&sort_by=<?php echo $sort_by; ?>&sort_order=<?php echo $sort_order; ?>">
            <?php echo $i; ?>
          </a>
        </li>
        <?php endfor; ?>
      </ul>
    </nav>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
  <script>
  $(document).ready(function() {
    $('.table').DataTable({
      "language": {
        "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/ar.json"
      },
      "paging": false,
      "info": false,
      "searching": false
    });
  });
  </script>
</body>

</html>