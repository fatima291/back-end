<?php
// إعدادات التطبيق
define('APP_NAME', 'Personal Translator');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'https://yourdomain.com'); // سيتم تغييره

// إعدادات JWT للتأمين (سنضيفه لاحقًا)
define('JWT_SECRET', 'your-secret-key-here-change-in-production');
define('JWT_ALGORITHM', 'HS256');

// إعدادات البريد الإلكتروني
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-email-password');

// إعدادات الترجمة
define('DEFAULT_SOURCE_LANG', 'auto');
define('DEFAULT_TARGET_LANG', 'ar');
define('MAX_TEXT_LENGTH', 5000);

// رموز الاستجابة HTTP
define('HTTP_OK', 200);
define('HTTP_CREATED', 201);
define('HTTP_BAD_REQUEST', 400);
define('HTTP_UNAUTHORIZED', 401);
define('HTTP_FORBIDDEN', 403);
define('HTTP_NOT_FOUND', 404);
define('HTTP_INTERNAL_ERROR', 500);

// أنواع المحتوى
header('Content-Type: application/json; charset=utf-8');

// إعدادات CORS للسماح بالاتصال من الفرونت إند
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
?>