<?php
// session_manager.php
class SessionManager {
    
    public static function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start([
                'cookie_lifetime' => 86400, // 24 hours
                'cookie_secure'   => false, // Set to true if using HTTPS
                'cookie_httponly' => true,
                'use_strict_mode' => true
            ]);
        }
    }
    
    public static function set($key, $value) {
        self::startSession();
        $_SESSION[$key] = $value;
    }
    
    public static function get($key, $default = null) {
        self::startSession();
        return $_SESSION[$key] ?? $default;
    }
    
    public static function remove($key) {
        self::startSession();
        unset($_SESSION[$key]);
    }
    
    public static function destroy() {
        self::startSession();
        session_unset();
        session_destroy();
    }
    
    public static function isLoggedIn() {
        return self::get('user_id') !== null;
    }
    
    public static function getUserId() {
        return self::get('user_id');
    }
}
?>