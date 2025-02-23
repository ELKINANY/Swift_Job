<?php
 include "../navBar.php";
// session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
    header('Location: ../login.php');
    exit();
}

require '../db_connection.php';

$user_id = $_SESSION['user_id'];

// جلب السيرة الذاتية الحالية
$stmt = $conn->prepare("SELECT cv_link FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$current_cv = $user['cv_link'] ?? null;

$success_message = "";
$error_message = "";

// معالجة رفع أو تحديث السيرة الذاتية
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['cv'])) {
    $cv = $_FILES['cv'];

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
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة السيرة الذاتية</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center mb-4">إدارة السيرة الذاتية</h1>

    <?php if ($success_message): ?>
        <div class="alert alert-success"><?= $success_message; ?></div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?= $error_message; ?></div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-body">
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
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><?= $current_cv ? "تحديث السيرة الذاتية" : "إضافة سيرة ذاتية"; ?></h5>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="cv" class="form-label">اختر ملف السيرة الذاتية (PDF فقط):</label>
                    <input type="file" name="cv" id="cv" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary"><?= $current_cv ? "تحديث" : "إضافة"; ?></button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
