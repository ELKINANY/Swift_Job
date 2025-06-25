<?php
require 'db_connection.php'; // الاتصال بقاعدة البيانات

// معالجة التسجيل
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $specialization = $_POST['specialization'];
    $location = $_POST['location'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role']; // الحصول على نوع المستخدم من النموذج

    // التحقق من تطابق كلمات المرور
    if ($password !== $confirm_password) {
        $error_message = "كلمتا المرور غير متطابقتين.";
    } else {
        // تشفير كلمة المرور
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // إدخال بيانات المستخدم في جدول users
        $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, role, specialization, location) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $name, $email, $phone, $hashed_password, $role, $specialization, $location);

        if ($stmt->execute()) {
            // الحصول على ID المستخدم الجديد
            $user_id = $stmt->insert_id;
            $success_message = "تم تسجيل الحساب بنجاح!";
        } else {
            $error_message = "حدث خطأ أثناء التسجيل. يرجى المحاولة مرة أخرى.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>تسجيل حساب جديد</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>

<body>
  <?php include "./navBar.php" ?> 

  <div class="reg-wrapper">
    <div class="reg-form">
      <h1 class="reg-heading mt-3">سجل كموظف أو حرفي</h1>

      <?php if (isset($success_message)): ?>
      <div class="alert alert-success"><?= $success_message; ?></div>
      <?php endif; ?>

      <?php if (isset($error_message)): ?>
      <div class="alert alert-danger"><?= $error_message; ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="input-box">
          <input type="text" name="name" class="reg-input" placeholder="الاسم الكامل" required>
        </div>
        <div class="input-box">
          <input type="text" name="phone" class="reg-input" placeholder="رقم الجوال" required>
        </div>
        <div class="input-box">
          <input type="email" name="email" class="reg-input" placeholder="البريد الالكتروني" required>
        </div>
        <div class="input-box">
          <select name="role" class="reg-input" required>
            <option value="">اختر نوع الحساب</option>
            <option value="employee">موظف</option>
            <option value="craftsman">حرفي</option>
          </select>
        </div>
        <div class="input-box">
          <input type="text" name="specialization" class="reg-input" placeholder="التخصص" required>
        </div>
        <div class="input-box">
          <input type="text" name="location" class="reg-input" placeholder="العنوان" required>
        </div>
        <div class="input-box">
          <input type="password" name="password" class="reg-input" placeholder="كلمة المرور" required>
        </div>
        <div class="input-box">
          <input type="password" name="confirm_password" class="reg-input" placeholder="تأكيد كلمة المرور" required>
        </div>
        <button type="submit" class="reg-submit">التالي</button>
        <div class="existing-account">
          <a href="login.php">لديك حساب بالفعل؟</a>
        </div>
      </form>
    </div>
    <div class="reg-image">
      <img src="<?= BASE_URL ?>page-form-thumb.webp" alt="Registration illustration">
    </div>
  </div>

  <?php include "./footer.php" ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

