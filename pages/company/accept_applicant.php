<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'company') {
    header("Location: ../login.php");
    exit();
}

require '../db_connection.php';

$bid_id = $_GET['bid_id'];
$company_id = $_SESSION['user_id'];

// التحقق من أن العرض يخص مشروع الشركة
$stmt = $conn->prepare("UPDATE project_bids pb
                      JOIN projects p ON pb.project_id = p.project_id
                      SET pb.status = 'accepted'
                      WHERE pb.bid_id = ? AND p.company_id = ?");
$stmt->bind_param("ii", $bid_id, $company_id);

if ($stmt->execute()) {
    $_SESSION['success_message'] = "تم قبول العرض بنجاح";
} else {
    $_SESSION['error_message'] = "حدث خطأ أثناء قبول العرض";
}

$stmt->close();
header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
?>