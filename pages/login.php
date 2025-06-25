<?php
session_start();
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST['email'];
  $password = $_POST['password'];
  $user = null;
  $role = null;

  // البحث أولاً في جدول users
  $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $role = $user['role']; // admin أو employee
  } else {
    // لو مش موجود في users، نبحث في companies
    $stmt = $conn->prepare("SELECT company_id, name, email, password FROM companies WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
      $user = $result->fetch_assoc();
      $role = 'company';
    }
  }



  // التحقق من الباسورد
  if ($user) {
    $_SESSION['role'] = $role;
    $_SESSION['name'] = $user['name'];
    $_SESSION['email'] = $user['email'];

    if ($role === 'company') {
      $_SESSION['user_id'] = $user['company_id'];
      header('Location: company/company_dashboard.php');
    } else {
      $_SESSION['user_id'] = $user['user_id'];
      if ($role === 'admin') {
        header('Location: admin/admin_dashboard.php');
      } else {
        header('Location: index.php');
      }
    }
    exit();
  } else {
    $error_message = "البريد الإلكتروني أو كلمة المرور غير صحيحة.";
  }

}
?>

<?php include "./navBar.php" ?>
<div class="background-image">
  <div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="login-form">
      <h2 class="text-center mb-4">تسجيل الدخول</h2>
      <?php if (isset($error_message)): ?>
      <div class="alert alert-danger"> <?= $error_message; ?> </div>
      <?php endif; ?>
      <form method="POST">
        <div class="mb-3">
          <input type="email" class="form-control" name="email" placeholder="البريد الالكتروني" required>
        </div>
        <div class="mb-3">
          <input type="password" class="form-control" name="password" placeholder="كلمة المرور" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">دخول</button>
        <div class="text-center mt-3">
          <a href="#" class="forgot-password">هل نسيت كلمة المرور؟</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include "./footer.php" ?>

// insert -> هضيف بيانات جديده #
// select -> هيجلب بيانات من قاعدة البيانات #