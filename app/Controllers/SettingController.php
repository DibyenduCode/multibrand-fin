<?php
require_once __DIR__ . '/../Helpers/Auth.php';
require_once __DIR__ . '/../Helpers/Security.php';
require_once __DIR__ . '/../Helpers/Flash.php';
require_once __DIR__ . '/../Models/Setting.php';

class SettingController {
    public static function index(): void {
        Auth::requireSuperAdmin();

        $notificationDays = (int)Setting::get('fixed_expense_notification_days', 7);

        $smtpSettings = [
            'host' => Setting::get('smtp_host', ''),
            'port' => Setting::get('smtp_port', '587'),
            'username' => Setting::get('smtp_username', ''),
            'password' => Setting::get('smtp_password', ''),
            'encryption' => Setting::get('smtp_encryption', 'tls'),
            'from_email' => Setting::get('smtp_from_email', ''),
            'from_name' => Setting::get('smtp_from_name', 'Fin App Notifications'),
            'enabled' => Setting::get('smtp_enabled', '1'),
        ];

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

    public static function updateSmtp(): void {
        Auth::requireSuperAdmin();

        if (!Security::verifyCsrf()) {
            Flash::error("Invalid CSRF token.");
            header('Location: ' . BASE_URL . '/settings');
            exit;
        }

        $host = trim($_POST['smtp_host'] ?? '');
        $port = (int)($_POST['smtp_port'] ?? 587);
        $username = trim($_POST['smtp_username'] ?? '');
        $password = $_POST['smtp_password'] ?? '';
        $encryption = trim($_POST['smtp_encryption'] ?? 'tls');
        $fromEmail = trim($_POST['smtp_from_email'] ?? '');
        $fromName = trim($_POST['smtp_from_name'] ?? '');
        $enabled = isset($_POST['smtp_enabled']) ? '1' : '0';

        Setting::set('smtp_host', $host);
        Setting::set('smtp_port', (string)$port);
        Setting::set('smtp_username', $username);
        Setting::set('smtp_password', $password);
        Setting::set('smtp_encryption', $encryption);
        Setting::set('smtp_from_email', $fromEmail);
        Setting::set('smtp_from_name', $fromName);
        Setting::set('smtp_enabled', $enabled);

        Flash::success("SMTP Settings updated successfully!");
        header('Location: ' . BASE_URL . '/settings');
        exit;
    }

    public static function testSmtp(): void {
        Auth::requireSuperAdmin();
        require_once __DIR__ . '/../Helpers/Mailer.php';

        if (!Security::verifyCsrf()) {
            Flash::error("Invalid CSRF token.");
            header('Location: ' . BASE_URL . '/settings');
            exit;
        }

        $testEmail = trim($_POST['test_email'] ?? '');
        if (empty($testEmail) || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            Flash::error("Please provide a valid test recipient email address.");
            header('Location: ' . BASE_URL . '/settings');
            exit;
        }

        $res = Mailer::testConnection($testEmail);
        if ($res['success']) {
            Flash::success($res['message']);
        } else {
            Flash::error("SMTP Test Failed: " . $res['message']);
        }

        header('Location: ' . BASE_URL . '/settings');
        exit;
    }
}

