<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
  header('Location: login.php');
  exit();
}

require '../db_connection.php';

$user_id = $_SESSION['user_id'];

// جلب بيانات الحساب
$stmt = $conn->prepare("SELECT name, email , phone , description , location , category FROM companies WHERE company_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
  die("المستخدم غير موجود.");
}

// تحديث بيانات الحساب
$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $password = trim($_POST['password']);
  $location = trim($_POST['location']);
  $description = trim($_POST['description']);
  $category = trim($_POST['category']);
  $phone = trim($_POST['phone']);
  $hashed_password = password_hash($password, PASSWORD_DEFAULT);

  if (empty($name) || empty($email)) {
    $error_message = "يجب ملء جميع الحقول المطلوبة.";
  } else {
    if (!empty($password)) {
      // تحديث مع كلمة المرور
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $conn->prepare("UPDATE companies SET name = ?, email = ?, password = ?, phone = ?, location = ?, description = ?, category = ? WHERE company_id = ?");
      $stmt->bind_param("sssssssi", $name, $email, $hashed_password, $phone, $location, $description, $category, $user_id);
    } else {
      // تحديث بدون كلمة المرور
      $stmt = $conn->prepare("UPDATE companies SET name = ?, email = ?, phone = ?, location = ?, description = ?, category = ? WHERE company_id = ?");
      $stmt->bind_param("ssssssi", $name, $email, $phone, $location, $description, $category, $user_id);
    }

    // تنفيذ الاستعلام
    if ($stmt->execute()) {
      $success_message = "تم تحديث بيانات الشركة بنجاح!";
    } else {
      $error_message = "حدث خطأ أثناء تحديث البيانات: " . $stmt->error;
    }

    $stmt->close();
  }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="ltr">

<head>
  <meta charset=" UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>تعديل الحساب</title>
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

  .form-container {
    max-width: 800px;
    margin: 0 auto;
    background: white;
    padding: 2rem;
    border-radius: 1rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    animation: fadeIn 0.5s ease-out;
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

  .form-label {
    font-weight: 500;
    color: var(--dark);
    margin-bottom: 0.5rem;
  }

  .form-control,
  .form-select {
    border-radius: 0.5rem;
    border: 1px solid #d1d5db;
    padding: 0.75rem;
    font-size: 1rem;
    transition: border-color 0.3s ease;
  }

  .form-control:focus,
  .form-select:focus {
    border-color: var(--secondary);
    box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
    outline: none;
  }

  .form-control::placeholder {
    color: #9ca3af;
  }

  .btn {
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    font-weight: 500;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
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
    h1 {
      font-size: 1.5rem;
    }

    .form-container {
      padding: 1.5rem;
    }
  }
  </style>
</head>

<body>
  <div class="dashboard-container">
    <!-- الشريط الجانبي -->
    <?php include "./sidebar.php" ?>

    <!-- المحتوى الرئيسي -->
    <div class="content">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>تعديل الحساب</h1>
      </div>

      <?php if (!empty($success_message)): ?>
      <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?= htmlspecialchars($success_message); ?>
      </div>
      <?php endif; ?>

      <?php if (!empty($error_message)): ?>
      <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <?= htmlspecialchars($error_message); ?>
      </div>
      <?php endif; ?>

      <div class="form-container">
        <form method="POST">
          <div class="mb-3">
            <label class="form-label">اسم الشركة:</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']); ?>"
              required>
          </div>

          <div class="mb-3">
            <label class="form-label">البريد الإلكتروني:</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']); ?>"
              required>
          </div>

          <div class="mb-3">
            <label class="form-label">كلمة المرور الجديدة (اختياري):</label>
            <input type="password" name="password" class="form-control"
              placeholder="اترك الحقل فارغًا إذا كنت لا تريد تغيير كلمة المرور">
          </div>

          <div class="mb-3">
            <label class="form-label">رقم الهاتف:</label>
            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']); ?>"
              required>
          </div>

          <div class="mb-3">
            <label class="form-label">الموقع:</label>
            <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($user['location']); ?>"
              required>
          </div>

          <div class="mb-3">
            <label class="form-label">وصف الشركة:</label>
            <textarea name="description" class="form-control"
              required><?= htmlspecialchars($user['description']); ?></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">التخصص:</label>
            <input type="text" name="category" class="form-control" value="<?= htmlspecialchars($user['category']); ?>"
              required>
          </div>

          <div class="d-grid gap-2">
            <button type="submit" name="update_profile" class="btn btn-primary">
              <i class="fas fa-save"></i> تحديث الحساب
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    console.log('Edit Profile Page Loaded');
  });
  </script>
</body>

</html>