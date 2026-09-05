<?php
require_once __DIR__ . '/../../config/config.php';

class Upload {
    public static function uploadLogo(array $file, ?string $oldLogo = null): ?string {
        if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return $oldLogo;
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception("Invalid file type. Allowed formats: JPG, PNG, WEBP, GIF, SVG.");
        }

        if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
            throw new Exception("Logo file size exceeds 5MB maximum limit.");
        }

        $uploadDir = __DIR__ . '/../../public/uploads/logos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'logo_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Delete old logo if exists
            if ($oldLogo && file_exists(__DIR__ . '/../../public/' . $oldLogo)) {
                @unlink(__DIR__ . '/../../public/' . $oldLogo);
            }
            return 'uploads/logos/' . $filename;
        }

        return $oldLogo;
    }
}
