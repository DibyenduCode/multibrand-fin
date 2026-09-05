<?php
require_once __DIR__ . '/../../config/database.php';

class Setting {
    public static function get(string $key, $default = null) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([trim($key)]);
        $val = $stmt->fetchColumn();
        return ($val !== false) ? $val : $default;
    }

    public static function set(string $key, string $value): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
                              ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        return $stmt->execute([trim($key), trim($value)]);
    }

    public static function all(): array {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
    }
}
