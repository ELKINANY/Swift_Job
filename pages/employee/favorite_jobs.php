<?php
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

require '../db_connection.php'; // الاتصال بقاعدة البيانات
include "../navBar.php";

$user_id = $_SESSION['user_id'];

// جلب الوظائف المفضلة
$query = "SELECT j.* FROM jobs j 
          JOIN favorites f ON j.job_id = f.job_id 
          WHERE f.user_id = ? 
          ORDER BY f.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// معالجة الرسائل
$message = $_GET['message'] ?? '';
$error = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>الوظائف المفضلة</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
    body {
        font-family: 'Tajawal', sans-serif;
        background-color: #f8f9fa;
    }

    .results-title {
        color: #2c3e50;
        font-weight: 700;
        border-bottom: 2px solid #3498db;
        padding-bottom: 10px;
        display: inline-block;
    }

    .job-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .job-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
    }

    .job-card.expired-job {
        opacity: 0.7;
        border-left: 5px solid #ef4444;
    }

    .job-card-body {
        padding: 0;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
    }

    .job-header {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        width: 100%;
    }

    .job-title {
        font-size: 1.4rem;
        font-weight: 700;
        color: #e2e8f0;
        margin: 0 15px 0 0;
    }

    .job-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 15px;
        width: 100%;
    }

    .meta-item {
        display: flex;
        align-items: center;
        color: #94a3b8;
        font-size: 0.95rem;
    }

    .meta-item i {
        margin-left: 5px;
        color: #3b82f6;
        transition: transform 0.3s ease;
    }

    .meta-item:hover i {
        transform: scale(1.2);
    }

    .job-secondary-info {
        display: flex;
        gap: 20px;
        margin-top: 10px;
    }

    .salary-info,
    .date-info {
        display: flex;
        align-items: center;
        font-weight: 600;
    }

    .salary-info i {
        color: #34d399;
    }

    .date-info i {
        color: #9b59b6;
    }

    .job-actions {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }

    .details-btn {
        border-radius: 8px;
        padding: 8px 20px;
        font-weight: 600;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        border: 2px solid #3b82f6;
        color: #3b82f6;
    }

    .details-btn:hover {
        background: #3b82f6;
        color: white;
        box-shadow: 0 5px 15px rgba(59, 130, 246, 0.5);
    }

    .message {
        margin-top: 1rem;
        padding: 1rem;
        border-radius: 0.5rem;
        text-align: center;
    }

    .message.success {
        background-color: #d1e7dd;
        color: #0f5132;
    }

    .message.error {
        background-color: #f8d7da;
        color: #842029;
    }

    .message.info {
        background-color: #cce5ff;
        color: #004085;
    }

    @media (max-width: 768px) {
        .job-card-body {
            flex-direction: column;
            align-items: flex-start;
        }

        .job-meta {
            flex-direction: column;
            gap: 10px;
        }

        .job-secondary-info {
            flex-direction: column;
            gap: 10px;
            width: 100%;
            margin: 15px 0;
        }

        .job-actions {
            width: 100%;
            justify-content: flex-start;
        }

        .details-btn {
            width: 100%;
        }
    }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4 fw-bold text-primary" style="font-family: 'Tajawal', sans-serif;">الوظائف المفضلة</h2>

        <?php if ($message): ?>
            <div class="message <?= $message === 'success' ? 'success' : ($message === 'error' ? 'error' : 'info') ?>">
                <?php
                if ($message === 'success') echo "تم إضافة الوظيفة للمفضلة بنجاح!";
                elseif ($message === 'already_added') echo "هذه الوظيفة مضافة بالفعل في المفضلة.";
                elseif ($message === 'invalid_job') echo "معرف الوظيفة غير صالح.";
                elseif ($message === 'error') echo "حدث خطأ: " . htmlspecialchars($error);
                ?>
            </div>
        <?php endif; ?>

        <?php if ($result->num_rows > 0): ?>
            <div class="row mt-4">
                <div class="col-md-12">
                    <h4 class="results-title mb-4">الوظائف المفضلة <span
                            class="badge bg-primary"><?= $result->num_rows ?> وظيفة</span></h4>

                    <?php while ($job = $result->fetch_assoc()): ?>
                        <div
                            class="job-card <?= ($job['expiration_date'] < date('Y-m-d') || $job['status'] == 0) ? 'expired-job' : '' ?>">
                            <div class="job-card-body">
                                <div class="job-main-info">
                                    <div class="job-header">
                                        <h2 class="job-title"><?= htmlspecialchars($job['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </h2>
                                        <?php if ($job['expiration_date'] < date('Y-m-d') || $job['status'] == 0): ?>
                                            <span class="badge bg-danger ms-2 p-2 ">منتهية</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="job-meta">
                                        <div class="meta-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span><?= htmlspecialchars($job['location'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                                        </div>
                                        <div class="meta-item">
                                            <i class="fas fa-briefcase"></i>
                                            <span><?= htmlspecialchars($job['specialization'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="job-secondary-info">
                                    <div class="salary-info">
                                        <i class="fas fa-dollar-sign"></i>
                                        <span><?= number_format((float) $job['salary'], 2); ?> <small>شهريا</small></span>
                                    </div>
                                    <div class="date-info">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span><?= date('Y-m-d', strtotime($job['created_at'])); ?></span>
                                    </div>
                                </div>

                                <div class="job-actions">
                                    <a href="job_details.php?job_id=<?= $job['job_id']; ?>" class="btn details-btn">
                                        <i class="fas fa-eye"></i> التفاصيل
                                    </a>

                                    <a href="compare_jobs.php?add=<?= $job['job_id']; ?>" class="btn btn-info btn-sm">إضافة للمقارنة</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center mt-5">
                <p class="text-muted">لا توجد وظائف مفضلة حاليًا.</p>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php $conn->close(); ?>