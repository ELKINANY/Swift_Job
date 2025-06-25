<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: ../login.php');
  exit();
}

require '../db_connection.php';

// Handle all redirects before including navBar.php
if (isset($_GET['add'])) {
  $job_id = $_GET['add'];
  if (!isset($_SESSION['compare_jobs']) || !is_array($_SESSION['compare_jobs'])) {
    $_SESSION['compare_jobs'] = [];
  }
  if (count($_SESSION['compare_jobs']) < 2 && !in_array($job_id, $_SESSION['compare_jobs'])) {
    $_SESSION['compare_jobs'][] = $job_id;
  } elseif (count($_SESSION['compare_jobs']) >= 2) {
    header('Location: compare_jobs.php?message=max_limit');
    exit();
  }
  header('Location: compare_jobs.php');
  exit();
}

if (isset($_GET['clear'])) {
  unset($_SESSION['compare_jobs']);
  header('Location: compare_jobs.php');
  exit();
}


function getApplicantCount($job_id) {
  global $conn;
  $stmt = $conn->prepare("SELECT COUNT(*) as count FROM job_applications WHERE job_id = ?");
  $stmt->bind_param("i", $job_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  return $row['count'];
}

// Now include navBar.php after all potential redirects
include "../navBar.php";

$user_id = $_SESSION['user_id'];

// جلب بيانات الوظائف المختارة
$jobs = [];
if (isset($_SESSION['compare_jobs']) && count($_SESSION['compare_jobs']) == 2) {
  $job_ids = implode(',', $_SESSION['compare_jobs']);
  $query = "SELECT job_id, title, salary, location, specialization, created_at 
            FROM jobs WHERE job_id IN ($job_ids)";
  $result = $conn->query($query);
  while ($row = $result->fetch_assoc()) {
    $jobs[$row['job_id']] = $row;
  }
  if (count($jobs) != 2) {
    unset($_SESSION['compare_jobs']);
  }
}

// تحديد الوظيفة الأفضل (بناءً على الراتب كمعيار أساسي)
$best_job_id = null;
if (count($jobs) == 2) {
  $job1_salary = (float)($jobs[$_SESSION['compare_jobs'][0]]['salary'] ?? 0);
  $job2_salary = (float)($jobs[$_SESSION['compare_jobs'][1]]['salary'] ?? 0);
  $best_job_id = $job1_salary > $job2_salary ? $_SESSION['compare_jobs'][0] : $_SESSION['compare_jobs'][1];
}
?>

<!DOCTYPE html>
<html lang="ar">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>مقارنة الوظائف</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Tajawal', sans-serif;
      background-color: #f9fafb;
      margin: 0;
      padding: 0;
    }

    .container {
      max-width: 900px;
      margin-top: 2rem;
      padding: 0 1rem;
    }

    .compare-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
    }

    .view-toggle {
      display: flex;
      gap: 1rem;
    }

    .view-toggle button {
      background: #fff;
      border: 1px solid #3b82f6;
      color: #3b82f6;
      padding: 0.5rem 1rem;
      border-radius: 5px;
      cursor: pointer;
    }

    .view-toggle button.active {
      background: #3b82f6;
      color: #fff;
    }

    .message {
      margin-top: 1rem;
      padding: 1rem;
      border-radius: 8px;
      text-align: center;
    }

    .message.error {
      background: #f8d7da;
      color: #842029;
    }

    .compare-table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0 1rem;
      display: none;
    }

    .compare-table th,
    .compare-table td {
      padding: 1rem;
      text-align: center;
      background: #fff;
      border-radius: 10px;
    }

    .compare-table th {
      background: #3b82f6;
      color: white;
    }

    .compare-table td {
      vertical-align: middle;
    }

    .compare-table.best {
      background: #d1fae5;
      border-left: 5px solid #10b981;
    }

    .compare-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
      gap: 1.5rem;
      display: none;
    }

    .card {
      background: #fff;
      border-radius: 15px;
      padding: 1.5rem;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      border-left: 5px solid #3b82f6;
    }

    .card.best {
      background: #d1fae5;
      border-left: 5px solid #10b981;
    }

    .card h4 {
      font-size: 1.2rem;
      font-weight: 600;
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .card p {
      margin: 0.5rem 0;
      font-size: 1rem;
    }

    .card .apply-btn {
      margin-top: 1rem;
    }

    .apply-btn {
      background: #10b981;
      color: #fff;
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 5px;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .apply-btn:hover {
      background: #059669;
      transform: translateY(-2px);
    }

    @media (max-width: 768px) {
      .container {
        margin-top: 1rem;
        padding: 0.5rem;
      }

      .compare-cards {
        grid-template-columns: 1fr;
      }

      .card {
        padding: 1rem;
      }
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="compare-header">
      <h2 class="mb-0"><i class="fas fa-balance-scale"></i> مقارنة الوظائف</h2>
      <?php if (isset($_SESSION['compare_jobs']) && count($_SESSION['compare_jobs']) == 2): ?>
        <div class="view-toggle">
          <button class="table-view active" onclick="toggleView('table')">جدول</button>
          <button class="card-view" onclick="toggleView('card')">كروت</button>
        </div>
      <?php endif; ?>
    </div>

    <?php if (isset($_GET['message']) && $_GET['message'] == 'max_limit'): ?>
      <div class="message error">لا يمكن مقارنة أكثر من وظيفتين!</div>
    <?php endif; ?>

    <?php if (isset($_SESSION['compare_jobs']) && count($_SESSION['compare_jobs']) == 2 && count($jobs) == 2): ?>
      <div class="compare-table" id="table-view">
        <table>
          <tr>
            <th><i class="fas fa-info-circle"></i> العنوان</th>
            <th><?= htmlspecialchars($jobs[$_SESSION['compare_jobs'][0]]['title'] ?? '') ?></th>
            <th><?= htmlspecialchars($jobs[$_SESSION['compare_jobs'][1]]['title'] ?? '') ?></th>
          </tr>
          <tr class="<?= $best_job_id == $_SESSION['compare_jobs'][0] ? 'best' : '' ?>">
            <td><i class="fas fa-money-bill-wave"></i> الراتب (شهريا)</td>
            <td><?= number_format((float)($jobs[$_SESSION['compare_jobs'][0]]['salary'] ?? 0), 2) ?> جنيه</td>
            <td><?= number_format((float)($jobs[$_SESSION['compare_jobs'][1]]['salary'] ?? 0), 2) ?> جنيه</td>
          </tr>
          <tr>
            <td><i class="fas fa-map-marker-alt"></i> الموقع</td>
            <td><?= htmlspecialchars($jobs[$_SESSION['compare_jobs'][0]]['location'] ?? '') ?></td>
            <td><?= htmlspecialchars($jobs[$_SESSION['compare_jobs'][1]]['location'] ?? '') ?></td>
          </tr>
          <tr>
            <td><i class="fas fa-briefcase"></i> التخصص</td>
            <td><?= htmlspecialchars($jobs[$_SESSION['compare_jobs'][0]]['specialization'] ?? '') ?></td>
            <td><?= htmlspecialchars($jobs[$_SESSION['compare_jobs'][1]]['specialization'] ?? '') ?></td>
          </tr>
          <tr>
            <td><i class="fas fa-calendar-alt"></i> تاريخ النشر</td>
            <td><?= date('Y-m-d', strtotime($jobs[$_SESSION['compare_jobs'][0]]['created_at'] ?? '')) ?></td>
            <td><?= date('Y-m-d', strtotime($jobs[$_SESSION['compare_jobs'][1]]['created_at'] ?? '')) ?></td>
          </tr>
          <tr class="<?= $best_job_id == $_SESSION['compare_jobs'][0] ? 'best' : '' ?>">
            <td><i class="fas fa-users"></i> عدد المتقدمين</td>
            <td><?= getApplicantCount($_SESSION['compare_jobs'][0]) ?></td>
            <td><?= getApplicantCount($_SESSION['compare_jobs'][1]) ?></td>
          </tr>
          <tr>
            <td></td>
            <td><button class="apply-btn" onclick="applyJob(<?= $_SESSION['compare_jobs'][0] ?>)">التقديم</button></td>
            <td><button class="apply-btn" onclick="applyJob(<?= $_SESSION['compare_jobs'][1] ?>)">التقديم</button></td>
          </tr>
        </table>
        <div class="text-center mt-3">
          <a href="compare_jobs.php?clear=1" class="btn btn-danger">مسح المقارنة</a>
        </div>
      </div>

      <div class="compare-cards" id="card-view">
        <?php foreach ($jobs as $job_id => $job): ?>
          <div class="card <?= $best_job_id == $job_id ? 'best' : '' ?>">
            <h4><i class="fas fa-info-circle"></i> <?= htmlspecialchars($job['title']) ?></h4>
            <p><i class="fas fa-money-bill-wave"></i> الراتب: <?= number_format((float)($job['salary'] ?? 0), 2) ?> جنيه</p>
            <p><i class="fas fa-map-marker-alt"></i> الموقع: <?= htmlspecialchars($job['location'] ?? '') ?></p>
            <p><i class="fas fa-briefcase"></i> التخصص: <?= htmlspecialchars($job['specialization'] ?? '') ?></p>
            <p><i class="fas fa-calendar-alt"></i> تاريخ النشر: <?= date('Y-m-d', strtotime($job['created_at'] ?? '')) ?></p>
            <p><i class="fas fa-users"></i> عدد المتقدمين: <?= getApplicantCount($job_id) ?></p>
            <button class="apply-btn" onclick="applyJob(<?= $job_id ?>)">التقديم</button>
          </div>
        <?php endforeach; ?>
        <div class="text-center mt-3">
          <a href="compare_jobs.php?clear=1" class="btn btn-danger">مسح المقارنة</a>
        </div>
      </div>
    <?php else: ?>
      <p class="text-center text-muted">اختر وظيفتين للمقارنة من صفحة البحث أو المفضلة.</p>
    <?php endif; ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function toggleView(view) {
      const tableView = document.getElementById('table-view');
      const cardView = document.getElementById('card-view');
      const tableBtn = document.querySelector('.table-view');
      const cardBtn = document.querySelector('.card-view');

      if (view === 'table') {
        tableView.style.display = 'block';
        cardView.style.display = 'none';
        tableBtn.classList.add('active');
        cardBtn.classList.remove('active');
      } else {
        tableView.style.display = 'none';
        cardView.style.display = 'grid';
        tableBtn.classList.remove('active');
        cardBtn.classList.add('active');
      }
    }

    function applyJob(jobId) {
      if (confirm('هل أنت متأكد من التقديم على هذه الوظيفة؟')) {
        window.location.href = `apply_job.php?job_id=${jobId}`;
      }
    }

    // تفعيل العرض الافتراضي
    document.addEventListener('DOMContentLoaded', () => {
      toggleView('table');
    });
  </script>
</body>

</html>
<?php $conn->close(); ?>