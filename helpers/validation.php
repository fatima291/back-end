<?php
/**
 * دالة للتحقق من صحة البريد الإلكتروني
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * دالة للتحقق من قوة كلمة المرور
 */
function validatePassword($password) {
    // على الأقل 6 أحرف
    if (strlen($password) < 6) {
        return false;
    }
    
    // يمكن إضافة شروط إضافية مثل:
    // - تحتوي على حرف كبير
    // - تحتوي على رقم
    // - تحتوي على حرف خاص
    
    return true;
}

/**
 * دالة للتحقق من الاسم
 */
function validateName($name) {
    // الاسم يجب أن يكون بين 2 و 100 حرف
    $length = strlen(trim($name));
    return $length >= 2 && $length <= 100;
}

/**
 * دالة للتحقق من رقم الهاتف
 */
function validatePhone($phone) {
    if (empty($phone)) {
        return true; // اختياري
    }
    
    // تحقق من تنسيق رقم الهاتف الدولي
    return preg_match('/^[0-9]{10,15}$/', $phone);
}

/**
 * دالة لتنظيف المدخلات
 */
function sanitizeInput($input) {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}
?>