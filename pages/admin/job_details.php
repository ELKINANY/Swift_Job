<?php
require '../db_connection.php';
session_start();

$job_id = $_GET['job_id'] ?? null;
if (!$job_id) {
    header('Location: index.php');
    exit();
}

$stmt = $conn->prepare("SELECT j.*, c.name as company_name, c.logo FROM jobs j 
                       LEFT JOIN companies c ON j.company_id = c.company_id 
                       WHERE j.job_id = ?");
$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result(); // Add this line to get the result set
$job = $result->fetch_assoc(); // Use fetch_assoc() on the result object instead

if (!$job) {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($job['title']); ?> - Swift Jobs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/admin_header.php'; ?>

    <div class="container mt-5">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <?php if ($job['logo']): ?>
                    <img src="<?php echo htmlspecialchars($job['logo']); ?>" alt="Company Logo" class="me-3" style="width: 50px; height: 50px;">
                <?php endif; ?>
                <h2 class="mb-0"><?php echo htmlspecialchars($job['title']); ?></h2>
            </div>
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($job['company_name']); ?></h5>
                <p class="card-text"><strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?></p>
                <p class="card-text"><strong>Salary:</strong> <?php echo htmlspecialchars($job['salary'] ?? 'Not specified'); ?></p>
                <p class="card-text"><strong>Experience:</strong> <?php echo htmlspecialchars($job['experience'] ?? 'Not specified'); ?> years</p>
                
                <h5 class="mt-4">Job Description</h5>
                <p class="card-text"><?php echo nl2br(htmlspecialchars($job['description'])); ?></p>

                <?php if ($job['status'] === 'active'): ?>
                    <a href="apply.php?job_id=<?php echo $job['id']; ?>" class="btn btn-primary mt-3">Apply Now</a>
                <?php else: ?>
                    <div class="alert alert-warning mt-3">This job posting is no longer active</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
