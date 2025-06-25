<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'craftsman') {
    header('Location: login.php');
    exit();
}

require '../db_connection.php'; // الاتصال بقاعدة البيانات

$user_id = $_SESSION['user_id'];

// جلب المشاريع المقدمة عليها
$stmt = $conn->prepare("SELECT projects.title, projects.budget, projects.project_type FROM project_bids JOIN projects ON project_bids.project_id = projects.project_id WHERE project_bids.craftsman_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bidded_projects = $stmt->get_result();

// جلب المشاريع المحفوظة
$stmt = $conn->prepare("SELECT projects.title, projects.budget, projects.project_type FROM saved_projects JOIN projects ON saved_projects.project_id = projects.project_id WHERE saved_projects.craftsman_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$saved_projects = $stmt->get_result();

// جلب بيانات الحرفي
$stmt = $conn->prepare("SELECT name, email, phone, specialization, location FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$craftsman = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="ar">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>لوحة تحكم الحرفي</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <style>
  .dashboard-container {
    display: flex;
    min-height: 100vh;
  }

  .sidebar {
    width: 250px;
    background: #343a40;
    color: white;
    padding: 20px;
  }

  .sidebar a {
    color: white;
    text-decoration: none;
    display: block;
    padding: 10px;
    border-radius: 5px;
  }

  .sidebar a:hover {
    background: #495057;
  }

  .content {
    flex-grow: 1;
    padding: 20px;
  }

  .stat-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
  }

  .stat-card i {
    font-size: 30px;
    margin-bottom: 10px;
  }

  .craftsman-badge {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 15px;
    margin-bottom: 20px;
  }
  </style>
</head>

<body>

  <div class="dashboard-container">
    <!-- الشريط الجانبي -->
    <div class="sidebar">
      <h4 class="text-center">لوحة تحكم الحرفي</h4>
      <hr>
      <a href="craftsman_dashboard.php"><i class="fas fa-th-large"></i> الرئيسية</a>
      <a href="#projects-section"><i class="fas fa-hammer"></i> المشاريع</a>
      <a href="#portfolio-section"><i class="fas fa-images"></i> معرض الأعمال</a>
      <a href="#edit-profile-section"><i class="fas fa-user-edit"></i> تعديل الملف الشخصي</a>
      <a href="logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a>
    </div>

    <!-- المحتوى الرئيسي -->
    <div class="content">
      <div class="craftsman-badge">
        <h2>مرحبًا، <?= $craftsman['name']; ?></h2>
        <p class="text-muted"><?= $craftsman['specialization']; ?></p>
        <p><i class="fas fa-map-marker-alt"></i> <?= $craftsman['location']; ?></p>
      </div>

      <!-- المشاريع المقدمة عليها -->
      <h3 id="projects-section">العروض المقدمة</h3>
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>اسم المشروع</th>
            <th>نوع المشروع</th>
            <th>الميزانية</th>
            <th>حالة العرض</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($project = $bidded_projects->fetch_assoc()): ?>
          <tr>
            <td><?= $project['title']; ?></td>
            <td><?= $project['project_type']; ?></td>
            <td><?= $project['budget']; ?></td>
            <td><span class="badge bg-warning text-dark">قيد المراجعة</span></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>

      <!-- المشاريع المحفوظة -->
      <h3>المشاريع المحفوظة</h3>
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>اسم المشروع</th>
            <th>نوع المشروع</th>
            <th>الميزانية</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($project = $saved_projects->fetch_assoc()): ?>
          <tr>
            <td><?= $project['title']; ?></td>
            <td><?= $project['project_type']; ?></td>
            <td><?= $project['budget']; ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>

      <!-- معرض الأعمال -->
      <h3 id="portfolio-section">معرض الأعمال</h3>
      <form method="POST" action="upload_portfolio.php" enctype="multipart/form-data">
        <div class="mb-3">
          <label class="form-label">إضافة صورة لمعرض الأعمال:</label>
          <input type="file" name="portfolio_image" class="form-control" accept="image/*">
        </div>
        <div class="mb-3">
          <label class="form-label">وصف الصورة:</label>
          <textarea name="description" class="form-control" rows="2"></textarea>
        </div>
        <button type="submit" class="btn btn-success">إضافة</button>
      </form>

      <!-- تعديل الملف الشخصي -->
      <h3 id="edit-profile-section">تعديل الملف الشخصي</h3>
      <form method="POST" action="update_craftsman_profile.php">
        <div class="mb-3">
          <label class="form-label">الاسم:</label>
          <input type="text" name="name" class="form-control" value="<?= $craftsman['name']; ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">البريد الإلكتروني:</label>
          <input type="email" name="email" class="form-control" value="<?= $craftsman['email']; ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">رقم الجوال:</label>
          <input type="tel" name="phone" class="form-control" value="<?= $craftsman['phone']; ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">التخصص:</label>
          <input type="text" name="specialization" class="form-control" value="<?= $craftsman['specialization']; ?>"
            required>
        </div>

        <div class="mb-3">
          <label class="form-label">الموقع:</label>
          <input type="text" name="location" class="form-control" value="<?= $craftsman['location']; ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">وصف الملف الشخصي:</label>
          <textarea name="profile_description" class="form-control"
            rows="3"><?= $craftsman['profile_description']; ?></textarea>
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