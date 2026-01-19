<?php
// C:\xampp\htdocs\personal_translator_main\backend\test_register.php

// تمكين عرض الأخطاء
error_reporting(E_ALL);
ini_set('display_errors', 1);

// محاكاة طلب POST مع البيانات
$_SERVER['REQUEST_METHOD'] = 'POST';

// إعداد محتوى الطلب (مقلد Postman)
$testData = [
    'name' => 'اختبار النظام',
    'email' => 'system_test_' . time() . '@example.com', // بريد فريد كل مرة
    'password' => '123456',
    'account_type' => 'teacher'
];

// محاكاة php://input
file_put_contents('php://memory', json_encode($testData));
rewind(fopen('php://memory', 'r'));

// محاكاة قراءة البيانات
$input = file_get_contents('php://memory');
$input = json_decode($input, true);

echo "<h3>🎯 اختبار نظام التسجيل</h3>";
echo "<pre>البيانات المرسلة: " . print_r($testData, true) . "</pre>";

// تشغيل ملف register.php
try {
    require_once 'api/auth/register.php';
} catch (Exception $e) {
    echo "<pre>❌ خطأ: " . $e->getMessage() . "</pre>";
}
?>