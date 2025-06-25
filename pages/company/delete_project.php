<?php
session_start();

// التحقق من أن المستخدم مسجل دخول وهو شركة
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'company') {
    header("Location: ../login.php");
    exit();
}

require '../db_connection.php';

$project_id = $_GET['project_id'];
$company_id = $_SESSION['user_id'];

// التحقق من أن المشروع يخص الشركة قبل الحذف
$stmt = $conn->prepare("DELETE FROM projects WHERE project_id = ? AND company_id = ?");
$stmt->bind_param("ii", $project_id, $company_id);

if ($stmt->execute()) {
    $_SESSION['success_message'] = "تم حذف المشروع بنجاح";
} else {
    $_SESSION['error_message'] = "حدث خطأ أثناء حذف المشروع";
}

$stmt->close();
header("Location: view_projects.php");
exit();
?>