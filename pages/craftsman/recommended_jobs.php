<?php
include "../navBar.php";
// session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
    header('Location: ../login.php');
    exit();
}

require '../db_connection.php';

$user_id = $_SESSION['user_id'];

// جلب التخصص الخاص بالموظف
$stmt = $conn->prepare("SELECT specialization FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("تعذر جلب بيانات المستخدم.");
}

$specialization = $user['specialization'];

// جلب الوظائف التي تتطابق مع التخصص
$stmt = $conn->prepare("
    SELECT jobs.title, jobs.job_type, jobs.salary, jobs.description, jobs.requirements
    FROM jobs
    WHERE jobs.specialization = ?
");
$stmt->bind_param("s", $specialization);
$stmt->execute();
$recommended_jobs = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>وظائف مخصصة</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center mb-4">الوظائف المخصصة لتخصصك: <?= htmlspecialchars($specialization); ?></h1>

    <?php if ($recommended_jobs->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>المسمى الوظيفي</th>
                    <th>نوع الوظيفة</th>
                    <th>الراتب</th>
                    <th>الوصف</th>
                    <th>المتطلبات</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($job = $recommended_jobs->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($job['title']); ?></td>
                        <td><?= htmlspecialchars($job['job_type']); ?></td>
                        <td><?= number_format($job['salary'], 2); ?> جنيه</td>
                        <td><?= htmlspecialchars($job['description']); ?></td>
                        <td><?= htmlspecialchars($job['requirements']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning text-center">لا توجد وظائف مخصصة لتخصصك حاليًا.</div>
    <?php endif; ?>
</div>
</body>
</html>
