<?php
require_once __DIR__ . '/../Helpers/Auth.php';
require_once __DIR__ . '/../Helpers/Security.php';
require_once __DIR__ . '/../Helpers/Flash.php';
require_once __DIR__ . '/../Models/Setting.php';

class SettingController {
    public static function index(): void {
        Auth::requireSuperAdmin();

        $notificationDays = (int)Setting::get('fixed_expense_notification_days', 7);

        require_once __DIR__ . '/../../views/settings/index.php';
    }

    public static function update(): void {
        Auth::requireSuperAdmin();

        if (!Security::verifyCsrf()) {
            Flash::error("Invalid CSRF token.");
            header('Location: ' . BASE_URL . '/settings');
            exit;
        }

        $days = (int)($_POST['fixed_expense_notification_days'] ?? 7);
        $days = max(0, min(31, $days));

        Setting::set('fixed_expense_notification_days', (string)$days);

        Flash::success("System Settings updated successfully! Fixed Expense Notification lead window set to " . ($days === 0 ? 'Entire Month' : $days . ' days') . ".");
        header('Location: ' . BASE_URL . '/settings');
        exit;
    }
}
