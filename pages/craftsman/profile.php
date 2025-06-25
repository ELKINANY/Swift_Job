<?php
require '../db_connection.php';
include "../navBar.php";

// تفعيل عرض الأخطاء
error_reporting(E_ALL);
ini_set('display_errors', 1);

// التحقق من أن المستخدم مسجل دخول وهو حرفي
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'craftsman') {
    header("Location: login.php");
    exit();
}

$craftsman_id = $_SESSION['user_id'];

// معالجة حذف العناصر (احتياطي، لكن الحذف يتم عبر AJAX)
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $item_id = $_GET['id'];
    
    // جلب معلومات الملف قبل الحذف
    $stmt = $conn->prepare("SELECT file_name FROM craftsman_portfolio WHERE portfolio_id = ? AND craftsman_id = ?");
    $stmt->bind_param("ii", $item_id, $craftsman_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $item = $result->fetch_assoc();
        $file_path = realpath('../Uploads/portfolio/' . $item['file_name']);
        
        // حذف الملف من السيرفر
        if ($file_path && file_exists($file_path)) {
            unlink($file_path);
        }
        
        // حذف السجل من قاعدة البيانات
        $stmt = $conn->prepare("DELETE FROM craftsman_portfolio WHERE portfolio_id = ? AND craftsman_id = ?");
        $stmt->bind_param("ii", $item_id, $craftsman_id);
        $stmt->execute();
        
        header("Location: profile.php?deleted=1");
        exit();
    } else {
        header("Location: profile.php?error=not_found");
        exit();
    }
}

// معالجة رفع الملفات
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['media_files'])) {
    $uploadDir = '../Uploads/portfolio/';
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $uploadedFiles = [];
    $allowedTypes = [
        'image/jpeg', 'image/png', 'image/gif',
        'video/mp4', 'video/webm', 'video/ogg'
    ];

    foreach ($_FILES['media_files']['tmp_name'] as $key => $tmpName) {
        if ($_FILES['media_files']['error'][$key] !== UPLOAD_ERR_OK) {
            echo '<script>alert("خطأ في رفع الملف: ' . $_FILES['media_files']['error'][$key] . '");</script>';
            continue;
        }

        $fileType = $_FILES['media_files']['type'][$key];
        
        if (in_array($fileType, $allowedTypes)) {
            $filename = basename($_FILES['media_files']['name'][$key]);
            $fileExt = pathinfo($filename, PATHINFO_EXTENSION);
            $newFileName = md5(uniqid() . time()) . '.' . $fileExt;
            $targetFile = $uploadDir . $newFileName;

            // التحقق من حجم الملف (100MB كحد أقصى)
            if ($_FILES['media_files']['size'][$key] > 100000000) {
                echo '<script>alert("عذراً، حجم الملف كبير جداً (الحد الأقصى 100MB).");</script>';
                continue;
            }

            if (move_uploaded_file($tmpName, $targetFile)) {
                $mediaType = strpos($fileType, 'image') !== false ? 'image' : 'video';
                
                $stmt = $conn->prepare("INSERT INTO craftsman_portfolio 
                    (craftsman_id, file_name, media_type, description) 
                    VALUES (?, ?, ?, ?)");
                $description = isset($_POST['description'][$key]) ? $_POST['description'][$key] : '';
                $stmt->bind_param("isss", 
                    $craftsman_id,
                    $newFileName,
                    $mediaType,
                    $description
                );
                
                if ($stmt->execute()) {
                    $uploadedFiles[] = $newFileName;
                } else {
                    // إذا فشل الإدراج في قاعدة البيانات، احذف الملف
                    unlink($targetFile);
                }
                $stmt->close();
            }
        }
    }

    // إعادة توجيه لتجنب إعادة إرسال النموذج
    if (!empty($uploadedFiles)) {
        header("Location: profile.php?uploaded=1");
        exit();
    }
}

// جلب معرض أعمال الحرفي
$query = "SELECT p.*, u.name AS craftsman_name, u.specialization 
          FROM craftsman_portfolio p
          JOIN users u ON p.craftsman_id = u.user_id
          WHERE p.craftsman_id = ?
          ORDER BY p.uploaded_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $craftsman_id);
$stmt->execute();
$portfolioResult = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>معرض أعمالي</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.1/css/lightgallery-bundle.min.css">
  <style>
  :root {
    --primary-color: #3498db;
    --secondary-color: #2c3e50;
    --accent-color: #e74c3c;
    --dark-bg: #1a1a2e;
    --light-bg: #f8f9fa;
    --gradient-bg: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
  }

  body {
    font-family: 'Tajawal', sans-serif;
    background-color: #f5f7fa;
    color: #333;
  }

  .portfolio-header {
    background: var(--gradient-bg);
    color: white;
    padding: 5rem 0;
    margin-bottom: 3rem;
    position: relative;
    overflow: hidden;
  }

  .portfolio-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path fill="rgba(255,255,255,0.05)" d="M0,0 L100,0 L100,100 L0,100 Z" /></svg>');
    background-size: cover;
    opacity: 0.2;
  }

  .portfolio-title {
    font-weight: 800;
    font-size: 3rem;
    margin-bottom: 1rem;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
  }

  .portfolio-subtitle {
    font-size: 1.2rem;
    opacity: 0.9;
  }

  .add-work-btn {
    background: var(--primary-color);
    color: white;
    border: none;
    padding: 12px 30px;
    font-weight: 600;
    border-radius: 50px;
    transition: all 0.3s;
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
    position: relative;
    overflow: hidden;
  }

  .add-work-btn:hover {
    background: #2980b9;
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(52, 152, 219, 0.4);
  }

  .add-work-btn i {
    margin-left: 8px;
  }

  .portfolio-container {
    padding: 0 15px;
  }

  .gallery-item {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    margin-bottom: 25px;
    background: white;
    border: none;
  }

  .gallery-item:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
  }

  .gallery-media {
    width: 100%;
    height: 250px;
    object-fit: cover;
    display: block;
  }

  .video-container {
    position: relative;
    height: 250px;
    overflow: hidden;
  }

  .video-container video {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .video-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.3);
    color: white;
    font-size: 3rem;
    opacity: 0;
    transition: opacity 0.3s;
  }

  .video-container:hover .video-overlay {
    opacity: 1;
  }

  .gallery-caption {
    padding: 20px;
    background: white;
  }

  .gallery-caption h5 {
    font-weight: 700;
    color: var(--secondary-color);
    margin-bottom: 10px;
  }

  .gallery-caption p {
    color: #666;
    margin-bottom: 0;
  }

  .upload-modal .modal-content {
    border-radius: 15px;
    overflow: hidden;
    border: none;
  }

  .upload-modal .modal-header {
    background: var(--gradient-bg);
    color: white;
    border-bottom: none;
  }

  .upload-modal .modal-body {
    padding: 30px;
  }

  .file-upload-area {
    border: 2px dashed #ddd;
    border-radius: 12px;
    padding: 40px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    margin-bottom: 20px;
    background: rgba(248, 249, 250, 0.5);
  }

  .file-upload-area:hover {
    border-color: var(--primary-color);
    background: rgba(52, 152, 219, 0.05);
  }

  .file-upload-area i {
    font-size: 3rem;
    color: var(--primary-color);
    margin-bottom: 15px;
  }

  .file-upload-area p {
    margin-bottom: 0;
    color: #666;
  }

  .preview-container {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-top: 20px;
  }

  .preview-item {
    width: 120px;
    height: 120px;
    border-radius: 8px;
    overflow: hidden;
    position: relative;
  }

  .preview-item img,
  .preview-item video {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .remove-preview {
    position: absolute;
    top: 5px;
    left: 5px;
    background: rgba(231, 76, 60, 0.9);
    color: white;
    width: 25px;
    height: 25px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 12px;
  }

  .description-field {
    margin-top: 10px;
    width: 100%;
  }

  .empty-portfolio {
    text-align: center;
    padding: 5rem 0;
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
  }

  .empty-portfolio i {
    font-size: 4rem;
    color: #ddd;
    margin-bottom: 20px;
  }

  .empty-portfolio h4 {
    color: #666;
    margin-bottom: 15px;
  }

  .alert-message {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    min-width: 300px;
  }

  @media (max-width: 768px) {
    .portfolio-title {
      font-size: 2rem;
    }

    .gallery-media,
    .video-container {
      height: 200px;
    }

    .upload-modal .modal-body {
      padding: 20px;
    }

    .file-upload-area {
      padding: 30px 20px;
    }
  }
  </style>
</head>

<body>
  <!-- رسائل التنبيه -->
  <?php if (isset($_GET['uploaded'])): ?>
  <div class="alert alert-success alert-message alert-dismissible fade show" role="alert">
    تم رفع العمل بنجاح
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  <?php elseif (isset($_GET['deleted'])): ?>
  <div class="alert alert-success alert-message alert-dismissible fade show" role="alert">
    تم حذف العمل بنجاح
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  <?php elseif (isset($_GET['updated'])): ?>
  <div class="alert alert-success alert-message alert-dismissible fade show" role="alert">
    تم تحديث العمل بنجاح
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  <?php elseif (isset($_GET['error'])): ?>
  <div class="alert alert-danger alert-message alert-dismissible fade show" role="alert">
    <?php
    $errors = [
        'not_found' => 'العمل غير موجود',
        'update_failed' => 'فشل تحديث العمل',
        'not_owner' => 'ليس لديك صلاحية تعديل هذا العمل'
    ];
    echo $errors[$_GET['error']] ?? 'حدث خطأ غير متوقع';
    ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  <?php endif; ?>

  <!-- رأس الصفحة -->
  <div class="portfolio-header text-center">
    <div class="container position-relative">
      <h1 class="portfolio-title">معرض أعمالي</h1>
      <p class="portfolio-subtitle">هذا المعرض يعرض أفضل أعمالي وإنجازاتي المهنية</p>

      <!-- زر إضافة عمل جديد -->
      <button type="button" class="add-work-btn" data-bs-toggle="modal" data-bs-target="#uploadModal">
        <i class="fas fa-plus"></i> إضافة عمل جديد
      </button>
    </div>
  </div>

  <!-- معرض الأعمال -->
  <div class="container portfolio-container">
    <?php if ($portfolioResult->num_rows > 0): ?>
    <div class="row gallery">
      <?php while ($item = $portfolioResult->fetch_assoc()): ?>
      <div class="col-lg-4 col-md-6 mb-4">
        <div class="gallery-item">
          <?php if ($item['media_type'] == 'image'): ?>
          <a href="../Uploads/portfolio/<?= $item['file_name'] ?>" class="gallery-img">
            <img src="../Uploads/portfolio/<?= $item['file_name'] ?>" alt="عمل الحرفي" class="gallery-media">
          </a>
          <?php else: ?>
          <div class="video-container">
            <video controls>
              <source src="../Uploads/portfolio/<?= $item['file_name'] ?>" type="video/mp4">
              متصفحك لا يدعم تشغيل الفيديوهات
            </video>
            <div class="video-overlay">
              <i class="fas fa-play"></i>
            </div>
          </div>
          <?php endif; ?>

          <div class="gallery-caption">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <h5>عمل <?= $item['media_type'] == 'image' ? 'صوري' : 'فيديو' ?></h5>
                <?php if (!empty($item['description'])): ?>
                <p><?= htmlspecialchars($item['description']) ?></p>
                <?php endif; ?>
              </div>
              <div class="btn-group">
                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editModal"
                  data-id="<?= $item['portfolio_id'] ?>"
                  data-description="<?= htmlspecialchars($item['description']) ?>">
                  <i class="fas fa-edit"></i>
                </button>
                <a href="#" class="btn btn-sm btn-outline-danger delete-btn" data-id="<?= $item['portfolio_id'] ?>">
                  <i class="fas fa-trash"></i>
                </a>
              </div>
            </div>
            <small class="text-muted"><?= date('Y-m-d', strtotime($item['uploaded_at'])) ?></small>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="empty-portfolio">
      <i class="fas fa-images"></i>
      <h4>لا توجد أعمال في معرضك بعد</h4>
      <p>ابدأ بإضافة أعمالك السابقة لعرضها للعملاء</p>
      <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#uploadModal">
        <i class="fas fa-plus me-2"></i> إضافة أول عمل
      </button>
    </div>
    <?php endif; ?>
  </div>

  <!-- مودال إضافة عمل جديد -->
  <div class="modal fade upload-modal" id="uploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">إضافة عمل جديد للمعرض</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="post" enctype="multipart/form-data" id="uploadForm">
          <div class="modal-body">
            <!-- منطقة رفع الملفات -->
            <label for="mediaFiles" class="file-upload-area">
              <i class="fas fa-cloud-upload-alt"></i>
              <h5>اسحب وأسقط الملفات هنا أو انقر للاختيار</h5>
              <p>يمكنك رفع الصور (JPG, PNG, GIF) أو الفيديوهات (MP4, WebM) - الحد الأقصى 100MB لكل ملف</p>
              <input type="file" id="mediaFiles" name="media_files[]" multiple accept="image/*,video/*"
                style="display: none;">
            </label>

            <!-- معاينة الملفات المختارة -->
            <div class="preview-container" id="previewContainer"></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
            <button type="submit" class="btn btn-primary">حفظ العمل</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- مودال التعديل -->
  <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">تعديل العمل</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="post" action="update_portfolio_item.php">
          <div class="modal-body">
            <input type="hidden" name="item_id" id="editItemId">
            <div class="mb-3">
              <label for="editDescription" class="form-label">وصف العمل</label>
              <textarea class="form-control" id="editDescription" name="description" rows="5"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
            <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.1/lightgallery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.1/plugins/video/lg-video.min.js"></script>
  <script>
  // معاينة الملفات قبل الرفع
  document.getElementById('mediaFiles').addEventListener('change', function(e) {
    const previewContainer = document.getElementById('previewContainer');
    previewContainer.innerHTML = '';

    const files = e.target.files;
    if (files.length > 0) {
      for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const fileType = file.type.split('/')[0];
        const previewItem = document.createElement('div');
        previewItem.className = 'preview-item';

        if (fileType === 'image') {
          const reader = new FileReader();
          reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            previewItem.appendChild(img);
            addRemoveButton(previewItem, i, files);
            addDescriptionField(previewItem, i);
          };
          reader.readAsDataURL(file);
        } else if (fileType === 'video') {
          const videoContainer = document.createElement('div');
          videoContainer.style.width = '100%';
          videoContainer.style.height = '100%';
          videoContainer.style.position = 'relative';

          const video = document.createElement('video');
          video.src = URL.createObjectURL(file);
          video.muted = true;
          video.loop = true;
          video.style.width = '100%';
          video.style.height = '100%';
          video.style.objectFit = 'cover';

          videoContainer.appendChild(video);
          previewItem.appendChild(videoContainer);
          addRemoveButton(previewItem, i, files);
          addDescriptionField(previewItem, i);
        }

        previewContainer.appendChild(previewItem);
      }
    }
  });

  function addRemoveButton(previewItem, index, files) {
    const removeBtn = document.createElement('div');
    removeBtn.className = 'remove-preview';
    removeBtn.innerHTML = '<i class="fas fa-times"></i>';
    removeBtn.onclick = function() {
      previewItem.remove();
      const dataTransfer = new DataTransfer();
      for (let j = 0; j < files.length; j++) {
        if (j !== index) dataTransfer.items.add(files[j]);
      }
      document.getElementById('mediaFiles').files = dataTransfer.files;
    };
    previewItem.appendChild(removeBtn);
  }

  function addDescriptionField(previewItem, index) {
    const descriptionContainer = document.createElement('div');
    descriptionContainer.className = 'description-field';
    const textarea = document.createElement('textarea');
    textarea.name = `description[${index}]`;
    textarea.className = 'form-control';
    textarea.placeholder = 'أدخل وصف العمل...';
    textarea.rows = 2;
    descriptionContainer.appendChild(textarea);
    previewItem.appendChild(descriptionContainer);
  }

  // تفعيل معرض الصور والفيديوهات
  document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('.gallery')) {
      lightGallery(document.querySelector('.gallery'), {
        selector: '.gallery-img, .gallery-video',
        download: false,
        videojs: true
      });
    }

    // تفعيل مودال التعديل
    const editModal = document.getElementById('editModal');
    if (editModal) {
      editModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const itemId = button.getAttribute('data-id');
        const description = button.getAttribute('data-description');

        document.getElementById('editItemId').value = itemId;
        document.getElementById('editDescription').value = description;
      });
    }

    // إدارة الحذف باستخدام AJAX
    document.querySelectorAll('.delete-btn').forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        const itemId = this.getAttribute('data-id');

        if (confirm('هل أنت متأكد من حذف هذا العمل؟')) {
          fetch('delete_portfolio_item.php?id=' + itemId, {
              method: 'DELETE',
              headers: {
                'Content-Type': 'application/json',
              }
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                window.location.reload();
              } else {
                alert('حدث خطأ أثناء الحذف: ' + data.message);
              }
            })
            .catch(error => {
              console.error('Error:', error);
              alert('حدث خطأ في الاتصال');
            });
        }
      });
    });

    // إخفاء رسائل التنبيه بعد 5 ثواني
    setTimeout(() => {
      const alerts = document.querySelectorAll('.alert-message');
      alerts.forEach(alert => {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
      });
    }, 5000);
  });

  // سحب وإسقاط الملفات
  const uploadArea = document.querySelector('.file-upload-area');
  uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.style.borderColor = '#3498db';
    uploadArea.style.backgroundColor = 'rgba(52, 152, 219, 0.1)';
  });

  uploadArea.addEventListener('dragleave', () => {
    uploadArea.style.borderColor = '#ddd';
    uploadArea.style.backgroundColor = 'rgba(248, 249, 250, 0.5)';
  });

  uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.style.borderColor = '#ddd';
    uploadArea.style.backgroundColor = 'rgba(248, 249, 250, 0.5)';

    if (e.dataTransfer.files.length) {
      document.getElementById('mediaFiles').files = e.dataTransfer.files;
      const event = new Event('change');
      document.getElementById('mediaFiles').dispatchEvent(event);
    }
  });
  </script>
</body>

</html>