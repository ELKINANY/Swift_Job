<?php
require '../db_connection.php';
session_start();
// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Get current admin data
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
if ($stmt === false) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    die("Database error: " . $conn->error);
}

$admin_data = $result->fetch_assoc();
if (!$admin_data) {
    die("Admin not found");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Validate and sanitize inputs
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate required fields
    if (empty($name)) {
        $errors[] = 'الاسم مطلوب';
    }
    
    if (empty($email)) {
        $errors[] = 'البريد الإلكتروني مطلوب';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'صيغة البريد الإلكتروني غير صحيحة';
    }
    
    if (!empty($new_password)) {
        if (strlen($new_password) < 8) {
            $errors[] = 'كلمة المرور يجب أن تكون 8 أحرف على الأقل';
        } elseif ($new_password !== $confirm_password) {
            $errors[] = 'كلمات المرور غير متطابقة';
        }
    }
    
    // Check if email already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
        if ($stmt === false) {
            die("Database error: " . $conn->error);
        }

        $stmt->bind_param("si", $email, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            die("Database error: " . $conn->error);
        }

        if ($result->num_rows > 0) {
            $errors[] = 'البريد الإلكتروني مستخدم بالفعل';
        }
    }
    
    // Update profile if no errors
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE user_id = ?");
            if ($stmt === false) {
                die("Database error: " . $conn->error);
            }

            $stmt->bind_param("ssi", $name, $email, $_SESSION['user_id']);
            $stmt->execute();

            // Update password if provided
            if (!empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                if ($stmt === false) {
                    die("Database error: " . $conn->error);
                }

                $stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
                $stmt->execute();
            }
            
            $_SESSION['success_message'] = 'تم تحديث الملف الشخصي بنجاح';
            header('Location: edit_profile.php');
            exit();
        } catch (Exception $e) {
            $errors[] = 'خطأ في تحديث الملف الشخصي: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>تعديل الملف الشخصي - Swift Jobs Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
  body {
    background: linear-gradient(135deg, #f0f4ff, #e0e7ff);
    font-family: 'Tajawal', sans-serif;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .container {
    max-width: 800px;
    margin-top: 2rem;
  }

  .card {
    background: #ffffff;
    border: none;
    border-radius: 20px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    overflow: hidden;
    transition: transform 0.3s ease;
  }

  .card:hover {
    transform: translateY(-5px);
  }

  .card-header {
    background: linear-gradient(90deg, #3b82f6, #1e40af);
    color: #ffffff;
    padding: 1.5rem;
    border-bottom: none;
    text-align: center;
  }

  .card-header h3 {
    margin: 0;
    font-weight: 700;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
  }

  .card-body {
    padding: 2rem;
  }

  /* تنسيق التنبيهات */
  .alert-success {
    background: linear-gradient(90deg, #34c759, #28a745);
    color: #ffffff;
    border: none;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
  }

  .alert-danger {
    background: linear-gradient(90deg, #ff4d4f, #dc3545);
    color: #ffffff;
    border: none;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
  }

  .alert ul li {
    margin-bottom: 0.5rem;
  }

  /* تنسيق الفورم */
  .form-label {
    color: #2d3748;
    font-weight: 600;
    margin-bottom: 0.5rem;
  }

  .form-control {
    border-radius: 10px;
    border: 1px solid #d1d9f0;
    padding: 0.75rem;
    transition: all 0.3s ease;
    background: #f8f9fa;
    color: #2d3748;
  }

  .form-control:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.3);
    background: #ffffff;
  }

  .form-control::placeholder {
    color: #a0aec0;
  }

  /* تنسيق الأزرار */
  .btn-primary {
    background: linear-gradient(90deg, #3b82f6, #1e40af);
    border: none;
    border-radius: 10px;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
  }

  .btn-primary:hover {
    background: linear-gradient(90deg, #1e40af, #1e3a8a);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
  }

  .btn-secondary {
    background: linear-gradient(90deg, #a0aec0, #6b7280);
    border: none;
    border-radius: 10px;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    color: #ffffff;
    transition: all 0.3s ease;
  }

  .btn-secondary:hover {
    background: linear-gradient(90deg, #6b7280, #4b5563);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(107, 114, 128, 0.4);
  }

  /* التصميم المتجاوب */
  @media (max-width: 768px) {
    .container {
      margin-top: 1rem;
      padding: 0 1rem;
    }

    .card {
      border-radius: 15px;
    }

    .card-header {
      padding: 1rem;
    }

    .card-body {
      padding: 1.5rem;
    }

    .form-control {
      padding: 0.5rem;
    }

    .btn-primary,
    .btn-secondary {
      padding: 0.5rem 1rem;
      font-size: 0.9rem;
    }
  }
  </style>
</head>

<body>
  <?php include 'includes/admin_header.php'; ?>

  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <h3 class="mb-0">تعديل الملف الشخصي</h3>
          </div>
          <div class="card-body">
            <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
              <?php 
                                echo $_SESSION['success_message'];
                                unset($_SESSION['success_message']);
                                ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
              <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <?php endif; ?>

            <form method="POST" action="">
              <div class="mb-3">
                <label for="name" class="form-label">الاسم الكامل</label>
                <input type="text" class="form-control" id="name" name="name"
                  value="<?php echo htmlspecialchars($admin_data['name'] ?? ''); ?>" required>
              </div>

              <div class="mb-3">
                <label for="email" class="form-label">البريد الإلكتروني</label>
                <input type="email" class="form-control" id="email" name="email"
                  value="<?php echo htmlspecialchars($admin_data['email'] ?? ''); ?>" required>
              </div>

              <div class="mb-3">
                <label for="current_password" class="form-label">كلمة المرور الحالية</label>
                <input type="password" class="form-control" id="current_password" name="current_password"
                  placeholder="أدخل كلمة المرور الحالية لتأكيد التغييرات">
              </div>

              <div class="mb-3">
                <label for="new_password" class="form-label">كلمة المرور الجديدة</label>
                <input type="password" class="form-control" id="new_password" name="new_password"
                  placeholder="اتركها فارغة للاحتفاظ بكلمة المرور الحالية">
              </div>

              <div class="mb-3">
                <label for="confirm_password" class="form-label">تأكيد كلمة المرور الجديدة</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
              </div>

              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">تحديث الملف الشخصي</button>
                <a href="admin_dashboard.php" class="btn btn-secondary">إلغاء</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>