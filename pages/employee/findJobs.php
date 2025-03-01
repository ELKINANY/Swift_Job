<?php
require '../db_connection.php'; // الاتصال بقاعدة البيانات
include "../navBar.php";
$search_keyword = $_GET['keyword'] ?? '';
$search_location = $_GET['location'] ?? '';
$search_category = $_GET['category'] ?? '';
$search_type = $_GET['job_type'] ?? '';

$query = "SELECT * FROM jobs WHERE 1";

// تطبيق الفلاتر حسب الإدخالات
if (!empty($search_keyword)) {
    $query .= " AND (title LIKE '%$search_keyword%' OR description LIKE '%$search_keyword%')";
}
if (!empty($search_location) && $search_location !== 'All') {
    $query .= " AND location = '$search_location'";
}
if (!empty($search_category) && $search_category !== 'All') {
    $query .= " AND category = '$search_category'";
}
if (!empty($search_type) && $search_type !== 'All') {
    $query .= " AND job_type = '$search_type'";
}

$query .= " ORDER BY created_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>البحث عن الوظائف</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8f9fa; }
        .filter-box {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
        }
        .filter-title {
            font-weight: bold;
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: #2c2c2c;
            border-bottom: 2px solid #6a0dad;
            display: inline-block;
            padding-bottom: 5px;
        }
        .search-btn {
            background-color: #6a0dad;
            color: white;
            width: 100%;
            border-radius: 5px;
            padding: 8px;
            font-size: 1rem;
            font-weight: bold;
        }
        .search-btn:hover {
            background-color: #5a0cad;
        }
        .job-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .job-logo {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ddd;
        }
        .job-details {
            flex-grow: 1;
            padding: 0 15px;
        }
        .job-title {
            font-size: 1.2rem;
            font-weight: bold;
        }
        .badge-expired {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <h2 class="text-center mb-4">البحث عن الوظائف</h2>

    <div class="row">
        <!-- بحث بالكلمات المفتاحية -->
        <div class="col-md-3">
            <div class="filter-box">
                <label class="filter-title">Search Keywords</label>
                <form method="GET">
                    <div class="input-group">
                        <input type="text" name="keyword" class="form-control" placeholder="Search Keywords..." value="<?= htmlspecialchars($search_keyword); ?>">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- البحث بالموقع -->
        <div class="col-md-3">
            <div class="filter-box">
                <label class="filter-title">Location</label>
                <form method="GET">
                    <select name="location" class="form-control">
                        <option value="All">All Location</option>
                        <option value="Dhaka" <?= $search_location == 'Dhaka' ? 'selected' : ''; ?>>Dhaka</option>
                    </select>
                    <button class="search-btn mt-2" type="submit">Search</button>
                </form>
            </div>
        </div>

        <!-- البحث بالفئة -->
        <div class="col-md-3">
            <div class="filter-box">
                <label class="filter-title">Category</label>
                <form method="GET">
                    <select name="category" class="form-control">
                        <option value="All">All Categories</option>
                        <option value="IT" <?= $search_category == 'IT' ? 'selected' : ''; ?>>IT</option>
                        <option value="Marketing" <?= $search_category == 'Marketing' ? 'selected' : ''; ?>>Marketing</option>
                    </select>
                    <button class="search-btn mt-2" type="submit">Search</button>
                </form>
            </div>
        </div>

        <!-- البحث بنوع الوظيفة -->
        <div class="col-md-3">
            <div class="filter-box">
                <label class="filter-title">Job Type</label>
                <form method="GET">
                    <select name="job_type" class="form-control">
                        <option value="All">All Job Types</option>
                        <option value="Full Time" <?= $search_type == 'Full Time' ? 'selected' : ''; ?>>Full Time</option>
                        <option value="Part Time" <?= $search_type == 'Part Time' ? 'selected' : ''; ?>>Part Time</option>
                    </select>
                    <button class="search-btn mt-2" type="submit">Search</button>
                </form>
            </div>
        </div>
    </div>

    <!-- عرض الوظائف -->
    <div class="row mt-4">
        <div class="col-md-12">
            <h4>نتائج الوظائف</h4>
            <?php while ($job = $result->fetch_assoc()): ?>
                <div class="job-card">
                    <!-- <img src="../uploads/company_logos/default.png" alt="Company Logo" class="job-logo"> -->
                    
                    <div class="job-details">
                        <h2 class="job-title"><?= htmlspecialchars($job['title']); ?></h2>
                        <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($job['location']); ?></p>
                        <p><i class="fas fa-briefcase"></i> <?= htmlspecialchars($job['category']); ?></p>
                        <p><i class="fas fa-dollar-sign"></i> <?= number_format((float) $job['salary'], 2); ?></p>
                        <p><i class="fas fa-calendar-alt"></i> <?= date('Y-m-d', strtotime($job['created_at'])); ?></p>
                    </div>

                    <?php if ($job['expiration_date'] < date('Y-m-d')): ?>
                        <span class="badge badge-expired">Expired</span>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
