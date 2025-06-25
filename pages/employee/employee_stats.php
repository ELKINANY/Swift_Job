<?php
session_start();

// التحقق من تسجيل الدخول كموظف
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
  header('Location: ../login.php');
  exit();
}

require '../db_connection.php'; // الاتصال بقاعدة البيانات
include "../navBar.php";  // التضمين الجديد للـ Navbar
$user_id = $_SESSION['user_id'];

// جلب الإحصائيات
try {
  // عدد الوظائف اللي قدم عليها
  $stmt = $conn->prepare("SELECT COUNT(*) as total_applications FROM job_applications WHERE user_id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $stats = $result->fetch_assoc();
  $total_applications = $stats['total_applications'];

  // عدد الوظائف المقبول فيها
  $stmt = $conn->prepare("SELECT COUNT(*) as accepted_applications FROM job_applications WHERE user_id = ? AND status = 'accepted'");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $stats = $result->fetch_assoc();
  $accepted_applications = $stats['accepted_applications'];

  // عدد الوظائف تحت المراجعة
  $stmt = $conn->prepare("SELECT COUNT(*) as pending_applications FROM job_applications WHERE user_id = ? AND status = 'pending'");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $stats = $result->fetch_assoc();
  $pending_applications = $stats['pending_applications'];

  // عدد الوظائف اللي اترفض فيها
  $stmt = $conn->prepare("SELECT COUNT(*) as rejected_applications FROM job_applications WHERE user_id = ? AND status = 'rejected'");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $stats = $result->fetch_assoc();
  $rejected_applications = $stats['rejected_applications'];

  // نسبة القبول
  $acceptance_rate = ($total_applications > 0) ? (($accepted_applications / $total_applications) * 100) : 0;
  $acceptance_rate = number_format($acceptance_rate, 2);

  $query = "SELECT j.title, ja.applied_at 
    FROM job_applications ja 
    JOIN jobs j ON ja.job_id = j.job_id 
    WHERE ja.user_id = ? 
    ORDER BY ja.applied_at DESC 
    LIMIT 1";

  $stmt = $conn->prepare($query);

  if (!$stmt) {
    die("⚠️ خطأ في الاستعلام: " . $conn->error);
  }

  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $latest_application = $result->fetch_assoc();


  // معدل التقديم الشهري (عدد التقديمات في الشهر الحالي)
  $current_month = date('Y-m');
  $stmt = $conn->prepare("SELECT COUNT(*) as monthly_applications FROM job_applications WHERE user_id = ? AND DATE_FORMAT(applied_at , '%Y-%m') = ?");
  $stmt->bind_param("is", $user_id, $current_month);
  $stmt->execute();
  $result = $stmt->get_result();
  $stats = $result->fetch_assoc();
  $monthly_applications = $stats['monthly_applications'];

} catch (mysqli_sql_exception $e) {
  $total_applications = 0;
  $accepted_applications = 0;
  $pending_applications = 0;
  $rejected_applications = 0;
  $acceptance_rate = 0;
  $latest_application = null;
  $monthly_applications = 0;
  // لا نعرض الخطأ للمستخدم مباشرة، يمكن تسجيله للأدمن
}
?>

<!DOCTYPE html>
<html lang="ar" dir="ltr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>إحصائيات الموظف</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary: #1e40af;
      /* أزرق داكن */
      --secondary: #3b82f6;
      /* أزرق فاتح */
      --accent: #9333ea;
      /* بنفسجي */
      --background: #f9fafb;
      /* خلفية فاتحة */
      --card-bg: #ffffff;
      /* بطاقات بيضاء */
      --text-dark: #1f2937;
      --text-light: #6b7280;
      --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      --hover-shadow: 0 10px 15px rgba(0, 0, 0, 0.15);
    }

    body {
      font-family: 'Tajawal', sans-serif;
      background-color: var(--background);
      color: var(--text-dark);
      margin: 0;
      padding: 0;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .content {
      flex: 1;
      padding: 2rem;
    }

    h1 {
      font-size: 2rem;
      font-weight: 700;
      color: var(--text-dark);
      margin-bottom: 2rem;
      position: relative;
      text-align: right;
    }

    h1::after {
      content: '';
      position: absolute;
      bottom: -8px;
      right: 0;
      width: 60px;
      height: 4px;
      background: var(--accent);
      border-radius: 2px;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 1.5rem;
      margin-top: 1rem;
    }

    .stat-card {
      background: var(--card-bg);
      border-radius: 15px;
      padding: 1.5rem;
      box-shadow: var(--shadow);
      text-align: center;
      transition: all 0.3s ease;
      border-left: 5px solid var(--secondary);
      overflow: hidden;
    }

    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--hover-shadow);
    }

    .stat-card h3 {
      font-size: 1.1rem;
      font-weight: 600;
      color: var(--text-dark);
      margin-bottom: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .stat-card p {
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--primary);
      margin: 0;
      line-height: 1.2;
    }

    .stat-card .icon {
      font-size: 1.5rem;
      color: var(--accent);
      margin-bottom: 1rem;
    }

    @media (max-width: 768px) {
      .content {
        padding: 1rem;
      }

      h1 {
        font-size: 1.5rem;
      }

      .stats-grid {
        grid-template-columns: 1fr;
      }

      .stat-card {
        padding: 1rem;
      }
    }
  </style>
</head>

<body>
  <!-- المحتوى الرئيسي -->
  <main class="content">
    <h1>إحصائيات الموظف</h1>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="icon"><i class="fas fa-briefcase"></i></div>
        <h3>عدد الوظائف اللي قدمت عليها</h3>
        <p><?php echo $total_applications; ?></p>
      </div>
      <div class="stat-card">
        <div class="icon"><i class="fas fa-check-circle"></i></div>
        <h3>عدد الوظائف المقبول فيها</h3>
        <p><?php echo $accepted_applications; ?></p>
      </div>
      <div class="stat-card">
        <div class="icon"><i class="fas fa-hourglass-half"></i></div>
        <h3>عدد الوظائف تحت المراجعة</h3>
        <p><?php echo $pending_applications; ?></p>
      </div>
      <div class="stat-card">
        <div class="icon"><i class="fas fa-times-circle"></i></div>
        <h3>عدد الوظائف اللي اترفض فيها</h3>
        <p><?php echo $rejected_applications; ?></p>
      </div>
      <div class="stat-card">
        <div class="icon"><i class="fas fa-percentage"></i></div>
        <h3>نسبة القبول (%)</h3>
        <p><?php echo $acceptance_rate; ?>%</p>
      </div>
      <div class="stat-card">
        <div class="icon"><i class="fas fa-calendar-alt"></i></div>
        <h3>أحدث وظيفة قدم عليها</h3>
        <p>
          <?php echo $latest_application ? htmlspecialchars($latest_application['title'] . ' - ' . date('Y-m-d', strtotime($latest_application['applied_at']))) : 'لا يوجد'; ?>
        </p>
      </div>
      <div class="stat-card">
        <div class="icon"><i class="fas fa-chart-line"></i></div>
        <h3>معدل التقديم الشهري</h3>
        <p><?php echo $monthly_applications; ?> تقديم</p>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php $conn->close(); ?>