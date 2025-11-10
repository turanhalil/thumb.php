<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error.log');

$cacheDir = __DIR__ . '/cache/';
$fontFile = __DIR__ . '/Exo-Black-Italic.otf';
$cacheLifetime = 60 * 60 * 24 * 7;

if (!file_exists($cacheDir)) mkdir($cacheDir, 0755, true);

$htaccessFile = $cacheDir . '.htaccess';
if (!file_exists($htaccessFile)) {
    file_put_contents($htaccessFile, "Options -Indexes\n<FilesMatch \"\\.(php)$\">\nDeny from all\n</FilesMatch>");
}

$src  = $_GET['src'] ?? '';
$w    = intval($_GET['w'] ?? 0);
$h    = intval($_GET['h'] ?? 0);
$q    = intval($_GET['q'] ?? 85);
$text = $_GET['text'] ?? '';
$pos  = strtolower($_GET['pos'] ?? 'br');
$opacity = intval($_GET['opacity'] ?? 60);
$colorHex = $_GET['color'] ?? 'FFFFFF';
$fontSizeParam = intval($_GET['size'] ?? 0);

if (!$src) exit('src parametresi yok.');

$srcPath = __DIR__ . '/' . ltrim($src, '/');
$realPath = realpath($srcPath);
if (!$realPath) exit('Dosya bulunamadı.');

$allowedDirs = [__DIR__ . '/upload', __DIR__ . '/uploads', __DIR__ . '/images'];
$allowed = false;
foreach ($allowedDirs as $dir) {
    $dir = realpath($dir);
    if ($dir && str_starts_with($realPath, $dir)) {
        $allowed = true;
        break;
    }
}
if (!$allowed) exit('Geçersiz dizin.');

$imgInfo = @getimagesize($realPath);
if (!$imgInfo) exit('Geçersiz görsel.');
[$origW, $origH] = $imgInfo;
$mime = $imgInfo['mime'];

switch ($mime) {
    case 'image/jpeg': $srcImg = @imagecreatefromjpeg($realPath); break;
    case 'image/png':  $srcImg = @imagecreatefrompng($realPath); break;
    case 'image/webp': $srcImg = @imagecreatefromwebp($realPath); break;
    case 'image/avif': $srcImg = @imagecreatefromavif($realPath); break;
    default: exit('Desteklenmeyen format.');
}

if (!$w && !$h) { $w = $origW; $h = $origH; }
elseif (!$h) $h = intval($origH * ($w / $origW));
elseif (!$w) $w = intval($origW * ($h / $origH));

$srcRatio = $origW / $origH;
$dstRatio = $w / $h;

if ($srcRatio > $dstRatio) {
    $newWidth = intval($origH * $dstRatio);
    $newHeight = $origH;
    $srcX = intval(($origW - $newWidth) / 2);
    $srcY = 0;
} else {
    $newWidth = $origW;
    $newHeight = intval($origW / $dstRatio);
    $srcX = 0;
    $srcY = intval(($origH - $newHeight) / 2);
}

$dstImg = imagecreatetruecolor($w, $h);
if ($mime === 'image/png' || $mime === 'image/webp' || $mime === 'image/avif') {
    imagealphablending($dstImg, false);
    imagesavealpha($dstImg, true);
    $transparent = imagecolorallocatealpha($dstImg, 0, 0, 0, 127);
    imagefilledrectangle($dstImg, 0, 0, $w, $h, $transparent);
}
imagecopyresampled($dstImg, $srcImg, 0, 0, $srcX, $srcY, $w, $h, $newWidth, $newHeight);

if ($text && file_exists($fontFile)) {
    $fontSize = $fontSizeParam > 0 ? (int)$fontSizeParam : max(10, (int)($w / 20));

    $bbox = imagettfbbox($fontSize, 0, $fontFile, $text);
    $tw = abs($bbox[2] - $bbox[0]);
    $th = abs($bbox[5] - $bbox[1]);

    while ($tw > $w - 20 && $fontSize > 5) {
        $fontSize--;
        $bbox = imagettfbbox($fontSize, 0, $fontFile, $text);
        $tw = abs($bbox[2] - $bbox[0]);
        $th = abs($bbox[5] - $bbox[1]);
    }

    $x = (int)(($w - $tw) / 2);
    $y = (int)(($h + $th) / 2);

    $colorHex = ltrim($colorHex, '#');
    if (strlen($colorHex) === 3) {
        $colorHex = preg_replace('/(.)/', '$1$1', $colorHex);
    }
    $r = hexdec(substr($colorHex, 0, 2));
    $g = hexdec(substr($colorHex, 2, 2));
    $b = hexdec(substr($colorHex, 4, 2));

    $alpha = 127 - round(($opacity / 100) * 127);
    $color = imagecolorallocatealpha($dstImg, $r, $g, $b, $alpha);

    $bbox = imagettfbbox($fontSize, 0, $fontFile, $text);
    $tw = abs($bbox[2] - $bbox[0]);
    $th = abs($bbox[5] - $bbox[1]);

    switch ($pos) {
        case 'tl': $x = 10; $y = $th + 10; break;
        case 'tr': $x = $w - $tw - 10; $y = $th + 10; break;
        case 'bl': $x = 10; $y = $h - 10; break;
        case 'br': $x = $w - $tw - 10; $y = $h - 10; break;
        case 't':  $x = ($w - $tw) / 2; $y = $th + 10; break;
        case 'b':  $x = ($w - $tw) / 2; $y = $h - 10; break;
        case 'c':  $x = ($w - $tw) / 2; $y = ($h + $th) / 2; break;
        default:   $x = $w - $tw - 10; $y = $h - 10;
    }

    imagettftext($dstImg, $fontSize, 0, $x, $y, $color, $fontFile, $text);
}

$cacheKey = md5($src.$w.$h.$q.$text.$pos.$opacity.$colorHex.$fontSizeParam) . '.webp';
$cacheFile = $cacheDir . $cacheKey;

imagewebp($dstImg, $cacheFile, $q);
header('Content-Type: image/webp');
readfile($cacheFile);

foreach (glob($cacheDir . '*') as $file) {
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    if (in_array($ext, ['webp','php'])) {
        if (filemtime($file) < time() - $cacheLifetime) unlink($file);
    }
}

imagedestroy($srcImg);
imagedestroy($dstImg);
?>
