<?php
// backend/helpers/Validation.php
require_once __DIR__ . '/../config/constants.php';

class Validation {
    
    // تنظيف وإعداد البيانات
    public static function sanitize($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::sanitize($value);
            }
            return $data;
        }
        
        // إزالة المسافات الزائدة
        $data = trim($data);
        // منع XSS
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        // إزالة السلاش
        $data = stripslashes($data);
        
        return $data;
    }
    
    // التحقق من البريد الإلكتروني
    public static function validateEmail($email) {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => ERROR_INVALID_EMAIL];
        }
        
        if (strlen($email) > 255) {
            return ['valid' => false, 'message' => 'البريد الإلكتروني طويل جداً'];
        }
        
        return ['valid' => true, 'email' => $email];
    }
    
    // التحقق من الاسم
    public static function validateName($name) {
        $name = self::sanitize($name);
        
        if (strlen($name) < 2 || strlen($name) > 100) {
            return ['valid' => false, 'message' => ERROR_INVALID_NAME];
        }
        
        // التحقق من أن الاسم يحتوي على أحرف صحيحة
        if (!preg_match('/^[\p{Arabic}\p{L}\s\-\.]+$/u', $name)) {
            return ['valid' => false, 'message' => 'الاسم يحتوي على أحرف غير مسموحة'];
        }
        
        return ['valid' => true, 'name' => $name];
    }
    
    // التحقق من كلمة المرور
    public static function validatePassword($password) {
        if (strlen($password) < MIN_PASSWORD_LENGTH) {
            return ['valid' => false, 'message' => ERROR_WEAK_PASSWORD];
        }
        
        if (strlen($password) > MAX_PASSWORD_LENGTH) {
            return ['valid' => false, 'message' => 'كلمة المرور طويلة جداً'];
        }
        
        if (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
            return ['valid' => false, 'message' => 'كلمة المرور يجب أن تحتوي على أحرف كبيرة وأرقام'];
        }
        
        return ['valid' => true];
    }
    
    // التحقق من نوع الحساب
    public static function validateAccountType($type) {
        $type = strtolower(self::sanitize($type));
        
        if (!in_array($type, ALLOWED_ACCOUNT_TYPES)) {
            return ['valid' => false, 'message' => 'نوع الحساب غير صالح'];
        }
        
        return ['valid' => true, 'type' => $type];
    }
}
?>