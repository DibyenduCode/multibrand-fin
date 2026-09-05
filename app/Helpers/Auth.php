<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Models/User.php';

class Auth {
    private static string $cookieName = 'fin_remember_token';
    private static string $secretKey = 'fin_group_secret_key_2026';

    public static function user(): ?array {
        if (!isset($_SESSION['user']) && isset($_COOKIE[self::$cookieName])) {
            self::checkRememberCookie();
        }
        return $_SESSION['user'] ?? null;
    }

    public static function check(): bool {
        return self::user() !== null;
    }

    public static function id(): ?int {
        $u = self::user();
        return $u['id'] ?? null;
    }

    public static function role(): ?string {
        $u = self::user();
        return $u['role'] ?? null;
    }

    public static function userBrandId(): ?int {
        $u = self::user();
        return $u['brand_id'] ?? null;
    }

    public static function userBrandIds(): array {
        if (!self::check()) return [];
        $u = self::user();
        if (isset($u['managed_brand_ids']) && is_array($u['managed_brand_ids'])) {
            return $u['managed_brand_ids'];
        }
        $primary = self::userBrandId();
        return $primary ? [(int)$primary] : [];
    }

    public static function isSuperAdmin(): bool {
        return self::role() === 'super_admin';
    }

    public static function isBrandUser(): bool {
        return self::role() === 'brand_user';
    }

    public static function isManager(): bool {
        return self::role() === 'manager';
    }

    public static function login(array $user, bool $rememberMe = false): void {
        unset($user['password']);
        
        if (empty($user['managed_brand_ids']) && !empty($user['id'])) {
            $user['managed_brand_ids'] = User::getUserBrandIds((int)$user['id']);
        }
        
        $_SESSION['user'] = $user;

        if ($rememberMe) {
            $tokenPayload = $user['id'] . ':' . hash_hmac('sha256', $user['id'] . $user['email'], self::$secretKey);
            // 7 Days cookie (7 * 24 * 60 * 60 = 604800 seconds)
            setcookie(self::$cookieName, $tokenPayload, [
                'expires' => time() + 604800,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
    }

    public static function logout(): void {
        unset($_SESSION['user']);
        if (isset($_COOKIE[self::$cookieName])) {
            setcookie(self::$cookieName, '', [
                'expires' => time() - 3600,
                'path' => '/',
                'httponly' => true
            ]);
            unset($_COOKIE[self::$cookieName]);
        }
        session_destroy();
    }

    private static function checkRememberCookie(): void {
        $cookie = $_COOKIE[self::$cookieName] ?? '';
        if (empty($cookie) || strpos($cookie, ':') === false) {
            return;
        }

        list($userId, $signature) = explode(':', $cookie, 2);
        $user = User::find((int)$userId);
        if ($user) {
            $expectedSignature = hash_hmac('sha256', $user['id'] . $user['email'], self::$secretKey);
            if (hash_equals($expectedSignature, $signature)) {
                unset($user['password']);
                $_SESSION['user'] = $user;
            }
        }
    }

    public static function requireLogin(): void {
        if (!self::check()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    public static function requireSuperAdmin(): void {
        self::requireLogin();
        if (!self::isSuperAdmin()) {
            http_response_code(403);
            die("Access Denied: Super Admin privileges required.");
        }
    }

    public static function requireWriteAccess(): void {
        self::requireLogin();
        if (self::isManager()) {
            http_response_code(403);
            die("Access Denied: Managers have View Only permissions.");
        }
    }

    public static function canModifyBrandData(int $targetBrandId): bool {
        if (!self::check()) return false;
        if (self::isManager()) return false;
        if (self::isSuperAdmin()) return true;
        if (self::isBrandUser()) {
            $managedIds = self::userBrandIds();
            return in_array($targetBrandId, $managedIds);
        }
        return false;
    }

    public static function authorizeBrandModification(int $targetBrandId): void {
        self::requireWriteAccess();
        if (!self::canModifyBrandData($targetBrandId)) {
            http_response_code(403);
            die("Access Denied: You are not authorized to modify data for this brand.");
        }
    }
}
