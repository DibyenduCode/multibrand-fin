<?php
require_once __DIR__ . '/../Helpers/Auth.php';
require_once __DIR__ . '/../Helpers/Security.php';
require_once __DIR__ . '/../Helpers/Flash.php';
require_once __DIR__ . '/../Models/User.php';

class ProfileController {
    public static function index(): void {
        Auth::requireLogin();

        $userId = Auth::id();
        $userObj = User::find($userId);

        if (!$userObj) {
            Flash::error("User session invalid.");
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        require_once __DIR__ . '/../../views/profile/index.php';
    }

    public static function update(): void {
        Auth::requireLogin();

        if (!Security::verifyCsrf()) {
            Flash::error("Invalid CSRF token.");
            header('Location: ' . BASE_URL . '/profile');
            exit;
        }

        $userId = Auth::id();
        $currentUser = User::find($userId);

        if (!$currentUser) {
            Flash::error("User account not found.");
            header('Location: ' . BASE_URL . '/profile');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');

        if (empty($name) || empty($email)) {
            Flash::error("Full Name and Email Address are required.");
            header('Location: ' . BASE_URL . '/profile');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::error("Please enter a valid email address.");
            header('Location: ' . BASE_URL . '/profile');
            exit;
        }

        // Check if email is used by another user
        $existing = User::findByEmail($email);
        if ($existing && (int)$existing['id'] !== $userId) {
            Flash::error("The email address '" . $email . "' is already in use by another account.");
            header('Location: ' . BASE_URL . '/profile');
            exit;
        }

        // Password validation if provided
        if (!empty($password)) {
            if (strlen($password) < 6) {
                Flash::error("Password must be at least 6 characters long.");
                header('Location: ' . BASE_URL . '/profile');
                exit;
            }

            if ($password !== $confirmPassword) {
                Flash::error("New password and confirm password do not match.");
                header('Location: ' . BASE_URL . '/profile');
                exit;
            }
        }

        User::updateProfile($userId, [
            'name' => $name,
            'email' => $email,
            'password' => $password
        ]);

        // Refresh Session Data
        $updatedUser = User::find($userId);
        Auth::login($updatedUser);

        Flash::success("Your profile details have been updated successfully!");
        header('Location: ' . BASE_URL . '/profile');
        exit;
    }
}
