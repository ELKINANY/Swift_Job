<?php include "./navBar.php";?>

<!-- Hero Section -->
<main class="hero d-flex align-items-center">
  <div class="container">
    <div class="row justify-content-center">
      <?php if (!isset($_SESSION['role'])): ?>
      <div class="col-md-4 mb-4 mb-md-0">
        <div class="card registration-card text-center p-4">
          <div class="card-icon mx-auto">
            <img src="<?= BASE_URL ?>تسجيل الشركات.png" alt="تسجيل منشأة">
          </div>
          <h2 class="h4 mt-3">سجل كمنشأة</h2>
          <p class="text-muted">سجل منشأتك وابدأ في توظيف الكفاءات</p>
          <a href="./companiesSignup.php" class="btn btn-custom-primary w-100">تسجيل منشأة</a>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card registration-card text-center p-4">
          <div class="card-icon mx-auto">
            <img src="<?= BASE_URL ?>تسجيل فرد.webp" alt="تسجيل فرد">
          </div>
          <h2 class="h4 mt-3">سجل كفرد</h2>
          <p class="text-muted">سجل كباحث عن عمل واكتشف الفرص المتاحة</p>
          <a href="./userSignup.php" class="btn btn-custom-primary w-100">تسجيل فرد</a>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </div>
</main>

<!-- Partners Section -->
<section class="py-5">
  <div class="container">
    <h2 class="text-center mb-5">المنشآت</h2>
    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-4 justify-content-center align-items-center">
      <div class="col text-center">
        <img src="companies/مؤسسة1.jpg" alt="Partner 1" class="partner-logo">
      </div>
      <div class="col text-center">
        <img src="companies/مؤسسة2.jpg" alt="Partner 2" class="partner-logo">
      </div>
      <div class="col text-center">
        <img src="companies/مؤسسة3.jpg" alt="Partner 3" class="partner-logo">
      </div>
      <div class="col text-center">
        <img src="companies/مؤسسة4.jpg" alt="Partner 4" class="partner-logo">
      </div>
      <div class="col text-center">
        <img src="companies/مؤسسة5.jpg" alt="Partner 5" class="partner-logo">
      </div>
      <div class="col text-center">
        <img src="companies/مؤسسة6.jpg" alt="Partner 6" class="partner-logo">
      </div>
    </div>
  </div>
</section>
<section class="jobs-section">
  <h2 class="section-title text-center mb-5">وظائف متوفرة</h2>
  <div class="jobs-grid">
    <!-- Job Card 1 -->
    <div class="job-card">
      <div class="job-title">
        <div class="job-title-icon">
          <i class="fa-solid fa-circle-user fa-xl"></i>
        </div>
        <span class="job-title-text">محاسب عام</span>
      </div>
      <div class="company-name">
        <span>شركة الجميل العالمية المحدودة</span>
      </div>
      <button class="apply-button">تقدم للوظيفة</button>
    </div>

    <!-- Job Card 2 -->
    <div class="job-card">
      <div class="job-title">
        <div class="job-title-icon">
          <i class="fa-solid fa-circle-user fa-xl"></i>
        </div>
        <span class="job-title-text">مصمم جرافيكي</span>
      </div>
      <div class="company-name">
        <span>شركة الجميل العالمية المحدودة</span>
      </div>
      <button class="apply-button">تقدم للوظيفة</button>
    </div>

    <!-- Job Card 3 -->
    <div class="job-card">
      <div class="job-title">
        <div class="job-title-icon">
          <i class="fa-solid fa-circle-user fa-xl"></i>
        </div>
        <span class="job-title-text">مساعد موارد بشرية</span>
      </div>
      <div class="company-name">
        <span>Ibn Hayan Trading Company</span>
      </div>
      <button class="apply-button">تقدم للوظيفة</button>
    </div>

    <!-- Job Card 4 -->
    <div class="job-card">
      <div class="job-title">
        <div class="job-title-icon">
          <i class="fa-solid fa-circle-user fa-xl"></i>
        </div>
        <span class="job-title-text">أخصائي إداري</span>
      </div>
      <div class="company-name">
        <span>شركة نايلس المحدودة</span>
      </div>
      <button class="apply-button">تقدم للوظيفة</button>
    </div>
  </div>
</section>
<?php if (!isset($_SESSION['role'])): ?>
<section class="about">
  <div class="about-content">
    <div class="about-text">
      <h1 class="about-title">هل تبحث عن وظيفة؟</h1>
      <p class="about-description">
        وظفني رح يساعدك بالعثور على عشرات الوظائف المناسبة لك عن طريق محركات بحث
        تقدر تحدد منها الراتب وموقع الوظيفة وساعات العمل!!
      </p>
      <a href="./userSignup.php" class="about-button">سجل الآن</a>
    </div>
    <div class="about-image">
      <img src="<?= BASE_URL ?>logo.jpg" alt="البحث عن وظيفة" width="500" height="400">
    </div>
  </div>
</section>
<?php endif; ?>
<!-- Footer -->
<?php
  include "./footer.php" 
?>