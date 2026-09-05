<?php
require_once __DIR__ . '/../Helpers/Auth.php';
require_once __DIR__ . '/../Helpers/Security.php';
require_once __DIR__ . '/../Helpers/Flash.php';
require_once __DIR__ . '/../Models/Brand.php';
require_once __DIR__ . '/../Models/User.php';

class UserController {
    public static function index(): void {
        Auth::requireSuperAdmin();

        $users = User::all();
        require_once __DIR__ . '/../../views/users/index.php';
    }

    public static function create(): void {
        Auth::requireSuperAdmin();

        $brands = Brand::all(true);
        require_once __DIR__ . '/../../views/users/create.php';
    }

    public static function store(): void {
        Auth::requireSuperAdmin();

        if (!Security::verifyCsrf()) {
            Flash::error("Invalid CSRF token.");
            header('Location: ' . BASE_URL . '/users/create');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $role = $_POST['role'] ?? 'brand_user';
        $brandIds = !empty($_POST['brand_ids']) && is_array($_POST['brand_ids']) ? array_map('intval', $_POST['brand_ids']) : [];

        if (empty($name) || empty($email) || empty($password)) {
            Flash::error("Name, email, and password are required.");
            header('Location: ' . BASE_URL . '/users/create');
            exit;
        }

        if ($role === 'brand_user' && empty($brandIds)) {
            Flash::error("A Brand User must be assigned to at least one Brand.");
            header('Location: ' . BASE_URL . '/users/create');
            exit;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role,
            'brand_ids' => $brandIds,
            'status' => $_POST['status'] ?? 'active'
        ]);

        Flash::success("User '" . $name . "' created with " . count($brandIds) . " brand assignment(s)!");
        header('Location: ' . BASE_URL . '/users');
        exit;
    }

    public static function edit(int $id): void {
        Auth::requireSuperAdmin();

        $userObj = User::find($id);
        if (!$userObj) {
            Flash::error("User not found.");
            header('Location: ' . BASE_URL . '/users');
            exit;
        }

        $brands = Brand::all(true);
        require_once __DIR__ . '/../../views/users/edit.php';
    }

    public static function update(int $id): void {
        Auth::requireSuperAdmin();

        if (!Security::verifyCsrf()) {
            Flash::error("Invalid CSRF token.");
            header('Location: ' . BASE_URL . '/users/' . $id . '/edit');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'brand_user';
        $brandIds = !empty($_POST['brand_ids']) && is_array($_POST['brand_ids']) ? array_map('intval', $_POST['brand_ids']) : [];
        $password = trim($_POST['password'] ?? '');

        if (empty($name) || empty($email)) {
            Flash::error("Name and email are required.");
            header('Location: ' . BASE_URL . '/users/' . $id . '/edit');
            exit;
        }

        if ($role === 'brand_user' && empty($brandIds)) {
            Flash::error("A Brand User must be assigned to at least one Brand.");
            header('Location: ' . BASE_URL . '/users/' . $id . '/edit');
            exit;
        }

        User::update($id, [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role,
            'brand_ids' => $brandIds,
            'status' => $_POST['status'] ?? 'active'
        ]);

        Flash::success("User profile and brand permissions updated successfully!");
        header('Location: ' . BASE_URL . '/users');
        exit;
    }

    public static function delete(int $id): void {
        Auth::requireSuperAdmin();

        if (!Security::verifyCsrf()) {
            Flash::error("Invalid CSRF token.");
            header('Location: ' . BASE_URL . '/users');
            exit;
        }

        if ($id === (int)Auth::id()) {
            Flash::error("You cannot delete your own logged-in Super Admin account.");
            header('Location: ' . BASE_URL . '/users');
            exit;
        }

        $userObj = User::find($id);
        if (!$userObj) {
            Flash::error("User account not found.");
            header('Location: ' . BASE_URL . '/users');
            exit;
        }

        try {
            User::delete($id);
            Flash::success("User account '" . $userObj['name'] . "' deleted successfully. All past financial entries created by this user have been safely preserved.");
        } catch (Exception $e) {
            Flash::error("Error deleting user: " . $e->getMessage());
        }

        header('Location: ' . BASE_URL . '/users');
        exit;
    }
}
