<?php
include "../navBar.php";
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

require '../db_connection.php';

$user_id = $_SESSION['user_id'];

// جلب بيانات المستخدم
$stmt = $conn->prepare("SELECT name, email, phone, location, profile_pic FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$success_message = "";
$error_message = "";

// تحديث البيانات عند الإرسال
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $location = trim($_POST['location']);

    // تحديث الصورة الشخصية
    if (!empty($_FILES['profile_pic']['name'])) {
        $target_dir = "../uploads/profiles/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $profile_pic = "profile_" . $user_id . ".jpg";
        $target_file = $target_dir . $profile_pic;
        move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file);

        // تحديث الصورة الشخصية في قاعدة البيانات
        $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE user_id = ?");
        $stmt->bind_param("si", $profile_pic, $user_id);
        $stmt->execute();
    }

    // تحديث البيانات في قاعدة البيانات
    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, location = ? WHERE user_id = ?");
    $stmt->bind_param("ssssi", $name, $email, $phone, $location, $user_id);

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
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']); ?>">
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
