<?php
require_once __DIR__ . '/../Helpers/Auth.php';
require_once __DIR__ . '/../Helpers/Security.php';
require_once __DIR__ . '/../Helpers/Format.php';
require_once __DIR__ . '/../Models/Brand.php';
require_once __DIR__ . '/../Models/User.php';

class DirectoryController {
    public static function index(): void {
        Auth::requireLogin();

        $brands = Brand::all(true);
        require_once __DIR__ . '/../../views/directory/index.php';
    }
}
