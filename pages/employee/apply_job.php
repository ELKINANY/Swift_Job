<?php
// اتصال بقاعدة البيانات
require '../db_connection.php'; // الاتصال بقاعدة البيانات
include "../navBar.php";


$userId = $_SESSION['user_id'];

// استقبال معرف الوظيفة
$jobId = $_GET['job_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // معالجة تحميل السيرة الذاتية
    $resume = $_FILES['resume']['name'];
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true); // Create the directory with proper permissions
    }
    
    $targetFile = $uploadDir . basename($_FILES['resume']['name']);
    if (move_uploaded_file($_FILES['resume']['tmp_name'], $targetFile)) {
        // إدخال الطلب في قاعدة البيانات
        $query = "INSERT INTO job_applications (job_id, user_id, cover_letter, expected_salary, resume, applied_at, why_job, why_company) 
                  VALUES (?, ?, ?, ?, ?, NOW(), ?, ?)";
        $stmt = $conn->prepare($query);
        $coverLetter = $_POST['cover_letter'];
        $expectedSalary = $_POST['expected_salary'];
        $whyJob = $_POST['why_job'];
        $whyCompany = $_POST['why_company'];
        $stmt->bind_param("iisssss", $jobId, $userId, $coverLetter, $expectedSalary, $resume, $whyJob, $whyCompany);
        $stmt->execute();

        echo '<script>alert("تم تقديم طلبك بنجاح!");</script>';
    } else {
        echo '<script>alert("حدث خطأ أثناء تحميل السيرة الذاتية.");</script>';
    }
}
?>
<style>
    form {
        width: 50%;
        margin: 20px auto;
        text-align: right;
        background-color: #f1f1f1;
        padding: 20px;
        border-radius: 10px;
    }
    label {
        display: block;
        margin-bottom: 10px;
        font-weight: bold;
    }
    input[type="file"] {
        display: block;
        margin-bottom: 20px;
        width: 100%;
    }
    textarea {
        width: 100%;
        height: 100px;
        margin-bottom: 20px;
    }
    input[type="submit"] {
        background-color: #4CAF50;
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    input[type="number"] {
        width: 100%;
        margin-bottom: 20px;
    }

</style>

<form method="post" enctype="multipart/form-data">
    <label for="resume">تحميل السيرة الذاتية:</label>
    <input type="file" name="resume" id="resume" required>
    <br>
    <label for="cover_letter">خطاب التقديم:</label>
    <textarea name="cover_letter" id="cover_letter" required></textarea>
    <br>
    <label for="expected_salary">الراتب المتوقع:</label>
    <input type="number" name="expected_salary" id="expected_salary" required>
    <br>

    <h3>الاسئلة الخاصة بالتقديم:</h3>
    <p>يرجى الاجابة على الاسئلة التالية:</p>
    <label for="why_job">ما الذي جعلتك تقدم علي هذه الوظيفة؟</label>
    <textarea name="why_job" id="why_job" required></textarea>
    <br>
    <label for="why_company">ما الذي جعلتك تريد ان تعمل في هذه الشركة؟</label>
    <textarea name="why_company" id="why_company" required></textarea>
    <br>


    <input type="submit" value="تقديم">
</form>