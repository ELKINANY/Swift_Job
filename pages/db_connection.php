<?php
// إعدادات الاتصال بقاعدة البيانات
$host = 'localhost';
$user = 'root';
$password = ''; // تأكد من تعديل كلمة المرور حسب الإعدادات لديك
$dbname = 'job_portal';

// إنشاء الاتصال
$conn = new mysqli($host, $user, $password, $dbname);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

// تعيين الترميز إلى UTF-8
$conn->set_charset("utf8");
?>
