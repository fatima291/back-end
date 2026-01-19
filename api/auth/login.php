<?php
// backend/api/auth/login.php

// تمكين CORS ومعالجة OPTIONS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// الطلب يجب أن يكون POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'الطريقة غير مسموحة']);
    exit();
}

// تحميل الملفات المطلوبة
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../helpers/Validation.php';
require_once __DIR__ . '/../../helpers/Response.php';
require_once __DIR__ . '/../../helpers/JWT.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Session.php';

// استخدام Response للتعامل مع CORS
Response::handleOptions();

// الحصول على البيانات من الطلب
$input = json_decode(file_get_contents('php://input'), true);

// التحقق من وجود البيانات
if (!$input || empty($input)) {
    Response::error('لم يتم استقبال أي بيانات');
}

// التحقق من الحقول المطلوبة
if (empty($input['email']) || empty($input['password'])) {
    Response::error('البريد الإلكتروني وكلمة المرور مطلوبان');
}

// تنظيف البيانات
$input = Validation::sanitize($input);
$email = $input['email'];
$password = $input['password'];

// التحقق من صحة البريد الإلكتروني
$emailValidation = Validation::validateEmail($email);
if (!$emailValidation['valid']) {
    Response::error($emailValidation['message']);
}

// التحقق من كلمة المرور
$passwordValidation = Validation::validatePassword($password);
if (!$passwordValidation['valid']) {
    Response::error($passwordValidation['message']);
}

try {
    $userModel = new User();
    $sessionModel = new Session();
    
    // البحث عن المستخدم بالبريد الإلكتروني
    $db = getDB();
    $stmt = $db->prepare("
        SELECT id, full_name, email, password_hash, account_type, is_active 
        FROM users 
        WHERE email = :email
    ");
    
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();
    
    // التحقق من وجود المستخدم
    if (!$user) {
        Response::error('البريد الإلكتروني أو كلمة المرور غير صحيحة');
    }
    
    // التحقق من حالة الحساب
    if (!$user['is_active']) {
        Response::error('الحساب معطل. يرجى التواصل مع الإدارة');
    }
    
    // التحقق من كلمة المرور
    if (!password_verify($password, $user['password_hash'])) {
        // تسجيل محاولة الدخول الفاشلة (يمكن تطويره)
        Response::error('البريد الإلكتروني أو كلمة المرور غير صحيحة');
    }
    
    // تحديث آخر دخول
    $updateStmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
    $updateStmt->execute([':id' => $user['id']]);
    
    // إنشاء توكن JWT
    $tokenPayload = [
        'user_id' => $user['id'],
        'email' => $user['email'],
        'name' => $user['full_name'],
        'account_type' => $user['account_type']
    ];
    
    $token = JWT::generate($tokenPayload, 24); // توكن لمدة 24 ساعة
    
    // تسجيل الجلسة في قاعدة البيانات
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    $sessionModel->create($user['id'], $token, $ipAddress, $userAgent);
    
    // تحديد صفحة التوجيه حسب نوع الحساب
    $redirectUrls = [
        'student' => 'requsets.html',
        'translator' => 'Translation_result.html',
        'teacher' => 'Courses.html'
    ];
    
    $redirectPage = $redirectUrls[$user['account_type']] ?? 'index.html';
    
    // إرسال الرد الناجح
    Response::success('تم تسجيل الدخول بنجاح', [
        'user' => [
            'id' => $user['id'],
            'name' => $user['full_name'],
            'email' => $user['email'],
            'account_type' => $user['account_type']
        ],
        'token' => $token,
        'redirect' => $redirectPage,
        'expires_in' => 24 * 3600 // بالثواني
    ]);
    
} catch (Exception $e) {
    // تسجيل الخطأ للتصحيح
    error_log("Login error: " . $e->getMessage());
    Response::error('حدث خطأ في الخادم. يرجى المحاولة مرة أخرى');
}
?>