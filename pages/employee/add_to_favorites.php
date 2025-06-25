<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

require '../db_connection.php';
$user_id = $_SESSION['user_id'];
$job_id = $_GET['job_id'] ?? null;

if ($job_id) {
    try {
        // التحقق إذا كانت الوظيفة مضافة قبل كده
        $check_query = "SELECT id FROM favorites WHERE user_id = ? AND job_id = ?";
        $check_stmt = $conn->prepare($check_query);
        
        if (!$check_stmt) {
            die("Prepare failed: " . $conn->error);
        }
        
        $check_stmt->bind_param("ii", $user_id, $job_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        

        if ($result->num_rows == 0) {
            // إضافة الوظيفة للمفضلة لو مفيش
            $insert_query = "INSERT INTO favorites (user_id, job_id) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("ii", $user_id, $job_id);
            if ($insert_stmt->execute()) {
                // نجاح الإضافة
                header('Location: favorite_jobs.php?message=success');
            } else {
                throw new Exception("فشل في إضافة الوظيفة للمفضلة.");
            }
        } else {
            // الوظيفة مضافة قبل كده
            header('Location: favorite_jobs.php?message=already_added');
        }
    } catch (Exception $e) {
        header('Location: favorite_jobs.php?message=error&error=' . urlencode($e->getMessage()));
    }
} else {
    header('Location: favorite_jobs.php?message=invalid_job');
    exit();
}

$conn->close();
?>