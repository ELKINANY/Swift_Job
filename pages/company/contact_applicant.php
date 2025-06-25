<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header('Location: login.php');
    exit();
}

if (!isset($_SESSION['email'])) {
    header('Location: ../login.php');
    exit();
}

require '../db_connection.php';

if (!isset($_GET['user_id'])) {
    die("لم يتم تحديد المتقدم.");
}

$user_id = intval($_GET['user_id']);

$stmt = $conn->prepare("SELECT name, email FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$applicant = $stmt->get_result()->fetch_assoc();

if (!$applicant) {
    die("المتقدم غير موجود.");
}

$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
  $subject = trim($_POST['subject']);
  $message = trim($_POST['message']);
  $to_email = $applicant['email'];
  $headers = "From: " . $_SESSION['email'] . "\r\n" .
             "Reply-To: " . $_SESSION['email'] . "\r\n" .
             "Content-Type: text/html; charset=UTF-8";

  if (empty($subject) || empty($message)) {
      $error_message = "يجب ملء جميع الحقول.";
  } else {
      // إرسال الإيميل
      if (mail($to_email, $subject, $message, $headers)) {
          // حفظ الرسالة في قاعدة البيانات
          $company_id = $_SESSION['company_id'];

          // Debug: Check the values before insertion
          if (empty($company_id) || empty($user_id) || empty($message)) {
              $error_message = "خطأ: بيانات غير صالحة. company_id, applicant_id, message";
          } else {
              $stmt = $conn->prepare("INSERT INTO company_applicant_contacts (company_id, applicant_id, message) VALUES (?, ?, ?)");
              if (!$stmt) {
                  $error_message = "خطأ في تجهيز الاستعلام: " . $conn->error;
              } else {
                  $stmt->bind_param("iis", $company_id, $user_id, $message);
                  if ($stmt->execute()) {
                      $success_message = "تم إرسال الرسالة وحفظها بنجاح!";
                  } else {
                      $error_message = "فشل في حفظ الرسالة في قاعدة البيانات: " . $stmt->error;
                  }
                  $stmt->close();
              }
          }
      } else {
          $error_message = "فشل في إرسال الإيميل.";
      }
  }
}
?>

<!DOCTYPE html>
<html lang="ar">

<head>
  <meta charset="UTF-8">
  <title>إرسال رسالة</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
  body {
    background: linear-gradient(to right, #1e3c72, #2a5298);
    color: #fff;
    font-family: 'Tajawal', sans-serif;
  }

  .dashboard-container {
    display: flex;
    min-height: 100vh;
  }

  .sidebar {
    width: 250px;
    background-color: #111;
    padding: 20px;
  }

  .sidebar a {
    color: #ccc;
    display: block;
    margin-bottom: 15px;
    padding: 10px;
    border-radius: 8px;
    text-decoration: none;
    transition: 0.3s;
  }

  .sidebar a:hover {
    background-color: #444;
    color: #fff;
  }

  .content {
    flex-grow: 1;
    padding: 30px;
  }

  .form-control,
  .form-select {
    background-color: #f9f9f9;
    color: #000;
  }

  .btn-primary {
    background-color: #ff6f00;
    border: none;
  }

  .btn-primary:hover {
    background-color: #e65100;
  }

  .card {
    background-color: #ffffff;
    color: #000;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
  }
  </style>
</head>

<body>
  <div class="dashboard-container">
    <div class="sidebar">
      <h4 class="text-center text-white mb-4">لوحة التحكم</h4>
      <a href="company_dashboard.php"><i class="fas fa-th-large"></i> الرئيسية</a>
      <a href="add_job.php"><i class="fas fa-plus-circle"></i> إضافة وظيفة</a>
      <a href="add_project.php"><i class="fas fa-hammer"></i> إضافة مهنة</a>
      <a href="view_jobs.php"><i class="fas fa-briefcase"></i> إدارة الوظائف</a>
      <a href="view_projects.php" class="active"><i class="fas fa-project-diagram"></i> إدارة المشاريع</a>
      <a href="edit_profile.php"><i class="fas fa-user-edit"></i> تعديل الحساب</a>
      <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a>
    </div>
    <div class="content">
      <div class="container">
        <div class="card">
          <h2 class="text-center mb-4">إرسال رسالة إلى المتقدم</h2>
          <?php if (!empty($success_message)): ?>
          <div class="alert alert-success"><?= $success_message; ?></div>
          <?php endif; ?>
          <?php if (!empty($error_message)): ?>
          <div class="alert alert-danger"><?= $error_message; ?></div>
          <?php endif; ?>
          <form method="POST">
            <div class="mb-3">
              <label class="form-label">اسم المتقدم:</label>
              <input type="text" class="form-control" value="<?= htmlspecialchars($applicant['name']); ?>" disabled>
            </div>
            <div class="mb-3">
              <label class="form-label">البريد الإلكتروني:</label>
              <input type="email" class="form-control" value="<?= htmlspecialchars($applicant['email']); ?>" disabled>
            </div>
            <div class="mb-3">
              <label class="form-label">موضوع الرسالة:</label>
              <input type="text" name="subject" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">نص الرسالة:</label>
              <textarea name="message" class="form-control" rows="6" required></textarea>
            </div>
            <div class="text-center">
              <button type="submit" name="send_message" class="btn btn-primary px-5">
                <i class="fas fa-paper-plane me-2"></i> إرسال
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

</body>

</html>