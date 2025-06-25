<?php
include "../navBar.php";
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
    SELECT jobs.job_id, jobs.title, jobs.job_type, jobs.salary, jobs.description, jobs.requirements
    FROM jobs
    WHERE jobs.specialization = ?
");
$stmt->bind_param("s", $specialization);
$stmt->execute();
$recommended_jobs = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>وظائف مخصصة</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.rtl.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">

  <style>
  body {
    font-family: 'Tajawal', sans-serif;
    background: linear-gradient(135deg, #f4f7fa, #e0e7ff);
    min-height: 100vh;
    margin: 0;
    padding: 0;
  }

  .container {
    padding: 3rem 1rem;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .container:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
  }

  h1 {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1e3a8a;
    text-align: center;
    margin-bottom: 2rem;
    position: relative;
  }

  h1::after {
    content: '';
    position: absolute;
    width: 60px;
    height: 5px;
    background: linear-gradient(90deg, #1e3a8a, #3b82f6);
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
  }

  .table-responsive {
    overflow-x: auto;
  }

  .table {
    border-radius: 15px;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.95);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
  }

  .table thead th {
    background: linear-gradient(90deg, #1e3a8a, #3b82f6);
    color: white;
    font-weight: 600;
    padding: 1rem;
    text-align: center;
    border: none;
  }

  .table tbody tr {
    transition: all 0.3s ease;
  }

  .table tbody tr:hover {
    background: #f1f5f9;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  }

  .table tbody td {
    vertical-align: middle;
    padding: 1rem;
    text-align: center;
    color: #2d3748;
    border-bottom: 1px solid #e2e8f0;
  }

  .apply-btn {
    background: linear-gradient(90deg, #1e3a8a, #3b82f6);
    border: none;
    padding: 0.5rem 1.5rem;
    border-radius: 10px;
    color: white;
    font-weight: 500;
    transition: all 0.3s ease;
    white-space: nowrap;
  }

  .apply-btn:hover {
    background: linear-gradient(90deg, #163072, #2f69c3);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(59, 130, 246, 0.5);
  }

  .alert-warning {
    background-color: #fff3cd;
    color: #856404;
    border: 1px solid #ffeeba;
    border-radius: 10px;
    padding: 1rem;
  }

  @media (max-width: 768px) {
    h1 {
      font-size: 2rem;
    }

    .table thead th,
    .table tbody td {
      font-size: 0.9rem;
      padding: 0.75rem;
    }

    .apply-btn {
      padding: 0.4rem 1rem;
      font-size: 0.9rem;
    }

    .table {
      font-size: 0.85rem;
    }
  }
  </style>
</head>

<body>
  <div class="container mt-5">
    <h1 class="text-center mb-4">الوظائف المخصصة لتخصصك: <?= htmlspecialchars($specialization); ?> <i
        class="fas fa-briefcase"></i></h1>

    <?php if ($recommended_jobs->num_rows > 0): ?>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead class="table-dark">
          <tr>
            <th>المسمى الوظيفي</th>
            <th>نوع الوظيفة</th>
            <th>الراتب</th>
            <th>الوصف</th>
            <th>المتطلبات</th>
            <th>الإجراء</th>
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
            <td>
              <a href="apply_job.php?job_id=<?= $job['job_id']; ?>" class="btn apply-btn">
                <i class="fas fa-paper-plane me-2"></i> قدم الآن
              </a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
    <div class="alert alert-warning text-center">لا توجد وظائف مخصصة لتخصصك حاليًا.</div>
    <?php endif; ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php $conn->close(); ?>