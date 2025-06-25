<?php
include "../navBar.php";
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
    header('Location: ../login.php');
    exit();
}

require '../db_connection.php';

$user_id = $_SESSION['user_id'];

// جلب السيرة الذاتية الحالية
$stmt = $conn->prepare("SELECT cv_link FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$current_cv = $user['cv_link'] ?? null;

$success_message = "";
$error_message = "";

// معالجة رفع أو تحديث السيرة الذاتية
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['cv'])) {
    $cv = $_FILES['cv'];

    // التحقق من نوع الملف (PDF فقط)
    $allowed_types = ['application/pdf'];
    if (!in_array($cv['type'], $allowed_types)) {
        $error_message = "يجب أن يكون الملف بصيغة PDF فقط.";
    } elseif ($cv['size'] > 2 * 1024 * 1024) { // 2MB كحد أقصى
        $error_message = "حجم الملف يجب ألا يتجاوز 2 ميجابايت.";
    } else {
        // حفظ الملف في مجلد "uploads"
        $uploads_dir = '../uploads/cvs/';
        if (!is_dir($uploads_dir)) {
            mkdir($uploads_dir, 0777, true);
        }

        $cv_name = "cv_" . $user_id . ".pdf";
        $cv_path = $uploads_dir . $cv_name;

        if (move_uploaded_file($cv['tmp_name'], $cv_path)) {
            // رابط السيرة الذاتية
            $cv_link = "uploads/cvs/" . $cv_name;

            // تحديث السيرة الذاتية في جدول users
            $stmt = $conn->prepare("UPDATE users SET cv_link = ? WHERE user_id = ?");
            $stmt->bind_param("si", $cv_link, $user_id);

            if ($stmt->execute()) {
                // التحقق إذا كانت السيرة الذاتية موجودة في جدول resumes
                $stmt_resume = $conn->prepare("SELECT resume_id FROM resumes WHERE user_id = ?");
                $stmt_resume->bind_param("i", $user_id);
                $stmt_resume->execute();
                $result = $stmt_resume->get_result();

                if ($result->num_rows > 0) {
                    // تحديث السيرة الذاتية في جدول resumes
                    $stmt_update_resume = $conn->prepare("UPDATE resumes SET cv_link = ? WHERE user_id = ?");
                    $stmt_update_resume->bind_param("si", $cv_link, $user_id);
                    $stmt_update_resume->execute();
                } else {
                    // إضافة السيرة الذاتية في جدول resumes
                    $stmt_add_resume = $conn->prepare("INSERT INTO resumes (user_id, cv_link) VALUES (?, ?)");
                    $stmt_add_resume->bind_param("is", $user_id, $cv_link);
                    $stmt_add_resume->execute();
                }

                $success_message = $current_cv ? "تم تحديث السيرة الذاتية بنجاح." : "تم إضافة السيرة الذاتية بنجاح.";
                $current_cv = $cv_link;
            } else {
                $error_message = "حدث خطأ أثناء حفظ السيرة الذاتية في جدول المستخدمين.";
            }
        } else {
            $error_message = "فشل في رفع الملف.";
        }
    }
}
?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>إدارة السيرة الذاتية</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>CSS/navbar.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <style>
  body {
    font-family: 'Tajawal', sans-serif;
    background: linear-gradient(135deg, #f4f7fa, #e0e7ff);
    min-height: 100vh;
    margin: 0;
    padding: 0;
  }

  .container {
    padding-top: 2rem;
    padding-bottom: 2rem;
  }

  h1 {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1e3a8a;
    text-align: center;
    margin-bottom: 2rem;
    position: relative;
    text-transform: uppercase;
  }

  h1::after {
    content: '';
    position: absolute;
    width: 50px;
    height: 4px;
    background: linear-gradient(90deg, #1e3a8a, #3b82f6);
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
  }

  .card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    background: white;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
  }

  .card-title {
    font-size: 1.5rem;
    color: #1e3a8a;
    font-weight: 600;
    margin-bottom: 1.5rem;
  }

  .alert {
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1.5rem;
  }

  .alert-success {
    background-color: #d1fae5;
    color: #065f46;
    border: 1px solid #10b981;
  }

  .alert-danger {
    background-color: #fee2e2;
    color: #991b1b;
    border: 1px solid #ef4444;
  }

  .form-label {
    font-weight: 500;
    color: #1e3a8a;
  }

  .form-control {
    border-radius: 10px;
    border: 1px solid #d1d5db;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
  }

  .form-control:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
    outline: none;
  }

  .btn {
    border-radius: 10px;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    transition: all 0.3s ease;
  }

  .btn-primary {
    background: linear-gradient(90deg, #1e3a8a, #3b82f6);
    color: white;
    border: none;
  }

  .btn-primary:hover {
    background: linear-gradient(90deg, #163072, #2f69c3);
    transform: translateY(-2px);
  }

  .btn-success {
    background: linear-gradient(90deg, #10b981, #34d399);
    color: white;
    border: none;
  }

  .btn-success:hover {
    background: linear-gradient(90deg, #047857, #22c55e);
    transform: translateY(-2px);
  }

  .text-muted {
    color: #64748b !important;
  }

  @media (max-width: 768px) {
    h1 {
      font-size: 2rem;
    }

    .card {
      margin: 0 1rem;
    }

    .form-control {
      font-size: 0.9rem;
      padding: 0.5rem 1rem;
    }

    .btn {
      padding: 0.5rem 1rem;
      font-size: 0.9rem;
    }
  }
  </style>
</head>

<body>
  <div class="container">
    <h1>إدارة السيرة الذاتية <i class="fas fa-file-pdf"></i></h1>

    <?php if ($success_message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success_message); ?></div>
    <?php endif; ?>

    <?php if ($error_message): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <div class="card mb-4">
      <div class="card-body">
        <h5 class="card-title">السيرة الذاتية الحالية</h5>
        <?php if ($current_cv): ?>
        <p>
          <a href="../<?= htmlspecialchars($current_cv); ?>" target="_blank" class="btn btn-success">
            <i class="fas fa-eye"></i> عرض السيرة الذاتية
          </a>
        </p>
        <?php else: ?>
        <p class="text-muted">لا توجد سيرة ذاتية حالياً.</p>
        <?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <h5 class="card-title"><?= $current_cv ? "تحديث السيرة الذاتية" : "إضافة سيرة ذاتية"; ?></h5>
        <form method="POST" enctype="multipart/form-data">
          <div class="mb-3">
            <label for="cv" class="form-label">اختر ملف السيرة الذاتية (PDF فقط):</label>
            <input type="file" name="cv" id="cv" class="form-control" accept="application/pdf" required>
          </div>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-upload"></i> <?= $current_cv ? "تحديث" : "إضافة"; ?>
          </button>
        </form>
      </div>
    </div>
  </div>
</body>

</html>
<?php $conn->close(); ?>