<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header('Location: login.php');
    exit();
}
?>

<div class="sidebar">
  <h4 class="text-center">لوحة التحكم</h4>
  <hr>
  <a href="company_dashboard.php"><i class="fas fa-th-large"></i> الرئيسية</a>
  <a href="add_job.php"><i class="fas fa-plus-circle"></i> إضافة وظيفة</a>
  <a href="add_project.php"><i class="fas fa-hammer"></i> إضافة مهنة</a>
  <a href="view_jobs.php"><i class="fas fa-briefcase"></i> إدارة الوظائف</a>
  <a href="view_projects.php" class="active"><i class="fas fa-project-diagram"></i> إدارة المشاريع</a>
  <a href="edit_profile.php"><i class="fas fa-user-edit"></i> تعديل الحساب</a>
  <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a>
</div>