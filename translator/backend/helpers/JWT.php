<?php
// backend/helpers/JWT.php

class JWT {
    private static $secret_key = 'personal_translator_secret_key_2025_change_this';
    private static $algorithm = 'HS256';
    
    public static function generate($payload, $expiry_hours = 24) {
        // الرأس
        $header = json_encode(['typ' => 'JWT', 'alg' => self::$algorithm]);
        $header_encoded = self::base64UrlEncode($header);
        
        // البيانات مع تاريخ الانتهاء
        $payload['exp'] = time() + ($expiry_hours * 3600);
        $payload['iat'] = time();
        $payload_encoded = self::base64UrlEncode(json_encode($payload));
        
        // التوقيع
        $signature = hash_hmac('sha256', "$header_encoded.$payload_encoded", self::$secret_key, true);
        $signature_encoded = self::base64UrlEncode($signature);
        
        return "$header_encoded.$payload_encoded.$signature_encoded";
    }
    
    public static function validate($token) {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }
        
        list($header_encoded, $payload_encoded, $signature_encoded) = $parts;
        
        // التحقق من التوقيع
        $signature = self::base64UrlDecode($signature_encoded);
        $expected_signature = hash_hmac('sha256', "$header_encoded.$payload_encoded", self::$secret_key, true);
        
        if (!hash_equals($signature, $expected_signature)) {
            return false;
        }
        
        // فك تشفير البيانات
        $payload = json_decode(self::base64UrlDecode($payload_encoded), true);
        
        // التحقق من تاريخ الانتهاء
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }
        
        return $payload;
    }
    
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    private static function base64UrlDecode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}
?>