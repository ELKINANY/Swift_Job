<?php
session_start();
require 'db_connection.php'; // الاتصال بقاعدة البيانات

// معالجة تسجيل الدخول
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $acctype = $_POST['acctype'];

    // التحقق من صحة البيانات
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
    $stmt->bind_param("ss", $email, $acctype);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];

        // توجيه المستخدم بناءً على نوعه
        if ($user['role'] === 'employee') {
            header('Location: index.php');
        } elseif ($user['role'] === 'company') {
            header('Location: company/company_dashboard.php');
        } else {
            header('Location: admin/admin_dashboard.php');
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
              <div class="select-container">
                  <select id="select-category" name="acctype" class="form-select" required>
                      <option value="employee">Employer</option>
                      <option value="company">company</option>
                      <option value="admin">Admin</option>
                  </select>
              </div>
          </div>
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