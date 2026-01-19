<?php
// backend/models/Session.php
require_once __DIR__ . '/../config/database.php';

class Session {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    // تسجيل جلسة دخول
    public function create($userId, $token, $ipAddress, $userAgent) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO user_sessions 
                (user_id, token, ip_address, user_agent, expires_at) 
                VALUES (:user_id, :token, :ip_address, :user_agent, :expires_at)
            ");
            
            $expiresAt = date('Y-m-d H:i:s', time() + (24 * 3600)); // 24 ساعة
            
            $stmt->execute([
                ':user_id' => $userId,
                ':token' => $token,
                ':ip_address' => $ipAddress,
                ':user_agent' => $userAgent,
                ':expires_at' => $expiresAt
            ]);
            
            return true;
        } catch (PDOException $e) {
            error_log("Session creation error: " . $e->getMessage());
            return false;
        }
    }
    
    // التحقق من صحة الجلسة
    public function isValid($token) {
        $stmt = $this->db->prepare("
            SELECT * FROM user_sessions 
            WHERE token = :token 
            AND expires_at > NOW() 
            AND is_revoked = FALSE
        ");
        
        $stmt->execute([':token' => $token]);
        return $stmt->fetch() !== false;
    }
    
    // إنهاء الجلسة
    public function revoke($token) {
        $stmt = $this->db->prepare("
            UPDATE user_sessions 
            SET is_revoked = TRUE, revoked_at = NOW() 
            WHERE token = :token
        ");
        
        return $stmt->execute([':token' => $token]);
    }
}
?>