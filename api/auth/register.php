<?php
// backend/api/auth/register.php

// تمكين معالجة CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// معالجة طلب OPTIONS (لـ CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// تأكد من أن الطلب POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'الطريقة غير مسموحة']);
    exit();
}

// تحميل الملفات المطلوبة
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../helpers/Validation.php';
require_once __DIR__ . '/../../helpers/Response.php';
require_once __DIR__ . '/../../models/User.php';

// استخدام Response للتعامل مع CORS
Response::handleOptions();

// الحصول على البيانات من الطلب
$input = json_decode(file_get_contents('php://input'), true);

// التحقق من وجود البيانات
if (!$input || empty($input)) {
    Response::error('لم يتم استقبال أي بيانات');
}

// تنظيف البيانات
$input = Validation::sanitize($input);

// التحقق من الحقول المطلوبة
$requiredFields = ['name', 'email', 'password', 'account_type'];
$missingFields = [];

foreach ($requiredFields as $field) {
    if (empty($input[$field])) {
        $missingFields[] = $field;
    }
}

if (!empty($missingFields)) {
    Response::error('الحقول التالية مطلوبة: ' . implode(', ', $missingFields));
}

// التحقق من صحة البيانات
$errors = [];

// التحقق من الاسم
$nameValidation = Validation::validateName($input['name']);
if (!$nameValidation['valid']) {
    $errors['name'] = $nameValidation['message'];
} else {
    $name = $nameValidation['name'];
}

// التحقق من البريد الإلكتروني
$emailValidation = Validation::validateEmail($input['email']);
if (!$emailValidation['valid']) {
    $errors['email'] = $emailValidation['message'];
} else {
    $email = $emailValidation['email'];
}

// التحقق من كلمة المرور
$passwordValidation = Validation::validatePassword($input['password']);
if (!$passwordValidation['valid']) {
    $errors['password'] = $passwordValidation['message'];
} else {
    $password = $input['password'];
}

// التحقق من نوع الحساب
$typeValidation = Validation::validateAccountType($input['account_type']);
if (!$typeValidation['valid']) {
    $errors['account_type'] = $typeValidation['message'];
} else {
    $accountType = $typeValidation['type'];
}

// إذا كانت هناك أخطاء في التحقق
if (!empty($errors)) {
    Response::error('أخطاء في التحقق من البيانات', $errors);
}

// إنشاء المستخدم
try {
    $userModel = new User();
    
    // التحقق من عدم وجود البريد مسبقاً
    if ($userModel->emailExists($email)) {
        Response::error(ERROR_EMAIL_EXISTS);
    }
    
    // إنشاء المستخدم
    $userData = [
        'name' => $name,
        'email' => $email,
        'password' => $password,
        'account_type' => $accountType
    ];
    
    $user = $userModel->create($userData);
    
    if ($user) {
        // نجاح التسجيل
        Response::success('تم التسجيل بنجاح!', [
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'account_type' => $user['account_type']
            ],
            'redirect' => '../login.html' // سيتم التوجيه للدخول
        ]);
    } else {
        Response::error(ERROR_SERVER);
    }
    
} catch (Exception $e) {
    // تسجيل الخطأ للتصحيح
    error_log("خطأ في تسجيل المستخدم: " . $e->getMessage());
    Response::error('حدث خطأ غير متوقع. يرجى المحاولة مرة أخرى');
}
?>