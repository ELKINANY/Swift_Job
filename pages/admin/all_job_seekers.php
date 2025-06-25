<?php
require '../db_connection.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// حذف المستخدم
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    try {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $_SESSION['success_message'] = 'تم حذف المستخدم بنجاح.';
        header('Location: all_users.php');
        exit();
    } catch (mysqli_sql_exception $e) {
        $_SESSION['error_message'] = 'حدث خطأ أثناء الحذف: ' . $e->getMessage();
        header('Location: all_users.php');
        exit();
    }
}

// البحث والفلترة
$search = $_GET['search'] ?? '';
$sort_by = $_GET['sort_by'] ?? 'name';
$sort_order = $_GET['sort_order'] ?? 'asc';
$user_type = $_GET['user_type'] ?? 'all'; // all, employee, craftsman

$allowed_sort = ['name', 'email', 'location', 'created_at'];
$allowed_order = ['asc', 'desc'];

$query = "SELECT user_id, name, email, phone, cv_link, location, role, specialization, created_at 
          FROM users 
          WHERE role IN ('employee', 'craftsman')";

if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $query .= " AND (name LIKE '%$search%' OR email LIKE '%$search%' OR specialization LIKE '%$search%')";
}

if ($user_type !== 'all') {
    $query .= " AND role = '$user_type'";
}

if (in_array($sort_by, $allowed_sort) && in_array($sort_order, $allowed_order)) {
    $query .= " ORDER BY $sort_by $sort_order";
}

$result = $conn->query($query);
$users = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <title>قائمة المستخدمين</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
  .admin-container {
    padding: 20px;
  }

  .admin-search {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
  }

  .badge-employee {
    background-color: #4e73df;
  }

  .badge-craftsman {
    background-color: #1cc88a;
  }

  .user-type-filter {
    margin-bottom: 15px;
  }
  </style>
</head>

<body>
  <?php include 'includes/admin_header.php'; ?>

  <div class="admin-container">
    <div class="container mt-4">
      <h2 class="mb-4">قائمة المستخدمين</h2>

      <?php if (isset($_SESSION['success_message'])): ?>
      <div class="alert alert-success"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
      <?php endif; ?>

      <?php if (isset($_SESSION['error_message'])): ?>
      <div class="alert alert-danger"><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
      <?php endif; ?>

      <!-- Search and Filters -->
      <form class="admin-search mb-4" method="GET">
        <div class="row">
          <div class="col-md-4">
            <div class="input-group">
              <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search); ?>"
                placeholder="ابحث عن مستخدمين...">
              <button class="btn btn-outline-secondary" type="submit">بحث</button>
            </div>
          </div>
          <div class="col-md-3">
            <select class="form-select" name="sort_by">
              <option value="name" <?= $sort_by === 'name' ? 'selected' : ''; ?>>الاسم</option>
              <option value="email" <?= $sort_by === 'email' ? 'selected' : ''; ?>>البريد الإلكتروني</option>
              <option value="location" <?= $sort_by === 'location' ? 'selected' : ''; ?>>الموقع</option>
              <option value="created_at" <?= $sort_by === 'created_at' ? 'selected' : ''; ?>>تاريخ التسجيل</option>
            </select>
          </div>
          <div class="col-md-2">
            <select class="form-select" name="sort_order">
              <option value="asc" <?= $sort_order === 'asc' ? 'selected' : ''; ?>>تصاعدي</option>
              <option value="desc" <?= $sort_order === 'desc' ? 'selected' : ''; ?>>تنازلي</option>
            </select>
          </div>
          <div class="col-md-3">
            <select class="form-select" name="user_type">
              <option value="all" <?= $user_type === 'all' ? 'selected' : ''; ?>>كل المستخدمين</option>
              <option value="employee" <?= $user_type === 'employee' ? 'selected' : ''; ?>>طالبو الوظيفة</option>
              <option value="craftsman" <?= $user_type === 'craftsman' ? 'selected' : ''; ?>>الحرفيون</option>
            </select>
          </div>
        </div>
      </form>

      <!-- جدول عرض المستخدمين -->
      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle text-center">
          <thead class="table-light">
            <tr>
              <th>الاسم</th>
              <th>البريد الإلكتروني</th>
              <th>رقم الهاتف</th>
              <th>النوع</th>
              <th>التخصص</th>
              <th>الموقع</th>
              <th>تاريخ التسجيل</th>
              <th>إجراءات</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($users) > 0): ?>
            <?php foreach ($users as $user): ?>
            <tr>
              <td><?= htmlspecialchars($user['name']) ?></td>
              <td><?= htmlspecialchars($user['email']) ?></td>
              <td><?= htmlspecialchars($user['phone']) ?></td>
              <td>
                <span class="badge <?= $user['role'] === 'employee' ? 'badge-employee' : 'badge-craftsman' ?>">
                  <?= $user['role'] === 'employee' ? 'طالب وظيفة' : 'حرفي' ?>
                </span>
              </td>
              <td><?= htmlspecialchars($user['specialization'] ?? 'غير محدد') ?></td>
              <td><?= htmlspecialchars($user['location']) ?></td>
              <td><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
              <td>
                <a href="?delete=<?= $user['user_id'] ?>" class="btn btn-sm btn-danger"
                  onclick="return confirm('هل أنت متأكد من حذف هذا المستخدم؟');" title="حذف المستخدم">
                  <i class="fas fa-trash"></i>
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php else: ?>
            <tr>
              <td colspan="8" class="text-muted">لا توجد نتائج.</td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>

</html>