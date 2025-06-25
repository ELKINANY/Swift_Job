<?php
require '../db_connection.php';
session_start();
// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: login.php');
  exit();
}

// Handle job deletion
if (isset($_GET['delete'])) {
  $job_id = $_GET['delete'];
  try {
    // First check if the job exists
    $check_stmt = $conn->prepare("SELECT job_id FROM jobs WHERE job_id = ?");
    $check_stmt->bind_param("i", $job_id);
    $check_stmt->execute();
    $check_stmt->store_result();
    if ($check_stmt->num_rows > 0) {
      $stmt = $conn->prepare("DELETE FROM jobs WHERE job_id = ?");
      $stmt->execute([$job_id]);
      $_SESSION['success_message'] = 'تم حذف الوظيفة بنجاح';
    } else {
      $_SESSION['error_message'] = 'الوظيفة غير موجودة';
    }
    header('Location: all_jobs.php');
    exit();
  } catch (PDOException $e) {
    $_SESSION['error_message'] = 'خطأ في حذف الوظيفة: ' . $e->getMessage();
    header('Location: all_jobs.php');
    exit();
  }
}

// Handle job status update
if (isset($_GET['status'])) {
  $job_id = $_GET['job_id'];
  $new_status = $_GET['status'] === 'active' ? 1 : 0;

  try {
    $stmt = $conn->prepare("UPDATE jobs SET status = ? WHERE job_id = ?");
    $stmt->execute([$new_status, $job_id]);
    $_SESSION['success_message'] = 'تم تحديث حالة الوظيفة بنجاح';
    header('Location: all_jobs.php');
    exit();
  } catch (PDOException $e) {
    $_SESSION['error_message'] = 'خطأ في تحديث حالة الوظيفة: ' . $e->getMessage();
    header('Location: all_jobs.php');
    exit();
  }
}

// Build query for jobs list
$query = "SELECT j.*, c.name, c.logo FROM jobs j 
          LEFT JOIN companies c ON j.company_id = c.company_id
          WHERE 1 = 1";

// Add search filters
$search = $_GET['search'] ?? '';
if (!empty($search)) {
  $query .= " AND (j.title LIKE ? OR j.description LIKE ? OR c.name LIKE ?)";
}

// Add sorting
$sort_by = $_GET['sort_by'] ?? 'created_at';
$sort_order = $_GET['sort_order'] ?? 'desc';
$query .= " ORDER BY j." . $sort_by . " " . $sort_order;

// Pagination
$limit = 10;
$page = $_GET['page'] ?? 1;
$offset = ($page - 1) * $limit;
$query .= " LIMIT ? OFFSET ?";

// Count total jobs for pagination
$count_query = "SELECT COUNT(*) as total FROM jobs";
$stmt = $conn->query($count_query);
$total_jobs = $stmt->num_rows;
$total_pages = ceil($total_jobs / $limit);

// Execute the main query
$stmt = $conn->prepare($query);
$params = [];

// Add search parameters if search is performed
if (!empty($search)) {
  $search_term = "%$search%";
  $params[] = $search_term;
  $params[] = $search_term;
  $params[] = $search_term;
}

// Add pagination parameters
$params[] = $limit;
$params[] = $offset;

try {
  $stmt->execute($params);
} catch (Exception $e) {
  if (strpos($e->getMessage(), 'Unknown column') !== false) {
    $query = str_replace('c.company_name', 'c.name', $query);
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
  } else {
    throw $e;
  }
}

// Fetch results using mysqli style
$result = $stmt->get_result();
$jobs = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<body>
  <?php include 'includes/admin_header.php'; ?>

  <div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>جميع الوظائف</h2>
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

    <!-- Search and Filters -->
    <form class="mb-4" method="GET">
      <div class="row">
        <div class="col-md-4">
          <div class="input-group">
            <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>"
              placeholder="ابحث في الوظائف...">
            <button class="btn btn-outline-secondary" type="submit">بحث</button>
          </div>
        </div>
        <div class="col-md-4">
          <select class="form-select" name="sort_by">
            <option value="created_at" <?php echo $sort_by === 'created_at' ? 'selected' : ''; ?>>تاريخ الإضافة</option>
            <option value="title" <?php echo $sort_by === 'title' ? 'selected' : ''; ?>>عنوان الوظيفة</option>
            <option value="salary" <?php echo $sort_by === 'salary' ? 'selected' : ''; ?>>الراتب</option>
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
            <th>شعار الشركة</th>
            <th>عنوان الوظيفة</th>
            <th>الشركة</th>
            <th>الموقع</th>
            <th>الراتب</th>
            <th>الحالة</th>
            <th>الإجراءات</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($jobs as $job): ?>
          <tr>
            <td>
              <?php if (!empty($job['logo'])): ?>
              <img src="<?php echo htmlspecialchars($job['logo'] ?? ''); ?>"
                alt="<?php echo htmlspecialchars($job['name'] ?? ''); ?>" class="company-logo me-2">
              <?php endif; ?>
            </td>
            <td>
              <a href="job_details.php?job_id=<?php echo htmlspecialchars($job['job_id'] ?? ''); ?>"
                target="_blank"><?php echo htmlspecialchars($job['title'] ?? ''); ?></a>
            </td>
            <td><?php echo htmlspecialchars($job['name'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($job['location'] ?? ''); ?></td>
            <td><?php echo !empty($job['salary']) ? htmlspecialchars($job['salary']) : 'غير محدد'; ?></td>
            <td>
              <span class="badge <?php echo ($job['status'] ?? 1) ? 'bg-success' : 'bg-warning'; ?>">
                <?php echo ($job['status'] ?? 1) ? 'نشطة' : 'غير نشطة'; ?>
              </span>
            </td>
            <td>
              <div class="btn-group" role="group">
                <a href="job_details.php?job_id=<?php echo htmlspecialchars($job['job_id']); ?>"
                  class="btn btn-sm btn-info" title="عرض التفاصيل">
                  <i class="fas fa-eye"></i>
                </a>
                <?php if ($job['status'] ?? 1): ?>
                <a href="?status=inactive&job_id=<?php echo htmlspecialchars($job['job_id']); ?>"
                  class="btn btn-sm btn-warning" onclick="return confirm('هل أنت متأكد أنك تريد تعطيل هذه الوظيفة؟')"
                  title="تعطيل الوظيفة">
                  <i class="fas fa-lock"></i>
                </a>
                <?php else: ?>
                <a href="?status=active&job_id=<?php echo htmlspecialchars($job['job_id']); ?>"
                  class="btn btn-sm btn-success" onclick="return confirm('هل أنت متأكد أنك تريد تفعيل هذه الوظيفة؟')"
                  title="تفعيل الوظيفة">
                  <i class="fas fa-check"></i>
                </a>
                <?php endif; ?>
                <a href="?delete=<?php echo htmlspecialchars($job['job_id']); ?>" class="btn btn-sm btn-danger"
                  onclick="return confirm('هل أنت متأكد أنك تريد حذف هذه الوظيفة؟')" title="حذف الوظيفة">
                  <i class="fas fa-trash"></i>
                </a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
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