<?php
include "../navBar.php";
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

require '../db_connection.php';

$user_id = $_SESSION['user_id'];

// جلب بيانات المستخدم
$stmt = $conn->prepare("SELECT name, email, phone, location, specialization, profile_pic, cv_link FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$current_cv = $user['cv_link'] ?? null;
$success_message = "";
$error_message = "";

// تحديث البيانات عند الإرسال
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $location = trim($_POST['location']);
    $specialization = trim($_POST['specialization']);

    // تحديث الصورة الشخصية
    if (!empty($_FILES['profile_pic']['name'])) {
        $target_dir = "../uploads/profiles/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $profile_pic = "profile_" . $user_id . ".jpg";
        $target_file = $target_dir . $profile_pic;

        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
            // تحديث الصورة الشخصية في قاعدة البيانات
            $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE user_id = ?");
            $stmt->bind_param("si", $profile_pic, $user_id);
            $stmt->execute();
            $user['profile_pic'] = $profile_pic; // تحديث الصورة في الجلسة الحالية
        } else {
            $error_message = "فشل في رفع الصورة الشخصية.";
        }
    }

    // تحديث البيانات في قاعدة البيانات
    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, location = ?, specialization = ? WHERE user_id = ?");
    $stmt->bind_param("sssssi", $name, $email, $phone, $location, $specialization, $user_id);

    if ($stmt->execute()) {
        $success_message = "تم تحديث البيانات بنجاح.";
        // تحديث البيانات في الجلسة الحالية لعرضها
        $user['name'] = $name;
        $user['email'] = $email;
        $user['phone'] = $phone;
        $user['location'] = $location;
        $user['specialization'] = $specialization;
    } else {
        $error_message = "حدث خطأ أثناء تحديث البيانات.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>تعديل الحساب</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>CSS/navbar.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <style>
  body {
    font-family: 'Tajawal', sans-serif;
    background: linear-gradient(135deg, #f4f7fa, #e0e7ff);
    min-height: 100vh;
    margin: 0;
    padding: 0;
  }

  .container {
    padding-top: 3rem;
    padding-bottom: 3rem;
  }

  h2 {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1e3a8a;
    text-align: center;
    margin-bottom: 2rem;
    position: relative;
  }

  h2::after {
    content: '';
    position: absolute;
    width: 50px;
    height: 4px;
    background: linear-gradient(90deg, #1e3a8a, #3b82f6);
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
  }

  .profile-container {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .profile-container:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
  }

  .profile-pic {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #3b82f6;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .profile-pic:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
  }

  .alert {
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1.5rem;
  }

  .alert-success {
    background-color: #d1fae5;
    color: #065f46;
    border: 1px solid #10b981;
  }

  .alert-danger {
    background-color: #fee2e2;
    color: #991b1b;
    border: 1px solid #ef4444;
  }

  .form-label {
    font-weight: 500;
    color: #1e3a8a;
    margin-bottom: 0.5rem;
  }

  .form-control {
    border-radius: 10px;
    border: 1px solid #d1d5db;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
  }

  .form-control:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
    outline: none;
  }

  .btn-primary {
    background: linear-gradient(90deg, #1e3a8a, #3b82f6);
    border: none;
    padding: 0.75rem;
    font-weight: 500;
    border-radius: 10px;
    transition: all 0.3s ease;
  }

  .btn-primary:hover {
    background: linear-gradient(90deg, #163072, #2f69c3);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(59, 130, 246, 0.5);
  }

  @media (max-width: 768px) {
    h2 {
      font-size: 2rem;
    }

    .profile-container {
      padding: 1.5rem;
    }

    .profile-pic {
      width: 120px;
      height: 120px;
    }

    .form-control {
      font-size: 0.9rem;
      padding: 0.5rem 1rem;
    }

    .btn-primary {
      padding: 0.5rem;
      font-size: 0.9rem;
    }
  }
  </style>
</head>

<body>
  <div class="container">
    <h2>تعديل الحساب <i class="fas fa-user-edit"></i></h2>

    <div class="row justify-content-center">
      <div class="col-md-6 profile-container">
        <?php if ($success_message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <div class="text-center mb-4">
          <img src="../uploads/profiles/<?= htmlspecialchars($user['profile_pic'] ?: 'default.jpg'); ?>"
            alt="Profile Picture" class="profile-pic">
        </div>

        <form method="POST" enctype="multipart/form-data">
          <div class="mb-3">
            <label class="form-label">الصورة الشخصية:</label>
            <input type="file" name="profile_pic" class="form-control" accept="image/*">
          </div>

          <div class="mb-3">
            <label class="form-label">الاسم:</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']); ?>"
              required>
          </div>

          <div class="mb-3">
            <label class="form-label">البريد الإلكتروني:</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']); ?>"
              required>
          </div>

          <div class="mb-3">
            <label class="form-label">رقم الهاتف:</label>
            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? ''); ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">التخصص:</label>
            <input type="text" name="specialization" class="form-control"
              value="<?= htmlspecialchars($user['specialization'] ?? ''); ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">الموقع:</label>
            <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($user['location']); ?>">
          </div>

          <button type="submit" class="btn btn-primary w-100">
            <i class="fas fa-save me-2"></i> تحديث البيانات
          </button>
        </form>
      </div>
    </div>
  </div>
</body>

</html>
<?php $conn->close(); ?>