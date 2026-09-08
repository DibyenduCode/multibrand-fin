<?php
$size = isset($_GET['size']) ? (int)$_GET['size'] : 192;
if ($size <= 0 || $size > 1024) $size = 192;

if (extension_loaded('gd')) {
    header('Content-Type: image/png');
    $im = imagecreatetruecolor($size, $size);
    
    // Gradient dark blue background
    $bg = imagecolorallocate($im, 12, 74, 110); // #0c4a6e
    $white = imagecolorallocate($im, 255, 255, 255);
    $sky = imagecolorallocate($im, 56, 189, 248);
    
    imagefill($im, 0, 0, $bg);
    
    // Draw outer rounded frame
    imagesetthickness($im, max(2, (int)($size / 30)));
    imagerectangle($im, (int)($size * 0.1), (int)($size * 0.1), (int)($size * 0.9), (int)($size * 0.9), $sky);
    
    // Draw string
    $font = 5;
    $str = "MB";
    $px = (imagesx($im) - 9 * strlen($str)) / 2;
    $py = (imagesy($im) - 16) / 2;
    imagestring($im, $font, (int)$px, (int)$py, $str, $white);
    
    imagepng($im);
    imagedestroy($im);
    exit;
}

// Fallback SVG output
header('Content-Type: image/svg+xml');
echo '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'" viewBox="0 0 512 512">
  <rect width="512" height="512" rx="100" fill="#0c4a6e"/>
  <path d="M120 380V180L256 100L392 180V380H330V260H182V380H120Z" fill="#38bdf8"/>
  <circle cx="256" cy="200" r="30" fill="#ffffff"/>
  <text x="256" y="440" font-family="sans-serif" font-weight="bold" font-size="70" fill="#ffffff" text-anchor="middle">MB FIN</text>
</svg>';
