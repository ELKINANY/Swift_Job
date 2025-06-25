<?php
session_start();

// التحقق من أن المستخدم مسجل دخول وهو شركة
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'company') {
    header("Location: ../login.php");
    exit();
}

require '../db_connection.php';

$project_id = $_GET['project_id'];
$company_id = $_SESSION['user_id'];

// جلب بيانات المشروع الحالية
$stmt = $conn->prepare("SELECT * FROM projects WHERE project_id = ? AND company_id = ?");
$stmt->bind_param("ii", $project_id, $company_id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$project) {
    header("Location: view_projects.php");
    exit();
}

// معالجة تحديث البيانات
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $budget = $_POST['budget'];
    $project_type = $_POST['project_type'];
    $location = $_POST['location'];
    $deadline = $_POST['deadline'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE projects SET 
                          title = ?, 
                          description = ?, 
                          budget = ?, 
                          project_type = ?, 
                          location = ?, 
                          deadline = ?, 
                          status = ?
                        WHERE project_id = ?");
    $stmt->bind_param("ssdssssi", $title, $description, $budget, $project_type, $location, $deadline, $status, $project_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "تم تحديث المشروع بنجاح";
        header("Location: view_projects.php");
        exit();
    } else {
        $error = "حدث خطأ أثناء تحديث المشروع";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="ltr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>تعديل المشروع</title>
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
        <h1>تعديل المشروع</h1>
        <a href="view_projects.php" class="btn btn-primary">
          <i class="fas fa-arrow-right"></i> رجوع
        </a>
      </div>

      <?php if (isset($error)): ?>
      <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <?= htmlspecialchars($error); ?>
      </div>
      <?php endif; ?>

      <div class="form-container">
        <form method="POST">
          <div class="mb-3">
            <label for="title" class="form-label">عنوان المشروع</label>
            <input type="text" class="form-control" id="title" name="title"
              value="<?= htmlspecialchars($project['title']); ?>" required>
          </div>

          <div class="mb-3">
            <label for="description" class="form-label">وصف المشروع</label>
            <textarea class="form-control" id="description" name="description" rows="5"
              required><?= htmlspecialchars($project['description']); ?></textarea>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="budget" class="form-label">الميزانية (ج.م)</label>
              <input type="number" class="form-control" id="budget" name="budget"
                value="<?= htmlspecialchars($project['budget']); ?>" required>
            </div>

            <div class="col-md-6 mb-3">
              <label for="project_type" class="form-label">نوع المشروع</label>
              <select class="form-select" id="project_type" name="project_type" required>
                <option value="construction" <?= $project['project_type'] == 'construction' ? 'selected' : ''; ?>>بناء
                </option>
                <option value="renovation" <?= $project['project_type'] == 'renovation' ? 'selected' : ''; ?>>ترميم
                </option>
                <option value="design" <?= $project['project_type'] == 'design' ? 'selected' : ''; ?>>تصميم</option>
                <option value="other" <?= $project['project_type'] == 'other' ? 'selected' : ''; ?>>أخرى</option>
              </select>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="location" class="form-label">الموقع</label>
              <input type="text" class="form-control" id="location" name="location"
                value="<?= htmlspecialchars($project['location']); ?>" required>
            </div>

            <div class="col-md-6 mb-3">
              <label for="deadline" class="form-label">موعد التسليم</label>
              <input type="date" class="form-control" id="deadline" name="deadline"
                value="<?= date('Y-m-d', strtotime($project['deadline'])); ?>" required>
            </div>
          </div>

          <div class="mb-3">
            <label for="status" class="form-label">حالة المشروع</label>
            <select class="form-select" id="status" name="status" required>
              <option value="open" <?= $project['status'] == 'open' ? 'selected' : ''; ?>>مفتوح</option>
              <option value="closed" <?= $project['status'] == 'closed' ? 'selected' : ''; ?>>مغلق</option>
            </select>
          </div>

          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i> حفظ التعديلات
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    console.log('Edit Project Page Loaded');
  });
  </script>
</body>

</html>