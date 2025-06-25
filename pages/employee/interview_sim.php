<?php
ob_start();
session_start();
require '../db_connection.php'; // الاتصال بقاعدة البيانات
include "../navBar.php";

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
  die("يرجى تسجيل الدخول.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_interview'])) {
  $_SESSION['interview_type'] = $_POST['interview_type'];
  $_SESSION['question_index'] = 0;
  $_SESSION['answers'] = [];
  header("Location: questions.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['retry'])) {
  unset($_SESSION['interview_type']);
  unset($_SESSION['question_index']);
  unset($_SESSION['answers']);
  header("Location: " . $_SERVER['PHP_SELF']);
  exit();
}
?>

<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>محاكاة المقابلات</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <style>
  body {
    font-family: 'Tajawal', sans-serif;
    background-color: #f4f7fa;
    color: #333;
    padding-top: 2rem;
  }

  .container {
    max-width: 800px;
    background: #fff;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }

  h1 {
    font-size: 2rem;
    color: #1e3a8a;
    text-align: center;
    margin-bottom: 1.5rem;
  }

  .btn-custom {
    background: #3b82f6;
    color: #fff;
    border: none;
    padding: 0.5rem 1.5rem;
    border-radius: 5px;
    transition: all 0.3s ease;
  }

  .btn-custom:hover {
    background: #2563eb;
    transform: translateY(-2px);
  }

  @media (max-width: 768px) {
    .container {
      padding: 1rem;
    }

    h1 {
      font-size: 1.5rem;
    }
  }
  </style>
</head>

<body>
  <div class="container">
    <h1>محاكاة المقابلة <i class="fas fa-user-tie"></i></h1>

    <?php if (!isset($_SESSION['interview_type'])): ?>
    <form method="post">
      <div class="mb-3">
        <label class="form-label">اختر نوع المقابلة:</label>
        <select class="form-select" name="interview_type" required>
          <option value="عام">عام</option>
          <option value="سلوكي">سلوكي</option>
          <option value="تقني">تقني</option>
        </select>
      </div>
      <button type="submit" name="start_interview" class="btn btn-custom">بدء المقابلة</button>
    </form>
    <?php else: ?>
    <div class="alert alert-success text-center">تم الانتهاء! التقييم متاح في <a href="questions.php">هنا</a>.</div>
    <form method="post">
      <button type="submit" name="retry" class="btn btn-custom mt-3">أعد المحاولة</button>
    </form>
    <?php endif; ?>
  </div>
</body>

</html>

<?php $conn->close(); ?>