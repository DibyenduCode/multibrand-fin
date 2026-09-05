<?php
require_once __DIR__ . '/../Helpers/Auth.php';
require_once __DIR__ . '/../Helpers/Security.php';
require_once __DIR__ . '/../Helpers/Flash.php';
require_once __DIR__ . '/../Helpers/Upload.php';
require_once __DIR__ . '/../Models/Brand.php';
require_once __DIR__ . '/../Models/User.php';

class BrandController {
    public static function index(): void {
        Auth::requireSuperAdmin();

        $brands = Brand::all();
        require_once __DIR__ . '/../../views/brands/index.php';
    }

    public static function create(): void {
        Auth::requireSuperAdmin();
        require_once __DIR__ . '/../../views/brands/create.php';
    }

    public static function store(): void {
        Auth::requireSuperAdmin();

        if (!Security::verifyCsrf()) {
            Flash::error("Invalid CSRF token.");
            header('Location: ' . BASE_URL . '/brands/create');
            exit;
        }

        $brandName = trim($_POST['brand_name'] ?? '');
        $companyName = trim($_POST['company_name'] ?? '');

        if (empty($brandName) || empty($companyName)) {
            Flash::error("Brand Name and Registered Company Name are required.");
            header('Location: ' . BASE_URL . '/brands/create');
            exit;
        }

        $logoPath = null;
        if (!empty($_FILES['logo']['name'])) {
            try {
                $logoPath = Upload::uploadLogo($_FILES['logo']);
            } catch (Exception $e) {
                Flash::error("Logo upload failed: " . $e->getMessage());
                header('Location: ' . BASE_URL . '/brands/create');
                exit;
            }
        }

        Brand::create([
            'brand_name' => $brandName,
            'company_name' => $companyName,
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'logo' => $logoPath,
            'status' => $_POST['status'] ?? 'active'
        ]);

        Flash::success("Brand '" . $brandName . "' created successfully!");
        header('Location: ' . BASE_URL . '/brands');
        exit;
    }

    public static function edit(int $id): void {
        Auth::requireLogin();
        Auth::authorizeBrandModification($id);

        $brand = Brand::find($id);
        if (!$brand) {
            Flash::error("Brand not found.");
            header('Location: ' . BASE_URL . '/brands');
            exit;
        }

        require_once __DIR__ . '/../../views/brands/edit.php';
    }

    public static function update(int $id): void {
        Auth::requireLogin();
        Auth::authorizeBrandModification($id);

        if (!Security::verifyCsrf()) {
            Flash::error("Invalid CSRF token.");
            header('Location: ' . BASE_URL . '/brands/' . $id . '/edit');
            exit;
        }

        $brand = Brand::find($id);
        if (!$brand) {
            Flash::error("Brand not found.");
            header('Location: ' . BASE_URL . '/brands');
            exit;
        }

        if (Auth::isSuperAdmin()) {
            $brandName = trim($_POST['brand_name'] ?? '');
            $companyName = trim($_POST['company_name'] ?? '');

            if (empty($brandName) || empty($companyName)) {
                Flash::error("Brand Name and Company Name are required.");
                header('Location: ' . BASE_URL . '/brands/' . $id . '/edit');
                exit;
            }
        } else {
            // Non-super admin users cannot modify brand name or company name
            $brandName = $brand['brand_name'];
            $companyName = $brand['company_name'];
        }

        $logoPath = $brand['logo'];
        if (!empty($_FILES['logo']['name'])) {
            try {
                $logoPath = Upload::uploadLogo($_FILES['logo'], $brand['logo']);
            } catch (Exception $e) {
                Flash::error("Logo upload failed: " . $e->getMessage());
                header('Location: ' . BASE_URL . '/brands/' . $id . '/edit');
                exit;
            }
        }

        Brand::update($id, [
            'brand_name' => $brandName,
            'company_name' => $companyName,
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'logo' => $logoPath,
            'status' => Auth::isSuperAdmin() ? ($_POST['status'] ?? 'active') : 'active'
        ]);

        Flash::success("Brand profile and logo updated successfully!");
        header('Location: ' . BASE_URL . '/brands');
        exit;
    }
}
