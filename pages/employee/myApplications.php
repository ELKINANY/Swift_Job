<?php
// اتصال بقاعدة البيانات
require '../db_connection.php'; // الاتصال بقاعدة البيانات
include "../navBar.php";
// بدء الجلسة للحصول على معرف المستخدم
$userId = $_SESSION['user_id'];

// استعلام لاسترجاع الطلبات المقدمة من المستخدم
$query = "SELECT jobs.title AS job_title, job_applications.applied_at, job_applications.expected_salary, job_applications.status 
          FROM job_applications 
          JOIN jobs ON job_applications.job_id = jobs.job_id
          WHERE job_applications.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

// عرض الطلبات في جدول
echo "<style>
table {
    border-collapse: collapse;
    width: 100%;
}

th, td {
    padding: 8px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

th {
    background-color: #4CAF50;
    color: white;
}

tr:hover {
    background-color: #f5f5f5;
}
</style>";

echo "<h1>طلباتك المقدمة</h1>";
echo "<table>";
echo "<tr>
<th>اسم الوظيفة</th>
<th>تاريخ التقديم</th>
<th>الراتب المتوقع</th>
<th>الحالة</th>
</tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['job_title'] . "</td>";
    echo "<td>" . $row['applied_at'] . "</td>";
    echo "<td>" . number_format((float) $row['expected_salary'], 2) . "</td>";
    echo "<td>" . $row['status'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// إغلاق الاتصال بقاعدة البيانات
$conn->close();
?>