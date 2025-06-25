<?php
require '../db_connection.php';
session_start();
// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Handle admin deletion
if (isset($_GET['delete'])) {
    $admin_id = $_GET['delete'];
    // Prevent deletion of current admin
    if ($admin_id != $_SESSION['user_id']) {
        try {
            $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND role = 'admin'");
            $stmt->execute([$admin_id]);
            $_SESSION['success_message'] = 'تم حذف المشرف بنجاح';
            header('Location: all_admins.php');
            exit();
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Error deleting admin: ' . $e->getMessage();
            header('Location: all_admins.php');
            exit();
        }
    } else {
        $_SESSION['error_message'] = 'لا يمكنك حذف حسابك الخاص';
        header('Location: all_admins.php');
        exit();
    }
}

// Handle adding new admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_admin'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'admin';

    try {
        // Check if email already exists
        $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $_SESSION['error_message'] = 'البريد الإلكتروني مسجل بالفعل';
        } else {
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssss", $name, $email, $password, $role);
            $stmt->execute();
            $_SESSION['success_message'] = 'تم إضافة المشرف بنجاح';
        }
        
        header('Location: all_admins.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error adding admin: ' . $e->getMessage();
        header('Location: all_admins.php');
        exit();
    }
}

// Build query for admins list
$query = "SELECT u.*
          FROM users u
          WHERE u.role = 'admin'";

// Add search filters
$search = $_GET['search'] ?? '';
if (!empty($search)) {
    $query .= " AND (u.name LIKE ? OR u.email LIKE ?)";
}

// Add sorting
$sort_by = $_GET['sort_by'] ?? 'created_at';
$sort_order = $_GET['sort_order'] ?? 'desc';
$query .= " GROUP BY u.user_id ORDER BY u." . $sort_by . " " . $sort_order;

// Pagination
$limit = 10;
$page = $_GET['page'] ?? 1;
$offset = ($page - 1) * $limit;
$query .= " LIMIT ? OFFSET ?";

// Count total admins for pagination
$count_query = "SELECT COUNT(*) as total FROM users WHERE role = 'admin'";
$result = $conn->query($count_query);
$total_admins = $result->fetch_assoc()['total'];
$total_pages = ceil($total_admins / $limit);

// Execute the main query
$stmt = $conn->prepare($query);
$params = [];
if (!empty($search)) {
    $search_term = "%$search%";
    $params = [$search_term, $search_term, $limit, $offset];
} else {
    $params = [$limit, $offset];
}

if (!empty($search)) {
    $stmt->bind_param("ssii", $params[0], $params[1], $params[2], $params[3]);
} else {
    $stmt->bind_param("ii", $params[0], $params[1]);
}
$stmt->execute();
$result = $stmt->get_result();
$admins = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>جميع المشرفين</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
  <?php include 'includes/admin_header.php'; ?>

  <div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>جميع المشرفين</h2>
      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminModal">
        <i class="fas fa-plus"></i> إضافة مشرف
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
              placeholder="ابحث عن مشرفين...">
            <button class="btn btn-outline-secondary" type="submit">بحث</button>
          </div>
        </div>
        <div class="col-md-4">
          <select class="form-select" name="sort_by">
            <option value="created_at" <?php echo $sort_by === 'created_at' ? 'selected' : ''; ?>>تاريخ التسجيل</option>
            <option value="name" <?php echo $sort_by === 'name' ? 'selected' : ''; ?>>الاسم</option>
            <option value="email" <?php echo $sort_by === 'email' ? 'selected' : ''; ?>>البريد الإلكتروني</option>
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
      <table class="table table-bordered">
        <thead class="table-dark">
          <tr>
            <th>الاسم</th>
            <th>البريد الإلكتروني</th>
            <th>الصلاحية</th>
            <th>الإجراءات</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($admins as $admin): ?>
          <tr>
            <td>
              <?php echo htmlspecialchars($admin['name']); ?>
              <?php if ($admin['user_id'] == $_SESSION['user_id']): ?>
              <span class="badge bg-info">حسابك</span>
              <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($admin['email']); ?></td>
            <td>
              <span class="badge bg-<?php echo $admin['role'] === 'super_admin' ? 'danger' : 'primary'; ?>">
                <?php echo $admin['role'] === 'super_admin' ? 'مشرف رئيسي' : 'مشرف'; ?>
              </span>
            </td>
            <td>
              <?php if ($admin['user_id'] != $_SESSION['user_id']): ?>
              <a href="?delete=<?php echo $admin['user_id']; ?>" class="btn btn-sm btn-danger"
                onclick="return confirm('هل أنت متأكد من حذف هذا المشرف؟');">
                <i class="fas fa-trash"></i> حذف
              </a>
              <?php endif; ?>
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

  <!-- Add Admin Modal -->
  <div class="modal fade" id="addAdminModal" tabindex="-1" aria-labelledby="addAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addAdminModalLabel">إضافة مشرف جديد</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="">
          <div class="modal-body">
            <div class="mb-3">
              <label for="name" class="form-label">الاسم الكامل</label>
              <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">البريد الإلكتروني</label>
              <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">كلمة المرور</label>
              <input type="password" class="form-control" id="password" name="password" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
            <button type="submit" name="add_admin" class="btn btn-primary">حفظ</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>