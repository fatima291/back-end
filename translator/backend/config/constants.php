<?php
// backend/config/constants.php

// إعدادات الموقع
define('SITE_NAME', 'Personal Translator');
define('BASE_URL', 'http://localhost/personal_translator_main'); //تغيير

// إعدادات الأمان
define('MIN_PASSWORD_LENGTH', 6);
define('MAX_PASSWORD_LENGTH', 255);
define('PASSWORD_COST', 12); // تكلفة تشفير bcrypt

// أنواع الحسابات المسموحة
define('ALLOWED_ACCOUNT_TYPES', ['student', 'teacher', 'translator']);

// رسائل الأخطاء الشائعة
define('ERROR_EMAIL_EXISTS', 'البريد الإلكتروني مسجل مسبقاً');
define('ERROR_INVALID_EMAIL', 'البريد الإلكتروني غير صالح');
define('ERROR_WEAK_PASSWORD', 'كلمة المرور ضعيفة. يجب أن تكون 6 أحرف على الأقل');
define('ERROR_INVALID_NAME', 'الاسم يجب أن يكون بين 2 و 100 حرف');
define('ERROR_MISSING_FIELDS', 'جميع الحقول مطلوبة');
define('ERROR_SERVER', 'حدث خطأ في الخادم. يرجى المحاولة لاحقاً');
?>