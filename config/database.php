<?php
class Database {
    private $host = "localhost";
    private $db_name = "personal_translator";
    private $username = "root";  // سيتم تغييرها لاحقًا
    private $password = "";      // سيتم تغييرها لاحقًا
    public $conn;
    
    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            
            // تعيين سمات PDO
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            
            // إعدادات إضافية لضمان الاتصال السليم
            $this->conn->exec("SET NAMES 'utf8mb4'");
            $this->conn->exec("SET CHARACTER SET utf8mb4");
            $this->conn->exec("SET SESSION collation_connection = 'utf8mb4_unicode_ci'"); // توقيت السعودية
            
        } catch(PDOException $exception) {
            // في البيئة الحقيقية، سجل الخطأ دون عرضه للمستخدم
            error_log("Connection error: " . $exception->getMessage());
            
            // رسالة آمنة للمستخدم
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'عذرًا، حدث خطأ في الخادم. يرجى المحاولة لاحقًا.'
            ]);
            exit();
        }
        
        return $this->conn;
    }
    
    /**
     * إغلاق الاتصال بقاعدة البيانات
     */
    public function closeConnection() {
        $this->conn = null;
    }
    
    /**
     * التحقق من اتصال قاعدة البيانات
     */
    public function testConnection() {
        try {
            $conn = $this->getConnection();
            if ($conn) {
                return [
                    'success' => true,
                    'message' => '✅ تم الاتصال بقاعدة البيانات بنجاح'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => '❌ فشل الاتصال بقاعدة البيانات: ' . $e->getMessage()
            ];
        }
    }
}

// إنشاء كائن قاعدة بيانات عام للاستخدام
$database = new Database();
?>