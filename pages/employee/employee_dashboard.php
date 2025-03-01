<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
    header('Location: login.php');
    exit();
}

require '../db_connection.php'; // الاتصال بقاعدة البيانات

$user_id = $_SESSION['user_id'];

// جلب الوظائف المتقدم لها
$stmt = $conn->prepare("SELECT jobs.title, jobs.salary, jobs.job_type FROM applications JOIN jobs ON applications.job_id = jobs.job_id WHERE applications.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$applied_jobs = $stmt->get_result();

// جلب الوظائف المحفوظة
$stmt = $conn->prepare("SELECT jobs.title, jobs.salary, jobs.job_type FROM saved_jobs JOIN jobs ON saved_jobs.job_id = jobs.job_id WHERE saved_jobs.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$saved_jobs = $stmt->get_result();

// جلب بيانات المستخدم
$stmt = $conn->prepare("SELECT name, email, specialization, cv_link FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم الباحث عن عمل</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        .dashboard-container { display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background: #343a40; color: white; padding: 20px; }
        .sidebar a { color: white; text-decoration: none; display: block; padding: 10px; border-radius: 5px; }
        .sidebar a:hover { background: #495057; }
        .content { flex-grow: 1; padding: 20px; }
        .stat-card { background: white; padding: 20px; border-radius: 10px; text-align: center; box-shadow: 0px 4px 6px rgba(0,0,0,0.1); }
        .stat-card i { font-size: 30px; margin-bottom: 10px; }
    </style>
</head>
<body>

<div class="dashboard-container">
    <!-- الشريط الجانبي -->
    <div class="sidebar">
        <h4 class="text-center">لوحة التحكم</h4>
        <hr>
        <a href="employee_dashboard.php"><i class="fas fa-th-large"></i> الرئيسية</a>
        <a href="#resume-section"><i class="fas fa-file-pdf"></i> إدارة السيرة الذاتية</a>
        <a href="#edit-profile-section"><i class="fas fa-user-edit"></i> تعديل الحساب</a>
        <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a>
    </div>

    <!-- المحتوى الرئيسي -->
    <div class="content">
        <h2 class="mb-4">مرحبًا، <?= $user['name']; ?></h2>

        <!-- الوظائف المتقدم لها -->
        <h3>الوظائف المتقدم لها</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>المسمى الوظيفي</th>
                    <th>نوع الوظيفة</th>
                    <th>الراتب</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($job = $applied_jobs->fetch_assoc()): ?>
                    <tr>
                        <td><?= $job['title']; ?></td>
                        <td><?= $job['job_type']; ?></td>
                        <td><?= $job['salary']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- الوظائف المحفوظة -->
        <h3>الوظائف المحفوظة</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>المسمى الوظيفي</th>
                    <th>نوع الوظيفة</th>
                    <th>الراتب</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($job = $saved_jobs->fetch_assoc()): ?>
                    <tr>
                        <td><?= $job['title']; ?></td>
                        <td><?= $job['job_type']; ?></td>
                        <td><?= $job['salary']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- إدارة السيرة الذاتية -->
        <h3 id="resume-section">إدارة السيرة الذاتية</h3>
        <form method="POST" action="upload_resume.php" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">رفع السيرة الذاتية:</label>
                <input type="file" name="cv" class="form-control">
            </div>
            <button type="submit" class="btn btn-success">رفع</button>
        </form>
        <?php if (!empty($user['cv_link'])): ?>
            <p>السيرة الذاتية الحالية: <a href="<?= $user['cv_link']; ?>" target="_blank">عرض</a></p>
        <?php endif; ?>

        <!-- تعديل الحساب -->
        <h3 id="edit-profile-section">تعديل الحساب</h3>
        <form method="POST" action="update_profile.php">
            <div class="mb-3">
                <label class="form-label">الاسم:</label>
                <input type="text" name="name" class="form-control" value="<?= $user['name']; ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">البريد الإلكتروني:</label>
                <input type="email" name="email" class="form-control" value="<?= $user['email']; ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">التخصص:</label>
                <input type="text" name="specialization" class="form-control" value="<?= $user['specialization']; ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">كلمة المرور الجديدة (اختياري):</label>
                <input type="password" name="password" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">تحديث البيانات</button>
        </form>
    </div>
</div>

</body>
</html>
