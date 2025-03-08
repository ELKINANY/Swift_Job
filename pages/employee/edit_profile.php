<?php
include "../navBar.php";
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
        } else {
            $error_message = "فشل في رفع الصورة الشخصية.";
        }
    }

    // التحقق من رفع السيرة الذاتية
    if (!empty($_FILES['cv_link']['name'])) {
        $cv = $_FILES['cv_link'];

        // التحقق من نوع الملف (PDF فقط)
        $allowed_types = ['application/pdf'];
        if (!in_array($cv['type'], $allowed_types)) {
            $error_message = "يجب أن يكون الملف بصيغة PDF فقط.";
        } elseif ($cv['size'] > 2 * 1024 * 1024) { // 2MB كحد أقصى
            $error_message = "حجم الملف يجب ألا يتجاوز 2 ميجابايت.";
        } else {
            // حفظ الملف في مجلد "uploads"
            $uploads_dir = '../uploads/cvs/';
            if (!is_dir($uploads_dir)) {
                mkdir($uploads_dir, 0777, true);
            }

            $cv_name = "cv_" . $user_id . ".pdf";
            $cv_path = $uploads_dir . $cv_name;

            if (move_uploaded_file($cv['tmp_name'], $cv_path)) {
                // تحديث رابط السيرة الذاتية في قاعدة البيانات
                $cv_link = "uploads/cvs/" . $cv_name;
                $stmt = $conn->prepare("UPDATE users SET cv_link = ? WHERE user_id = ?");
                $stmt->bind_param("si", $cv_link, $user_id);

                if ($stmt->execute()) {
                    $success_message = $current_cv ? "تم تحديث السيرة الذاتية بنجاح." : "تم إضافة السيرة الذاتية بنجاح.";
                    $current_cv = $cv_link;
                } else {
                    $error_message = "حدث خطأ أثناء حفظ السيرة الذاتية.";
                }
            } else {
                $error_message = "فشل في رفع الملف.";
            }
        }
    }

    // تحديث البيانات في قاعدة البيانات
    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, location = ?, specialization = ? WHERE user_id = ?");
    $stmt->bind_param("sssssi", $name, $email, $phone, $location, $specialization, $user_id );

    if ($stmt->execute()) {
        $success_message = "تم تحديث البيانات بنجاح.";
    } else {
        $error_message = "حدث خطأ أثناء تحديث البيانات.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل الحساب</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        .profile-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
        }
        .profile-pic {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #ddd;
        }
        .btn-primary {
            background-color: #6a0dad;
            border-color: #6a0dad;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center mb-4">تعديل الحساب</h2>

    <div class="row justify-content-center">
        <div class="col-md-6 profile-container">
            <?php if ($success_message): ?>
                <div class="alert alert-success"><?= $success_message; ?></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?= $error_message; ?></div>
            <?php endif; ?>

            <div class="text-center">
                <img src="../uploads/profiles/<?= $user['profile_pic'] ?: 'default.jpg'; ?>" alt="Profile Picture" class="profile-pic">
            </div>

            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">الصورة الشخصية:</label>
                    <input type="file" name="profile_pic" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">الاسم:</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">البريد الإلكتروني:</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">رقم الهاتف:</label>
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">التخصص:</label>
                    <input type="text" name="specialization" class="form-control" value="<?= htmlspecialchars($user['specialization'] ?? ''); ?>">
                </div>
                <div class="mb-3">
    <h5 class="card-title">السيرة الذاتية الحالية</h5>
    <?php if ($current_cv): ?>
        <p>
            <a href="../<?= $current_cv; ?>" target="_blank" class="btn btn-success">
                عرض السيرة الذاتية
            </a>
        </p>
    <?php else: ?>
        <p class="text-muted">لا توجد سيرة ذاتية حالياً.</p>
    <?php endif; ?>

    <label for="cv" class="form-label">اختر ملف السيرة الذاتية (PDF فقط):</label>
    <input type="file" name="cv_link" id="cv_link" class="form-control">
</div>




                <div class="mb-3">
                    <label class="form-label">الموقع:</label>
                    <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($user['location']); ?>">
                </div>

                <button type="submit" class="btn btn-primary w-100">تحديث البيانات</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
