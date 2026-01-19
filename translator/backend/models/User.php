<?php
// backend/models/User.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    // التحقق من وجود البريد الإلكتروني
    public function emailExists($email) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch() !== false;
    }
    
    // إنشاء مستخدم جديد
    public function create($userData) {
        try {
            // تشفير كلمة المرور
            $hashedPassword = password_hash(
                $userData['password'], 
                PASSWORD_BCRYPT, 
                ['cost' => PASSWORD_COST]
            );
            
            $stmt = $this->db->prepare("
                INSERT INTO users (full_name, email, password_hash, account_type)
                VALUES (:full_name, :email, :password_hash, :account_type)
            ");
            
            $stmt->execute([
                ':full_name' => $userData['name'],
                ':email' => $userData['email'],
                ':password_hash' => $hashedPassword,
                ':account_type' => $userData['account_type']
            ]);
            
            return [
                'id' => $this->db->lastInsertId(),
                'name' => $userData['name'],
                'email' => $userData['email'],
                'account_type' => $userData['account_type']
            ];
            
        } catch (PDOException $e) {
            // تسجيل الخطأ (في بيئة الإنتاج، استخدم نظام تسجيل أخطاء)
            error_log("خطأ في إنشاء المستخدم: " . $e->getMessage());
            return false;
        }
    }
    
    // الحصول على معلومات المستخدم
    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT id, full_name, email, account_type, created_at 
            FROM users WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
}
?>