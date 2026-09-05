<?php

class Security {
    // Escape XSS
    public static function e(?string $string): string {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }

    // CSRF Token Generation
    public static function csrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    // CSRF Field Input HTML
    public static function csrfField(): string {
        $token = self::csrfToken();
        return '<input type="hidden" name="csrf_token" value="' . self::e($token) . '">';
    }

    // CSRF Verification
    public static function verifyCsrf(): bool {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
                return false;
            }
        }
        return true;
    }

    // Mask Account Number (e.g. XXXX XXXX 1234)
    public static function maskAccountNumber(string $accNo): string {
        $clean = preg_replace('/\s+/', '', $accNo);
        $len = strlen($clean);
        if ($len <= 4) {
            return $clean;
        }
        $last4 = substr($clean, -4);
        return 'XXXX XXXX ' . $last4;
    }
}

// Global shortcut helper for htmlspecialchars
if (!function_exists('e')) {
    function e(?string $str): string {
        return Security::e($str);
    }
}
