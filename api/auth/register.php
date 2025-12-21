<?php

ini_set('display_errors', 0); // إخفاء الأخطاء عن المستخدمين
ini_set('log_errors', 1); // تسجيل الأخطاء في ملف
ini_set('error_log', __DIR__ . '/../../logs/php_errors.log');

// إعدادات الترميز 
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// تمكين عرض الأخطاء أثناء التطوير فقط
error_reporting(E_ALL);
ini_set('display_errors', 1);


// تضمين ملف الاتصال بقاعدة البيانات
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/validation.php';

// التعامل مع طلبات OPTIONS لـ CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// التحقق من أن الطلب هو POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'الطريقة غير مسموحة', null, 405);
    exit();
}

// الحصول على البيانات من body الطلب
$data = json_decode(file_get_contents('php://input'), true);

// التحقق من وجود البيانات الأساسية
if (!isset($data['email']) || !isset($data['password']) || !isset($data['name'])) {
    sendResponse(false, 'بيانات غير مكتملة. يرجى إدخال البريد الإلكتروني وكلمة المرور والاسم الكامل', null, 400);
    exit();
}

// تنظيف وتهيئة البيانات
$email = trim($data['email']);
$password = $data['password'];
$name = trim($data['name']);
$account_type = isset($data['account_type']) ? trim($data['account_type']) : 'student';

// التحقق من صحة البيانات

$validationErrors = [];

$allowed_account_types = ['student', 'teacher', 'translator'];
if (!in_array($account_type, $allowed_account_types)) {
    $validationErrors['account_type'] = 'نوع الحساب غير صحيح';
}

// التحقق من البريد الإلكتروني
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $validationErrors['email'] = 'البريد الإلكتروني غير صحيح';
}

// التحقق من كلمة المرور (على الأقل 6 أحرف)
if (strlen($password) < 6) {
    $validationErrors['password'] = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
}

// التحقق من الاسم الكامل
if (strlen($name) < 2) {
    $validationErrors['name'] = 'الاسم الكامل يجب أن يكون حرفين على الأقل';
}


// إذا كانت هناك أخطاء في التحقق
if (!empty($validationErrors)) {
    sendResponse(false, 'خطأ في التحقق من البيانات', ['errors' => $validationErrors], 400);
    exit();
}

try {
    // إنشاء اتصال بقاعدة البيانات
    $database = new Database();
    $conn = $database->getConnection();
    
    // التحقق من عدم وجود مستخدم بنفس البريد الإلكتروني
    $checkQuery = "SELECT id FROM users WHERE email = :email";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bindParam(':email', $email);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() > 0) {
        sendResponse(false, 'البريد الإلكتروني مسجل مسبقاً', null, 409);
        exit();
    }
    
    // تشفير كلمة المرور
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // إعداد الاستعلام لإدخال المستخدم الجديد
    $query = "INSERT INTO users 
          (email, password, name, account_type, created_at, updated_at) 
          VALUES 
          (:email, :password, :name, :account_type, NOW(), NOW())";
    
    $stmt = $conn->prepare($query);
    
    // ربط القيم
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':account_type', $account_type);
    
    // تنفيذ الاستعلام
    if ($stmt->execute()) {
        // الحصول على ID المستخدم الجديد
        $userId = $conn->lastInsertId();
        
        // جلب بيانات المستخدم (بدون كلمة المرور)
        $userQuery = "SELECT id, email, name, account_type, created_at FROM users WHERE id = :id";
        $userStmt = $conn->prepare($userQuery);
        $userStmt->bindParam(':id', $userId);
        $userStmt->execute();
        
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        // التأكد من ترميز UTF-8
        if ($user && is_array($user)) {
            array_walk_recursive($user, function(&$value) {
                if (is_string($value)) {
                    $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                }
            });
        }
        // إرجاع الاستجابة الناجحة
        sendResponse(true, 'تم إنشاء الحساب بنجاح', [
            'user' => $user,
            'message' => 'مرحباً ' . $name . '! تم إنشاء حسابك بنجاح.'
        ], 201);
        
    } else {
        sendResponse(false, 'فشل في إنشاء الحساب', null, 500);
    }
    
} catch (PDOException $e) {
    // تسجيل الخطأ (في ملف log في البيئة الحقيقية)
    error_log("Register Error: " . $e->getMessage());
    
    sendResponse(false, 'حدث خطأ في الخادم', null, 500);
    
} finally {
    // إغلاق الاتصال
    if (isset($conn)) {
        $database->closeConnection();
    }
}
?>