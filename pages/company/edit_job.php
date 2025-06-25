<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header('Location: login.php');
    exit();
}

require '../db_connection.php';

// التحقق من وجود job_id في الرابط
if (!isset($_GET['job_id'])) {
    die("لم يتم تحديد الوظيفة.");
}

$job_id = intval($_GET['job_id']);
$company_id = $_SESSION['user_id'];

// جلب بيانات الوظيفة للتعديل
$stmt = $conn->prepare("SELECT * FROM jobs WHERE job_id = ? AND company_id = ?");
$stmt->bind_param("ii", $job_id, $company_id);
$stmt->execute();
$job = $stmt->get_result()->fetch_assoc();

if (!$job) {
    die("الوظيفة غير موجودة أو لا تملك صلاحية لتعديلها.");
}

// تحديث بيانات الوظيفة
$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_job'])) {
    $title = trim($_POST['title']);
    $job_type = trim($_POST['job_type']);
    $salary = trim($_POST['salary']);
    $description = trim($_POST['description']);
    $requirements = trim($_POST['requirements']);
    $test_question = trim($_POST['test_question']);

    if (empty($title) || empty($job_type) || empty($salary) || empty($description) || empty($requirements)) {
        $error_message = "يجب ملء جميع الحقول المطلوبة.";
    } else {
        $stmt = $conn->prepare("UPDATE jobs SET title = ?, job_type = ?, salary = ?, description = ?, requirements = ?, test_question = ? WHERE job_id = ? AND company_id = ?");
        $stmt->bind_param("ssdsssii", $title, $job_type, $salary, $description, $requirements, $test_question, $job_id, $company_id);

        if ($stmt->execute()) {
            $success_message = "تم تحديث الوظيفة بنجاح.";
        } else {
            $error_message = "حدث خطأ أثناء تحديث الوظيفة.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="ltr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>تعديل وظيفة</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
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

  /* Form Card */
  .form-card {
    background: white;
    border-radius: 1rem;
    padding: 2rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    max-width: 900px;
    margin: 0 auto;
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

  /* Form Fields */
  .form-control,
  .form-select {
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
    padding: 0.75rem;
    transition: all 0.3s ease;
  }

  .form-control:focus,
  .form-select:focus {
    border-color: var(--secondary);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    outline: none;
  }

  textarea.form-control {
    min-height: 150px;
    resize: vertical;
  }

  .input-group-text {
    background-color: var(--light);
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem 0 0 0.5rem;
  }

  .form-label {
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 0.5rem;
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

  /* Button */
  .btn-primary {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    border: none;
    padding: 0.75rem 2rem;
    border-radius: 0.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
  }

  .btn-primary:hover {
    background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }

  .btn-primary i {
    margin-left: 0.5rem;
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
    .form-card {
      padding: 1.5rem;
    }

    h1 {
      font-size: 1.5rem;
    }

    .btn-primary {
      width: 100%;
    }
  }
  </style>
</head>

<body>
  <div class="dashboard-container">
    <!-- الشريط الجانبي -->
    <?php include "./sidebar.php" ?>

    <!-- المحتوى الرئيسي -->
    <main class="content">
      <div class="form-card">
        <h1>تعديل وظيفة: <?= htmlspecialchars($job['title']); ?></h1>

        <?php if (!empty($success_message)): ?>
        <div class="alert alert-success">
          <i class="fas fa-check-circle"></i>
          <?= $success_message; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-circle"></i>
          <?= $error_message; ?>
        </div>
        <?php endif; ?>

        <form method="POST" class="row g-3">
          <div class="col-md-6">
            <label class="form-label">المسمى الوظيفي</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($job['title']); ?>"
              required>
          </div>

          <div class="col-md-6">
            <label class="form-label">نوع الوظيفة</label>
            <select name="job_type" class="form-select" required>
              <option value="Full Time" <?= $job['job_type'] === 'Full Time' ? 'selected' : ''; ?>>دوام كامل</option>
              <option value="Part Time" <?= $job['job_type'] === 'Part Time' ? 'selected' : ''; ?>>دوام جزئي</option>
              <option value="Contract" <?= $job['job_type'] === 'Contract' ? 'selected' : ''; ?>>عقد</option>
              <option value="Internship" <?= $job['job_type'] === 'Internship' ? 'selected' : ''; ?>>تدريب</option>
              <option value="WARDIA" <?= $job['job_type'] === 'WARDIA' ? 'selected' : ''; ?>>وردية</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">الراتب</label>
            <div class="input-group">
              <input type="number" name="salary" class="form-control" step="0.01"
                value="<?= htmlspecialchars($job['salary']); ?>" required>
              <span class="input-group-text">ج.م</span>
            </div>
          </div>

          <div class="col-12">
            <label class="form-label">الوصف</label>
            <textarea name="description" class="form-control"
              required><?= htmlspecialchars($job['description']); ?></textarea>
          </div>

          <div class="col-12">
            <label class="form-label">المتطلبات</label>
            <textarea name="requirements" class="form-control"
              required><?= htmlspecialchars($job['requirements']); ?></textarea>
          </div>

          <div class="col-12">
            <label class="form-label">السؤال الاختباري (اختياري)</label>
            <textarea name="test_question"
              class="form-control"><?= htmlspecialchars($job['test_question']); ?></textarea>
          </div>

          <div class="col-12 text-center mt-4">
            <button type="submit" name="update_job" class="btn btn-primary">
              <i class="fas fa-save"></i> تحديث الوظيفة
            </button>
          </div>
        </form>
      </div>
    </main>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    console.log('Edit Job Page Loaded');
  });
  </script>
</body>

</html>