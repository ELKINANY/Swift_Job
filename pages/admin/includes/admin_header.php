<?php
require_once '../db_connection.php';


?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>لوحة التحكم - Swift Jobs</title>
  <!-- Bootstrap RTL -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <!-- Custom CSS -->
  <link href="../../assets/css/admin.css" rel="stylesheet">
</head>

<body>
  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
        <div class="position-sticky pt-3">
          <ul class="nav flex-column">
            <li class="nav-item">
              <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : ''; ?>"
                href="admin_dashboard.php">
                <i class="fas fa-tachometer-alt"></i>
                لوحة التحكم
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'all_jobs.php' ? 'active' : ''; ?>"
                href="all_jobs.php">
                <i class="fas fa-briefcase"></i>
                جميع الوظائف
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'all_projects.php' ? 'active' : ''; ?>"
                href="all_projects.php">
                <i class="fas fa-hammer"></i>
                جميع المهن
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'all_companies.php' ? 'active' : ''; ?>"
                href="all_companies.php">
                <i class="fas fa-building"></i>
                الشركات
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'all_job_seekers.php' ? 'active' : ''; ?>"
                href="all_job_seekers.php">
                <i class="fas fa-users"></i>
                طالبي العمل
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'all_admins.php' ? 'active' : ''; ?>"
                href="all_admins.php">
                <i class="fas fa-user-shield"></i>
                المشرفين
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="edit_profile.php">
                <i class="fas fa-user-edit"></i>
                تعديل الملف الشخصي
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="../logout.php">
                <i class="fas fa-sign-out-alt"></i>
                تسجيل الخروج
              </a>
            </li>
          </ul>
        </div>
      </nav>

      <!-- Main content -->
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div
          class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
          <h1 class="h2">لوحة التحكم</h1>
          <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
              <button type="button" class="btn btn-sm btn-outline-secondary">تصدير</button>
              <button type="button" class="btn btn-sm btn-outline-secondary">استيراد</button>
            </div>
          </div>
        </div>