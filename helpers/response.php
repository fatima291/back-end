<?php
/**
 * دالة مساعدة لإرسال ردود JSON موحدة
 */
function sendResponse($success, $message, $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    
    $response = [
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();

    // تنظيف أي إخراج سابق
    if (ob_get_length()) {
        ob_clean();
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}

/**
 * دالة لتحويل البيانات إلى JSON
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}
?>