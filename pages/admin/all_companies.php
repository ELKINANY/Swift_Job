<?php
require '../db_connection.php';
session_start();
// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Handle company deletion
if (isset($_GET['delete'])) {
    $company_id = $_GET['delete'];
    try {
        // First delete related jobs
        $stmt = $conn->prepare("DELETE FROM jobs WHERE company_id = ?");
        $stmt->bind_param("i", $company_id);
        $stmt->execute();
        
        // Then delete the company
        $stmt = $conn->prepare("DELETE FROM companies WHERE company_id = ?");
        $stmt->bind_param("i", $company_id);
        $stmt->execute();
        
        $_SESSION['success_message'] = 'تم حذف الشركة بنجاح'; 
        header('Location: all_companies.php');
        exit();
    } catch (mysqli_sql_exception $e) {
        $_SESSION['error_message'] = 'Error deleting company: ' . $e->getMessage();
        header('Location: all_companies.php');
        exit();
    }
}

// Build query for companies list
$query = "SELECT 
            c.company_id,
            c.name,
            c.email,
            c.phone,
            c.description,
            c.location,
            c.category,
            COUNT(j.job_id) as total_jobs,
            COUNT(DISTINCT ja.user_id) as unique_applicants,
            COUNT(ja.application_id) as total_applications,
            COUNT(p.project_id) as total_projects
          FROM companies c
          LEFT JOIN jobs j ON c.company_id = j.company_id
          LEFT JOIN job_applications ja ON j.job_id = ja.job_id
          LEFT JOIN projects p ON c.company_id = p.company_id
          GROUP BY c.company_id";

// Add search filters
$search = $_GET['search'] ?? '';
if (!empty($search)) {
    $query .= " HAVING (c.name LIKE ? OR c.description LIKE ? OR c.email LIKE ?)";
}

// Add sorting
$sort_by = $_GET['sort_by'] ?? 'company_id';
$sort_order = $_GET['sort_order'] ?? 'desc';
$query .= " ORDER BY " . $sort_by . " " . $sort_order;

// Pagination
$limit = 10;
$page = $_GET['page'] ?? 1;
$offset = ($page - 1) * $limit;
$query .= " LIMIT ? OFFSET ?";

// Count total companies for pagination
$count_query = "SELECT COUNT(*) as total FROM companies";
$result = $conn->query($count_query);
$total_companies = $result->fetch_assoc()['total'];
$total_pages = ceil($total_companies / $limit);

// Execute the main query
$stmt = $conn->prepare($query);
$params = [];
if (!empty($search)) {
    $search_term = "%$search%";
    $params = [$search_term, $search_term, $search_term, $limit, $offset];
} else {
    $params = [$limit, $offset];
}

if (!empty($search)) {
    $stmt->bind_param("ssssi", ...$params);
} else {
    $stmt->bind_param("ii", ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$companies = $result->fetch_all(MYSQLI_ASSOC);

// Handle company addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_company'])) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $description = $_POST['description'] ?? '';
    $location = $_POST['location'] ?? '';
    $category = $_POST['category'] ?? '';

    try {
        $stmt = $conn->prepare("INSERT INTO companies (name, email, phone, description, location, category) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $email, $phone, $description, $location, $category);
        $stmt->execute();

        $_SESSION['success_message'] = 'تم إضافة الشركة بنجاح';
    } catch (mysqli_sql_exception $e) {
        $_SESSION['error_message'] = 'خطأ في إضافة الشركة: ' . $e->getMessage();
    }
    header('Location: all_companies.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>جميع الشركات - لوحة تحكم الأدمن</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
  .company-logo {
    width: 30px;
    height: 30px;
    object-fit: cover;
    border-radius: 50%;
  }
  </style>
</head>

<body>
  <?php include 'includes/admin_header.php'; ?>

  <div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>جميع الشركات</h2>
      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCompanyModal">
        <i class="fas fa-plus"></i> إضافة شركة
      </button>
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
              placeholder="ابحث في الشركات...">
            <button class="btn btn-outline-secondary" type="submit">بحث</button>
          </div>
        </div>
        <div class="col-md-4">
          <select class="form-select" name="sort_by">
            <option value="company_id" <?php echo $sort_by === 'company_id' ? 'selected' : ''; ?>>تاريخ الانضمام
            </option>
            <option value="name" <?php echo $sort_by === 'name' ? 'selected' : ''; ?>>اسم الشركة</option>
            <option value="total_jobs" <?php echo $sort_by === 'total_jobs' ? 'selected' : ''; ?>>الوظائف المنشورة
            </option>
            <option value="total_applications" <?php echo $sort_by === 'total_applications' ? 'selected' : ''; ?>>
              الطلبات</option>
            <option value="total_projects" <?php echo $sort_by === 'total_projects' ? 'selected' : ''; ?>>المشاريع
            </option>
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
            <th>اسم الشركة</th>
            <th>البريد الإلكتروني</th>
            <th>رقم الهاتف</th>
            <th>الموقع</th>
            <th>التصنيف</th>
            <th>الوظائف المنشورة</th>
            <th>الحرف المنشورة</th>
            <th>الإجراءات</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($companies as $company): ?>
          <tr>
            <td><?php echo htmlspecialchars($company['name'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($company['email'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($company['phone'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($company['location'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($company['category'] ?? ''); ?></td>
            <td><?php echo !empty($company['total_jobs']) ? htmlspecialchars($company['total_jobs']) : '0'; ?></td>
            <td><?php echo !empty($company['total_projects']) ? htmlspecialchars($company['total_projects']) : '0'; ?>
            </td>
            <td>
              <div class="btn-group" role="group">
                <a href="company_details.php?company_id=<?php echo htmlspecialchars($company['company_id']); ?>"
                  class="btn btn-sm btn-info" title="مشاهدة التفاصيل">
                  <i class="fas fa-eye"></i>
                </a>
                <a href="?delete=<?php echo htmlspecialchars($company['company_id']); ?>" class="btn btn-sm btn-danger"
                  onclick="return confirm('هل أنت متأكد من أنك تريد حذف الشركة؟')" title="Delete Company">
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

    <!-- Add Company Modal -->
    <div class="modal fade" id="addCompanyModal" tabindex="-1" aria-labelledby="addCompanyModalLabel"
      aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addCompanyModalLabel">إضافة شركة جديدة</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
              <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
            <?php endif; ?>

            <form method="POST" id="addCompanyForm">
              <div class="mb-3">
                <label for="name" class="form-label">اسم الشركة</label>
                <input type="text" class="form-control" id="name" name="name" required>
              </div>

              <div class="mb-3">
                <label for="email" class="form-label">البريد الإلكتروني</label>
                <input type="email" class="form-control" id="email" name="email" required>
              </div>

              <div class="mb-3">
                <label for="phone" class="form-label">رقم الهاتف</label>
                <input type="text" class="form-control" id="phone" name="phone" required>
              </div>

              <div class="mb-3">
                <label for="description" class="form-label">الوصف</label>
                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
              </div>

              <div class="mb-3">
                <label for="location" class="form-label">الموقع</label>
                <input type="text" class="form-control" id="location" name="location" required>
              </div>

              <div class="mb-3">
                <label for="category" class="form-label">التصنيف</label>
                <input type="text" class="form-control" id="category" name="category" required>
              </div>

              <button type="submit" name="add_company" class="btn btn-primary">إضافة الشركة</button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
  <script>
  $(document).ready(function() {
    $('.table').DataTable({
      "paging": false,
      "info": false,
      "searching": false
    });

    $('#addCompanyForm').on('submit', function(e) {
      e.preventDefault();
      $.ajax({
        url: 'all_companies.php',
        type: 'POST',
        data: $(this).serialize() + '&add_company=1',
        success: function(response) {
          location.reload(); // Reload page to show success message
        },
        error: function() {
          alert('حدث خطأ أثناء الإضافة');
        }
      });
    });
  });
  </script>
</body>

</html>