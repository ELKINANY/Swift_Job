<?php include "./navBar.php" ?>
    <!-- Main Content -->
    <main class="container py-5">
        <h1 class="text-center mb-5">الأسئلة الشائعة</h1>
        <div class="accordion" id="faqAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                        كيف يمكنني التسجيل في الموقع؟
                    </button>
                </h2>
                <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        يمكنك التسجيل بسهولة عن طريق النقر على زر "سجل كمنشأة" أو "سجل كفرد" في الصفحة الرئيسية، ثم اتبع الخطوات البسيطة لإكمال عملية التسجيل.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                        هل التسجيل في الموقع مجاني؟
                    </button>
                </h2>
                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        نعم، التسجيل في موقعنا مجاني تمامًا لكل من الباحثين عن عمل والشركات. نحن نؤمن بتوفير فرص متكافئة للجميع.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                        كيف يمكنني البحث عن وظائف؟
                    </button>
                </h2>
                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        بعد تسجيل الدخول، يمكنك استخدام محرك البحث الخاص بنا للعثور على وظائف تناسب مهاراتك وخبراتك. يمكنك تصفية النتائج حسب الموقع، نوع الوظيفة، والراتب.
                    </div>
                </div>
            </div>
        </div>
    </main>

  <!-- Footer -->
<?php include "./footer.php" ?>