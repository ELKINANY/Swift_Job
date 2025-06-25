<?php
require '../db_connection.php'; // الاتصال بقاعدة البيانات

$search_keyword = $_GET['keyword'] ?? '';
$search_location = $_GET['location'] ?? '';
$search_category = $_GET['category'] ?? '';

$query = "SELECT * FROM companies WHERE 1";

// تطبيق الفلاتر حسب الإدخالات
if (!empty($search_keyword)) {
    $query .= " AND (name LIKE '%$search_keyword%' OR description LIKE '%$search_keyword%')";
}
if (!empty($search_location) && $search_location !== 'All') {
    $query .= " AND location = '$search_location'";
}
if (!empty($search_category) && $search_category !== 'All') {
    $query .= " AND category = '$search_category'";
}

$query .= " ORDER BY created_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>البحث عن الشركات</title>
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
        .company-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .company-logo {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ddd;
        }
        .company-details {
            flex-grow: 1;
            padding: 0 15px;
        }
        .company-name {
            font-size: 1.2rem;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <h2 class="text-center mb-4">البحث عن الشركات</h2>

    <div class="row">
        <!-- بحث بالكلمات المفتاحية -->
        <div class="col-md-4">
            <div class="filter-box">
                <label class="filter-title">Search Keywords</label>
                <form method="GET">
                    <div class="input-group">
                        <input type="text" name="keyword" class="form-control" placeholder="Search Companies..." value="<?= htmlspecialchars($search_keyword); ?>">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- البحث بالموقع -->
        <div class="col-md-4">
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
        <div class="col-md-4">
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
    </div>

    <!-- عرض الشركات -->
    <div class="row mt-4">
        <div class="col-md-12">
            <h4>نتائج الشركات</h4>
            <?php while ($company = $result->fetch_assoc()): ?>
                <div class="company-card">
                    <img src="../uploads/company_logos/<?= $company['logo'] ?: 'default.png'; ?>" alt="Company Logo" class="company-logo">
                    
                    <div class="company-details">
                        <h2 class="company-name"><?= htmlspecialchars($company['name']); ?></h2>
                        <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($company['location']); ?></p>
                        <p><i class="fas fa-briefcase"></i> <?= htmlspecialchars($company['category']); ?></p>
                        <p><i class="fas fa-calendar-alt"></i> تأسست في <?= date('Y', strtotime($company['created_at'])); ?></p>
                    </div>

                    <a href="company_profile.php?id=<?= $company['id']; ?>" class="btn btn-outline-primary">عرض التفاصيل</a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
