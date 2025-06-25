<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header('Location: login.php');
    exit();
}

require '../db_connection.php';

// التحقق من وجود user_id في الرابط
if (!isset($_GET['user_id'])) {
    die("لم يتم تحديد المتقدم.");
}

$user_id = intval($_GET['user_id']);

// جلب بيانات المتقدم
$stmt = $conn->prepare("SELECT name, email, phone FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$applicant = $stmt->get_result()->fetch_assoc();

if (!$applicant) {
    die("المتقدم غير موجود.");
}

$whatsapp_url = '';
$success_message = "";
$error_message = "";

// معالجة الإرسال عند النقر على زر الإرسال
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    // تأكد من أن الحقول ليست فارغة
    if (empty($subject) || empty($message)) {
        $error_message = "يجب ملء جميع الحقول.";
    } else {
        // إنشاء رابط WhatsApp مع الموضوع والنص المدخل
        $whatsapp_url = "https://api.whatsapp.com/send?phone=20" . htmlspecialchars($applicant['phone']);
        $whatsapp_url .= "&text=" . urlencode("موضوع: " . $subject);
        $whatsapp_url .= "%0A" . "نص الرسالة: " . $message;
        $whatsapp_url .= "%0A" . "رقم الهاتف: " . htmlspecialchars($applicant['phone']);
        
        $success_message = "تم إنشاء رابط الواتساب بنجاح! يمكنك الآن إرسال الرسالة.";
        $_POST = []; // لفض الـ POST عشان يظهر الفورم فارغ
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="ltr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>التواصل مع المتقدم</title>
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

  .form-control:disabled {
    background-color: #f1f5f9;
    color: #6b7280;
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

  .btn-success {
    background-color: var(--success);
    color: white;
    border: none;
  }

  .btn-success:hover {
    background-color: #059669;
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
        <h1>التواصل مع المتقدم</h1>
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
            <label class="form-label">اسم المتقدم:</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($applicant['name']); ?>" disabled>
          </div>

          <div class="mb-3">
            <label class="form-label">البريد الإلكتروني:</label>
            <input type="email" class="form-control" value="<?= htmlspecialchars($applicant['email']); ?>" disabled>
          </div>

          <div class="mb-3">
            <label class="form-label">رقم الهاتف:</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($applicant['phone']); ?>" disabled>
          </div>

          <div class="mb-3">
            <label class="form-label">موضوع الرسالة:</label>
            <input type="text" name="subject" class="form-control"
              value="<?= htmlspecialchars($_POST['subject'] ?? ''); ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label">نص الرسالة:</label>
            <textarea name="message" class="form-control" rows="5"
              required><?= htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
          </div>

          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-paper-plane"></i> إرسال
            </button>
          </div>
        </form>

        <?php if ($whatsapp_url): ?>
        <div class="mt-4">
          <a href="<?= $whatsapp_url ?>" class="btn btn-success" target="_blank">
            <i class="fab fa-whatsapp"></i> إرسال عبر واتساب
          </a>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    console.log('WhatsApp Message Page Loaded');
  });
  </script>
</body>

</html>