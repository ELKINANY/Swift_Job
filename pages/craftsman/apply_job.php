<?php
require '../db_connection.php';
include "../navBar.php";

// Define all possible questions for craftsmen
$allQuestions = [
    'q1' => 'ما هي خبرتك في هذا النوع من المشاريع؟',
    'q2' => 'ما هي المواد التي تفضل العمل بها ولماذا؟',
    'q3' => 'كيف تتعامل مع التغييرات غير المتوقعة في متطلبات المشروع؟',
    'q4' => 'ما هو أكبر مشروع قمت به من قبل؟',
    'q5' => 'كيف تضمن جودة عملك؟',
    'q6' => 'ما هي الأدوات والمعدات التي تمتلكها للعمل؟',
    'q7' => 'ما هو الوقت المتوقع لإنجاز هذا المشروع؟',
    'q8' => 'هل لديك فريق عمل أم تعمل بمفردك؟',
    'q9' => 'ما هي ضماناتك على العمل المقدم؟',
    'q10' => 'هل لديك أمثلة لأعمال مشابهة قمت بها من قبل؟'
];

// Select 5 random questions
$randomKeys = array_rand($allQuestions, 5);
$selectedQuestions = array_intersect_key($allQuestions, array_flip($randomKeys));

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$userId = $_SESSION['user_id'];

// Check for project_id
if (!isset($_GET['project_id'])) {
    header("Location: findProjects.php");
    exit();
}
$projectId = $_GET['project_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // معالجة تحميل الصور
    $uploadedImages = [];
    $uploadDir = '../uploads/project_bids/';
    
    // Create directory if not exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Process each uploaded image
    foreach ($_FILES['work_images']['tmp_name'] as $key => $tmpName) {
        if ($_FILES['work_images']['error'][$key] === UPLOAD_ERR_OK) {
            $fileName = basename($_FILES['work_images']['name'][$key]);
            $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $newFileName = uniqid() . '.' . $fileType;
            $targetFile = $uploadDir . $newFileName;

            // Check file type
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($fileType, $allowedTypes)) {
                echo '<script>alert("عذراً، يُسمح فقط بملفات الصور (JPG, JPEG, PNG, GIF).");</script>';
                continue;
            }

            // Check file size (max 5MB)
            if ($_FILES['work_images']['size'][$key] > 5000000) {
                echo '<script>alert("عذراً، حجم الصورة كبير جداً (الحد الأقصى 5MB).");</script>';
                continue;
            }

            // Try to upload file
            if (move_uploaded_file($tmpName, $targetFile)) {
                $uploadedImages[] = $newFileName;
            }
        }
    }

    if (count($uploadedImages) > 0) {
        $questionsAnswers = serialize($_POST['questions']);
        $imagesSerialized = serialize($uploadedImages);

        // Sanitize input data
        $proposal = htmlspecialchars($_POST['proposal']);
        $bidAmount = filter_var($_POST['bid_amount'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $timeline = htmlspecialchars($_POST['timeline']);
        $phone = htmlspecialchars($_POST['phone']);

        if ($bidAmount === false) {
            echo '<script>alert("قيمة العرض يجب أن تكون رقماً صالحاً.");</script>';
        } else {
            $query = "INSERT INTO project_bids (
                project_id, 
                craftsman_id, 
                proposal, 
                timeline,
                bid_amount, 
                phone, 
                work_images, 
                additional_questions,
                bid_date
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ? , NOW())";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param(
                "iissssss",
                $projectId,
                $userId,
                $proposal,
                $bidAmount,
                $timeline,
                $phone,
                $imagesSerialized,
                $questionsAnswers
            );

            if ($stmt->execute()) {
                echo '<script>
                    alert("تم تقديم عرضك بنجاح!");
                    window.location.href = "project_details.php?project_id='.$projectId.'";
                </script>';
            } else {
                echo '<script>alert("حدث خطأ أثناء حفظ بيانات العرض: ' . $stmt->error . '");</script>';
            }
            $stmt->close();
        }
    } else {
        echo '<script>alert("عذراً، يجب تحميل صورة واحدة على الأقل من أعمالك السابقة.");</script>';
    }
    
    $conn->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>تقديم عرض للمشروع</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
  :root {
    --primary-color: #3498db;
    --secondary-color: #2c3e50;
    --accent-color: #e74c3c;
    --light-bg: #f8f9fa;
    --dark-text: #2c3e50;
    --light-text: #7f8c8d;
  }

  body {
    font-family: 'Tajawal', sans-serif;
    background-color: var(--light-bg);
    color: var(--dark-text);
  }

  .form-container {
    max-width: 800px;
    margin: 40px auto;
    background-color: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    border: 1px solid #e0e0e0;
  }

  h2 {
    color: var(--secondary-color);
    text-align: center;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--primary-color);
  }

  .form-label {
    font-weight: 600;
    color: var(--secondary-color);
    margin-bottom: 8px;
  }

  .form-control,
  .form-select,
  .form-file {
    padding: 12px 15px;
    border-radius: 10px;
    border: 1px solid #ddd;
    margin-bottom: 20px;
    transition: all 0.3s;
  }

  .form-control:focus,
  .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
  }

  textarea.form-control {
    min-height: 120px;
    resize: vertical;
  }

  .btn-submit {
    background-color: var(--primary-color);
    color: white;
    border: none;
    padding: 12px 30px;
    font-weight: 600;
    border-radius: 50px;
    transition: all 0.3s;
    width: 100%;
    margin-top: 20px;
  }

  .btn-submit:hover {
    background-color: #2980b9;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
  }

  .image-preview-container {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 20px;
  }

  .image-preview {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 8px;
    border: 2px dashed #ddd;
    display: none;
  }

  .file-input-label {
    display: block;
    padding: 30px;
    border: 2px dashed #ccc;
    border-radius: 10px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    margin-bottom: 20px;
  }

  .file-input-label:hover {
    border-color: var(--primary-color);
    background-color: rgba(52, 152, 219, 0.05);
  }

  .file-input-label i {
    font-size: 2rem;
    color: var(--primary-color);
    margin-bottom: 10px;
  }

  @media (max-width: 768px) {
    .form-container {
      padding: 20px;
      margin: 20px auto;
    }

    .form-control,
    .form-select {
      padding: 10px 12px;
    }
  }
  </style>
</head>

<body>
  <div class="container py-4">
    <div class="form-container">
      <h2><i class="fas fa-handshake me-2"></i> تقديم عرض للمشروع</h2>

      <form method="post" enctype="multipart/form-data">
        <!-- عرض السعر والجدول الزمني -->
        <div class="row">
          <div class="col-md-6">
            <label for="bid_amount" class="form-label">قيمة العرض (بالعملة المحلية)</label>
            <input type="number" step="0.01" class="form-control" id="bid_amount" name="bid_amount" required>
          </div>
          <div class="col-md-6">
            <label for="timeline" class="form-label">المدة المتوقعة لإنجاز المشروع (بالأيام)</label>
            <input type="number" class="form-control" id="timeline" name="timeline" required>
          </div>
        </div>

        <!-- تحميل صور الأعمال السابقة -->
        <label class="form-label">صور لأعمال سابقة (لإثبات خبرتك)</label>
        <label for="work_images" class="file-input-label">
          <i class="fas fa-images"></i>
          <div>انقر لرفع صور أعمالك السابقة</div>
          <small class="text-muted">(يمكن رفع أكثر من صورة)</small>
          <input type="file" id="work_images" name="work_images[]" multiple accept="image/*" style="display: none;"
            required>
        </label>

        <div class="image-preview-container" id="imagePreviewContainer">
          <!-- سيتم عرض معاينة الصور هنا -->
        </div>

        <!-- اقتراح المشروع -->
        <label for="proposal" class="form-label">مقترحك لتنفيذ المشروع</label>
        <textarea class="form-control" id="proposal" name="proposal" required
          placeholder="صف كيف ستقوم بتنفيذ هذا المشروع، المواد التي ستستخدمها، وأي تفاصيل أخرى مهمة..."></textarea>

        <!-- معلومات التواصل -->
        <label for="phone" class="form-label">رقم الهاتف (للتواصل)</label>
        <input type="text" class="form-control" id="phone" name="phone" required
          placeholder="مثال: 009665... أو +9665...">

        <!-- الأسئلة الخاصة بالحرفيين -->
        <h5 class="mt-4 mb-3" style="color: var(--secondary-color);">
          <i class="fas fa-question-circle me-2"></i>الأسئلة الخاصة بالحرفيين
        </h5>

        <?php foreach ($selectedQuestions as $key => $question): ?>
        <label for="<?= $key ?>" class="form-label"><?= $question ?></label>
        <textarea class="form-control" id="<?= $key ?>" name="questions[<?= $key ?>]" required></textarea>
        <?php endforeach; ?>

        <button type="submit" class="btn btn-submit">
          <i class="fas fa-paper-plane me-2"></i> تقديم العرض
        </button>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  // عرض معاينة الصور قبل الرفع
  document.getElementById('work_images').addEventListener('change', function(event) {
    const previewContainer = document.getElementById('imagePreviewContainer');
    previewContainer.innerHTML = ''; // مسح الصور السابقة

    const files = event.target.files;
    if (files) {
      for (let i = 0; i < files.length; i++) {
        const reader = new FileReader();

        reader.onload = function(e) {
          const img = document.createElement('img');
          img.src = e.target.result;
          img.classList.add('image-preview');
          img.style.display = 'block';
          previewContainer.appendChild(img);
        }

        reader.readAsDataURL(files[i]);
      }
    }
  });
  </script>
</body>

</html>