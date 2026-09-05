<?php
require_once __DIR__ . '/../Helpers/Auth.php';
require_once __DIR__ . '/../Helpers/Security.php';
require_once __DIR__ . '/../Helpers/Flash.php';
require_once __DIR__ . '/../Models/User.php';

class AuthController {
    public static function showLogin(): void {
        if (Auth::check()) {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }

        require_once __DIR__ . '/../../views/auth/login.php';
    }

    public static function login(): void {
        if (!Security::verifyCsrf()) {
            Flash::error("Invalid CSRF token.");
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $rememberMe = !empty($_POST['remember_me']);

        if (empty($email) || empty($password)) {
            Flash::error("Please provide both email and password.");
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $user = User::findByEmail($email);

        if (!$user || !User::verifyPassword($user, $password)) {
            Flash::error("Invalid email address or password.");
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        if ($user['status'] !== 'active') {
            Flash::error("Your account has been deactivated. Please contact Super Admin.");
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        Auth::login($user, $rememberMe);
        Flash::success("Welcome back, " . $user['name'] . "!");
        header('Location: ' . BASE_URL . '/dashboard');
        exit;
    }

    public static function logout(): void {
        Auth::logout();
        Flash::info("You have been successfully logged out.");
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}
