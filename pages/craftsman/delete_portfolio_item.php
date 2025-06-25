<?php
require '../db_connection.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'DELETE' && isset($_GET['id'])) {
    $item_id = $_GET['id'];
    $craftsman_id = $_SESSION['user_id'];

    // جلب اسم الملف
    $stmt = $conn->prepare("SELECT file_name FROM craftsman_portfolio WHERE portfolio_id = ? AND craftsman_id = ?");
    $stmt->bind_param("ii", $item_id, $craftsman_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $item = $result->fetch_assoc();
        $file_path = realpath('../Uploads/portfolio/' . $item['file_name']);

        // حذف الملف من السيرفر
        if ($file_path && file_exists($file_path)) {
            unlink($file_path);
        }

        // حذف السجل من قاعدة البيانات
        $stmt = $conn->prepare("DELETE FROM craftsman_portfolio WHERE portfolio_id = ? AND craftsman_id = ?");
        $stmt->bind_param("ii", $item_id, $craftsman_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'فشل حذف السجل']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'العنصر غير موجود']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'طلب غير صالح']);
}
?>