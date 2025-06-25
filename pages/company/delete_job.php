<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') {
    header('Location: login.php');
    exit();
}

require '../db_connection.php';

// التحقق من وجود job_id في الرابط
if (!isset($_GET['job_id'])) {
    die("لم يتم تحديد الوظيفة للحذف.");
}

$job_id = intval($_GET['job_id']);
$company_id = $_SESSION['user_id'];

// التحقق من أن الوظيفة تخص الشركة الحالية
$stmt = $conn->prepare("SELECT * FROM jobs WHERE job_id = ? AND company_id = ?");
$stmt->bind_param("ii", $job_id, $company_id);
$stmt->execute();
$job = $stmt->get_result()->fetch_assoc();

if (!$job) {
    die("الوظيفة غير موجودة أو لا تملك صلاحية لحذفها.");
}

// حذف الوظيفة
$stmt = $conn->prepare("DELETE FROM jobs WHERE job_id = ? AND company_id = ?");
$stmt->bind_param("ii", $job_id, $company_id);

if ($stmt->execute()) {
    // إعادة التوجيه بعد الحذف
    header("Location: view_jobs.php?success=job_deleted");
    exit();
} else {
    die("حدث خطأ أثناء محاولة حذف الوظيفة.");
}
?>
