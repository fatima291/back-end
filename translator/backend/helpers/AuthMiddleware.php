<?php
// backend/helpers/AuthMiddleware.php

require_once __DIR__ . '/JWT.php';

class AuthMiddleware {
    
    public static function verifyToken() {
        $headers = getallheaders();
        
        // البحث عن التوكن في الرؤوس
        $token = null;
        
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                $token = $matches[1];
            }
        }
        
        // أو البحث في GET/POST
        if (!$token && isset($_GET['token'])) {
            $token = $_GET['token'];
        }
        
        if (!$token) {
            return ['authenticated' => false, 'error' => 'No token provided'];
        }
        
        // التحقق من التوكن
        $payload = JWT::validate($token);
        
        if (!$payload) {
            return ['authenticated' => false, 'error' => 'Invalid or expired token'];
        }
        
        return [
            'authenticated' => true,
            'user_id' => $payload['user_id'],
            'email' => $payload['email'],
            'account_type' => $payload['account_type']
        ];
    }
    
    public static function requireAuth() {
        $auth = self::verifyToken();
        
        if (!$auth['authenticated']) {
            require_once __DIR__ . '/Response.php';
            Response::error('Authentication required', [], 401);
        }
        
        return $auth;
    }
    
    public static function redirectIfLoggedIn() {
        $auth = self::verifyToken();
        
        if ($auth['authenticated']) {
            // توجيه حسب نوع الحساب
            self::redirectBasedOnAccount($auth['account_type']);
        }
    }
    
    public static function redirectBasedOnAccount($accountType) {
        $redirectUrls = [
            'student' => '../frontend/requsets.html',
            'translator' => '../frontend/Translation_result.html',
            'teacher' => '../frontend/Courses.html'
        ];
        
        if (isset($redirectUrls[$accountType])) {
            header('Location: ' . $redirectUrls[$accountType]);
            exit;
        }
    }
}
?>