<?php
session_start();

// إنهاء الجلسة
session_unset(); // إزالة جميع متغيرات الجلسة
session_destroy(); // تدمير الجلسة

// إعادة توجيه المستخدم إلى الصفحة الرئيسية
header("Location: index.php");
exit();