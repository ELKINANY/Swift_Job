<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>كورسات Swift Jobs - قريباً</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
  :root {
    --primary-color: #3498db;
    --secondary-color: #2c3e50;
    --accent-color: #e74c3c;
    --gradient: linear-gradient(135deg, #3498db, #2c3e50);
  }

  body {
    font-family: 'Tajawal', sans-serif;
    background-color: #f5f7fa;
    color: #2c3e50;
    height: 100vh;
    overflow-x: hidden;
  }

  .coming-soon-container {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 2rem;
    background: url('https://images.unsplash.com/photo-1551434678-e076c223a692?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') no-repeat center center;
    background-size: cover;
    position: relative;
  }

  .coming-soon-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    z-index: 0;
  }

  .coming-soon-content {
    position: relative;
    z-index: 1;
    text-align: center;
    max-width: 800px;
    width: 100%;
    padding: 3rem;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 20px;
    box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(5px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transform-style: preserve-3d;
    perspective: 1000px;
    animation: float 6s ease-in-out infinite;
  }

  @keyframes float {

    0%,
    100% {
      transform: translateY(0) rotateY(0deg);
    }

    50% {
      transform: translateY(-20px) rotateY(5deg);
    }
  }

  .logo {
    width: 120px;
    margin-bottom: 1.5rem;
    filter: drop-shadow(0 5px 15px rgba(0, 0, 0, 0.2));
  }

  h1 {
    font-size: 3.5rem;
    font-weight: 800;
    margin-bottom: 1rem;
    background: var(--gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    text-shadow: 0 2px 10px rgba(52, 152, 219, 0.2);
  }

  .coming-soon-text {
    font-size: 1.5rem;
    margin-bottom: 2rem;
    line-height: 1.6;
  }

  .countdown {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
  }

  .countdown-item {
    background: var(--gradient);
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 10px;
    min-width: 80px;
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
    transition: all 0.3s;
  }

  .countdown-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(52, 152, 219, 0.4);
  }

  .countdown-number {
    font-size: 2rem;
    font-weight: 700;
    display: block;
  }

  .countdown-label {
    font-size: 0.9rem;
    opacity: 0.9;
  }

  .notify-btn {
    background: var(--accent-color);
    color: white;
    border: none;
    padding: 1rem 2.5rem;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 50px;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
  }

  .notify-btn:hover {
    background: #c0392b;
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(231, 76, 60, 0.4);
  }

  .social-links {
    margin-top: 2rem;
    display: flex;
    gap: 1rem;
    justify-content: center;
  }

  .social-link {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--gradient);
    color: white;
    transition: all 0.3s;
  }

  .social-link:hover {
    transform: translateY(-3px) scale(1.1);
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
  }

  @media (max-width: 768px) {
    h1 {
      font-size: 2.5rem;
    }

    .coming-soon-text {
      font-size: 1.2rem;
    }

    .coming-soon-content {
      padding: 2rem 1.5rem;
    }

    .countdown-item {
      padding: 0.8rem 1rem;
      min-width: 70px;
    }

    .countdown-number {
      font-size: 1.5rem;
    }
  }
  </style>
</head>

<body>>

  <div class="coming-soon-container">
    <div class="coming-soon-content">
      <img src="companies/logo.jpg" alt="Swift Jobs Logo" class="logo">
      <h1>كورسات Swift Jobs</h1>
      <p class="coming-soon-text">
        نحن نعمل على تحضير مجموعة من الكورسات التدريبية المتميزة التي ستساعدك على تطوير مهاراتك وزيادة فرصك الوظيفية.
        ترقبوا الإطلاق قريباً!
      </p>

      <div class="countdown">
        <div class="countdown-item">
          <span class="countdown-number" id="days">00</span>
          <span class="countdown-label">أيام</span>
        </div>
        <div class="countdown-item">
          <span class="countdown-number" id="hours">00</span>
          <span class="countdown-label">ساعات</span>
        </div>
        <div class="countdown-item">
          <span class="countdown-number" id="minutes">00</span>
          <span class="countdown-label">دقائق</span>
        </div>
        <div class="countdown-item">
          <span class="countdown-number" id="seconds">00</span>
          <span class="countdown-label">ثواني</span>
        </div>
      </div>

      <button class="notify-btn">
        <i class="fas fa-bell"></i> إشعارني عند الإطلاق
      </button>

      <div class="social-links">
        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
        <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  // Countdown timer - set launch date to 30 days from now
  const launchDate = new Date();
  launchDate.setDate(launchDate.getDate() + 30);

  function updateCountdown() {
    const now = new Date();
    const diff = launchDate - now;

    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

    document.getElementById('days').textContent = days.toString().padStart(2, '0');
    document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
    document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
    document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
  }

  // Update countdown every second
  setInterval(updateCountdown, 1000);
  updateCountdown();

  // Notify button functionality
  document.querySelector('.notify-btn').addEventListener('click', function() {
    alert('شكراً لاهتمامك! سنقوم بإعلامك بمجرد إطلاق الكورسات.');
  });
  </script>
</body>

</html>