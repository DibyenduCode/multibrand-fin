<?php

class Flash {
    public static function set(string $type, string $message): void {
        $_SESSION['flash'][$type][] = $message;
    }

    public static function success(string $message): void {
        self::set('success', $message);
    }

    public static function error(string $message): void {
        self::set('error', $message);
    }

    public static function info(string $message): void {
        self::set('info', $message);
    }

    public static function warning(string $message): void {
        self::set('warning', $message);
    }

    public static function get(?string $type = null) {
        if ($type !== null) {
            if (!empty($_SESSION['flash'][$type])) {
                $msg = array_shift($_SESSION['flash'][$type]);
                if (empty($_SESSION['flash'][$type])) {
                    unset($_SESSION['flash'][$type]);
                }
                return $msg;
            }
            return null;
        }

        $flashes = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flashes;
    }
}
