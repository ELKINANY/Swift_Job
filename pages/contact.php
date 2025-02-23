<?php include "./navBar.php" ?>
    <!-- Main Content -->
    <main class="container py-5">
        <h1 class="text-center mb-5">اتصل بنا</h1>
        <div class="row">
            <div class="col-md-6 mb-4">
                <h2 class="mb-3">معلومات الاتصال</h2>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <strong>العنوان:</strong> معهد مصر العالي للتجارة وللتجارة والحاسبات
                    </li>
                    <li class="mb-2">
                        <strong>الهاتف:</strong> +123 456 7890
                    </li>
                    <li class="mb-2">
                        <strong>البريد الإلكتروني:</strong> support@swiftjobs.com
                    </li>
                </ul>
                <h3 class="mt-4 mb-3">ساعات العمل</h3>
                <p>الأحد - الخميس: 9:00 صباحًا - 5:00 مساءً</p>
                <p>الجمعة - السبت: مغلق</p>
            </div>
            <div class="col-md-6">
                <h2 class="mb-3">نموذج الاتصال</h2>
                <form>
                    <div class="mb-3">
                        <label for="name" class="form-label">الاسم</label>
                        <input type="text" class="form-control" id="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">البريد الإلكتروني</label>
                        <input type="email" class="form-control" id="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="subject" class="form-label">الموضوع</label>
                        <input type="text" class="form-control" id="subject" required>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">الرسالة</label>
                        <textarea class="form-control" id="message" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">إرسال</button>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include "./footer.php" ?>