<?php
$size = isset($_GET['size']) ? (int)$_GET['size'] : 192;
if ($size <= 0 || $size > 1024) $size = 192;

$logoPath = __DIR__ . '/uploads/group_logo.png';

if (extension_loaded('gd')) {
    header('Content-Type: image/png');
    $im = imagecreatetruecolor($size, $size);
    
    // Enable alpha blending
    imagealphablending($im, true);
    imagesavealpha($im, true);

    // Dark navy background (#0f172a)
    $bg = imagecolorallocate($im, 15, 23, 42);
    imagefill($im, 0, 0, $bg);

    // Draw outer rounded accent frame line (#38bdf8)
    $sky = imagecolorallocate($im, 56, 189, 248);
    imagesetthickness($im, max(2, (int)($size / 35)));
    imagerectangle($im, (int)($size * 0.05), (int)($size * 0.05), (int)($size * 0.95), (int)($size * 0.95), $sky);

    // If company logo PNG exists, overlay it centered inside app icon
    if (file_exists($logoPath)) {
        $logo = @imagecreatefrompng($logoPath);
        if ($logo) {
            $logoW = imagesx($logo);
            $logoH = imagesy($logo);
            
            // Scale logo to fit inside icon padding
            $maxW = (int)($size * 0.75);
            $maxH = (int)($size * 0.75);
            $ratio = min($maxW / $logoW, $maxH / $logoH);
            
            $newW = (int)($logoW * $ratio);
            $newH = (int)($logoH * $ratio);
            
            $dstX = (int)(($size - $newW) / 2);
            $dstY = (int)(($size - $newH) / 2);
            
            imagecopyresampled($im, $logo, $dstX, $dstY, 0, 0, $newW, $newH, $logoW, $logoH);
            imagedestroy($logo);
        }
    } else {
        // Fallback text drawing if logo image file is missing
        $white = imagecolorallocate($im, 255, 255, 255);
        $font = 5;
        $str = "BISWAS";
        $px = (imagesx($im) - 9 * strlen($str)) / 2;
        $py = (imagesy($im) - 16) / 2;
        imagestring($im, $font, (int)$px, (int)$py, $str, $white);
    }
    
    imagepng($im);
    imagedestroy($im);
    exit;
}

// Fallback SVG output if GD library is disabled
header('Content-Type: image/svg+xml');
echo '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'" viewBox="0 0 512 512">
  <rect width="512" height="512" rx="100" fill="#0f172a"/>
  <rect x="20" y="20" width="472" height="472" rx="90" fill="none" stroke="#38bdf8" stroke-width="12"/>
  <text x="256" y="270" font-family="system-ui, -apple-system, sans-serif" font-weight="900" font-size="75" fill="#ffffff" text-anchor="middle">BISWAS</text>
  <text x="256" y="340" font-family="system-ui, -apple-system, sans-serif" font-weight="700" font-size="40" fill="#38bdf8" text-anchor="middle">COMPANY</text>
</svg>';
