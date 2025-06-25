<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>أخبار سوق العمل - قريباً | Swift Jobs</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
  :root {
    --primary: #3498db;
    --secondary: #2c3e50;
    --accent: #e74c3c;
    --light: #f8f9fa;
    --dark: #212529;
  }

  body {
    font-family: 'Tajawal', sans-serif;
    background-color: var(--light);
    color: var(--dark);
    min-height: 100vh;
    overflow-x: hidden;
  }

  .news-hero {
    background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)),
      url('https://images.unsplash.com/photo-1450101499163-c8848c66ca85?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
    background-size: cover;
    background-position: center;
    height: 60vh;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: white;
    position: relative;
    margin-bottom: 5rem;
  }

  .news-hero::after {
    content: '';
    position: absolute;
    bottom: -50px;
    left: 0;
    right: 0;
    height: 100px;
    background: var(--light);
    clip-path: polygon(0 0, 100% 70%, 100% 100%, 0% 100%);
  }

  .hero-content {
    max-width: 800px;
    padding: 2rem;
    z-index: 1;
  }

  .coming-soon-badge {
    background: var(--accent);
    color: white;
    padding: 0.5rem 1.5rem;
    border-radius: 50px;
    font-weight: bold;
    display: inline-block;
    margin-bottom: 1.5rem;
    animation: pulse 2s infinite;
  }

  @keyframes pulse {
    0% {
      transform: scale(1);
    }

    50% {
      transform: scale(1.05);
    }

    100% {
      transform: scale(1);
    }
  }

  .news-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    padding: 0 2rem;
    max-width: 1200px;
    margin: 0 auto 5rem;
  }

  .news-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: all 0.3s;
    opacity: 0.5;
    filter: blur(2px);
  }

  .news-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
  }

  .news-img {
    height: 200px;
    background: #ddd;
    position: relative;
    overflow: hidden;
  }

  .news-img::after {
    content: 'قريباً';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 5px;
    font-weight: bold;
  }

  .news-content {
    padding: 1.5rem;
  }

  .news-category {
    display: inline-block;
    background: var(--primary);
    color: white;
    padding: 0.25rem 1rem;
    border-radius: 50px;
    font-size: 0.8rem;
    margin-bottom: 0.5rem;
  }

  .news-title {
    font-weight: 700;
    margin-bottom: 1rem;
    font-size: 1.2rem;
  }

  .news-meta {
    display: flex;
    justify-content: space-between;
    color: #6c757d;
    font-size: 0.9rem;
  }

  .subscribe-section {
    background: var(--secondary);
    color: white;
    padding: 4rem 2rem;
    text-align: center;
    margin-top: 3rem;
  }

  .subscribe-form {
    max-width: 600px;
    margin: 0 auto;
    display: flex;
    gap: 1rem;
  }

  .form-input {
    flex: 1;
    padding: 1rem;
    border: none;
    border-radius: 50px;
    font-size: 1rem;
  }

  .form-btn {
    background: var(--accent);
    color: white;
    border: none;
    padding: 0 2rem;
    border-radius: 50px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s;
  }

  .form-btn:hover {
    background: #c0392b;
  }

  @media (max-width: 768px) {
    .news-hero {
      height: 50vh;
    }

    .hero-content h1 {
      font-size: 2rem;
    }

    .subscribe-form {
      flex-direction: column;
    }

    .form-btn {
      padding: 1rem;
    }
  }
  </style>
</head>

<body>

  <!-- قسم الهيرو -->
  <section class="news-hero">
    <div class="hero-content">
      <span class="coming-soon-badge">قريباً</span>
      <h1 class="display-4 fw-bold mb-4">مركز أخبار سوق العمل</h1>
      <p class="lead">
        نعمل على إنشاء منصة متكاملة لتقديم أحدث الأخبار والتحليلات عن سوق العمل،
        معدلات التوظيف، المهارات المطلوبة، والاتجاهات الحديثة في مختلف القطاعات
      </p>
    </div>
  </section>

  <!-- شبكة أخبار تجريبية -->
  <div class="news-grid">
    <div class="news-card">
      <div class="news-img"></div>
      <div class="news-content">
        <span class="news-category">تكنولوجيا</span>
        <h3 class="news-title">أهم المهارات المطلوبة في سوق العمل التقني لعام 2024</h3>
        <div class="news-meta">
          <span><i class="far fa-calendar-alt me-1"></i> قريباً</span>
          <span><i class="far fa-eye me-1"></i> 0</span>
        </div>
      </div>
    </div>

    <div class="news-card">
      <div class="news-img"></div>
      <div class="news-content">
        <span class="news-category">اقتصاد</span>
        <h3 class="news-title">تقرير: القطاعات الأكثر نمواً في الشرق الأوسط</h3>
        <div class="news-meta">
          <span><i class="far fa-calendar-alt me-1"></i> قريباً</span>
          <span><i class="far fa-eye me-1"></i> 0</span>
        </div>
      </div>
    </div>

    <div class="news-card">
      <div class="news-img"></div>
      <div class="news-content">
        <span class="news-category">توظيف</span>
        <h3 class="news-title">دليل الرواتب في مصر والسعودية والإمارات لعام 2024</h3>
        <div class="news-meta">
          <span><i class="far fa-calendar-alt me-1"></i> قريباً</span>
          <span><i class="far fa-eye me-1"></i> 0</span>
        </div>
      </div>
    </div>
  </div>

  <!-- قسم الاشتراك -->
  <section class="subscribe-section">
    <h2 class="mb-4">كن أول من يعلم عند الإطلاق!</h2>
    <p class="mb-5">سجل بريدك الإلكتروني ليصلك كل جديد عن أخبار سوق العمل والوظائف</p>

    <form class="subscribe-form">
      <input type="email" class="form-input" placeholder="بريدك الإلكتروني" required>
      <button type="submit" class="form-btn">اشترك الآن</button>
    </form>
  </section>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  // تأثير عند النقر على زر الاشتراك
  document.querySelector('.form-btn').addEventListener('click', function(e) {
    e.preventDefault();
    alert('شكراً لاشتراكك! سنبقيك على اطلاع عند إطلاق خدمة الأخبار.');
  });

  // تأثيرات للبطاقات عند التمرير
  const newsCards = document.querySelectorAll('.news-card');

  function animateCards() {
    newsCards.forEach((card, index) => {
      setTimeout(() => {
        card.style.opacity = '1';
        card.style.filter = 'blur(0)';
        card.style.transform = 'translateY(0)';
      }, index * 200);
    });
  }

  // تشغيل التأثير عند تحميل الصفحة
  window.addEventListener('load', animateCards);
  </script>
</body>

</html>