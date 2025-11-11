<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error.log');

$cacheDir = __DIR__ . '/thumb_cache/';
$allowedDirs = [
    realpath(__DIR__ . '/upload'),
    realpath(__DIR__ . '/uploads'),
    realpath(__DIR__ . '/images'),
];

if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
    file_put_contents($cacheDir . '/index.html', '');
    $ht = "Options -Indexes\n<FilesMatch '\.(php|phtml|php3|php4|php5|phps)$'>\nDeny from all\n</FilesMatch>";
    file_put_contents($cacheDir . '/.htaccess', $ht);
}

$src = $_GET['src'] ?? '';
$w = isset($_GET['w']) ? (int)$_GET['w'] : 0;
$h = isset($_GET['h']) ? (int)$_GET['h'] : 0;
$q = isset($_GET['q']) ? (int)$_GET['q'] : 90;
$text = $_GET['text'] ?? '';
$pos = $_GET['pos'] ?? 'br';
$opacity = isset($_GET['opacity']) ? (float)$_GET['opacity'] : 60;
$fontSizeParam = isset($_GET['size']) ? (int)$_GET['size'] : 0;
$colorHex = $_GET['color'] ?? 'ffffff';
$objectFit = strtolower($_GET['of'] ?? 'cover');
$cacheDays = isset($_GET['cache']) ? max(0, (int)$_GET['cache']) : 3;
$cacheTime = $cacheDays * 86400;
$fontFile = __DIR__ . '/Exo-Black-Italic.otf';

$srcPath = ltrim($src, '/');
$fullSrc = realpath(__DIR__ . '/' . $srcPath);
if (!$fullSrc) {
    $fullSrc = realpath($src);
}
if (!$fullSrc || !file_exists($fullSrc)) {
    http_response_code(404);
    exit('no image');
}
$ok = false;
foreach ($allowedDirs as $d) {
    if ($d && str_starts_with($fullSrc, $d)) { $ok = true; break; }
}
if (!$ok) {
    http_response_code(403);
    exit('forbidden');
}

$uniq = md5($fullSrc . '|' . $w . '|' . $h . '|' . $q . '|' . $text . '|' . $pos . '|' . $opacity . '|' . $fontSizeParam . '|' . $colorHex . '|' . $objectFit . '|' . $cacheDays);
$cacheFile = $cacheDir . $uniq . '.webp';

if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
    header('Content-Type: image/webp');
    readfile($cacheFile);
    exit;
}

$ext = strtolower(pathinfo($fullSrc, PATHINFO_EXTENSION));
switch ($ext) {
    case 'jpg':
    case 'jpeg':
        $srcImg = @imagecreatefromjpeg($fullSrc);
        break;
    case 'png':
        $srcImg = @imagecreatefrompng($fullSrc);
        break;
    case 'gif':
        $srcImg = @imagecreatefromgif($fullSrc);
        break;
    case 'webp':
        $srcImg = @imagecreatefromwebp($fullSrc);
        break;
    case 'avif':
        if (function_exists('imagecreatefromavif')) { $srcImg = @imagecreatefromavif($fullSrc); break; }
    default:
        exit('unsupported');
}
if (!$srcImg) exit('load error');

$srcW = imagesx($srcImg);
$srcH = imagesy($srcImg);

if ($w <= 0 && $h <= 0) { $w = $srcW; $h = $srcH; }
elseif ($w <= 0) { $w = (int)($srcW * ($h / $srcH)); }
elseif ($h <= 0) { $h = (int)($srcH * ($w / $srcW)); }

$dstImg = imagecreatetruecolor($w, $h);
imagealphablending($dstImg, false);
imagesavealpha($dstImg, true);
$transparent = imagecolorallocatealpha($dstImg, 0, 0, 0, 127);
imagefilledrectangle($dstImg, 0, 0, $w, $h, $transparent);

$srcX = $srcY = 0;
$newW = $srcW;
$newH = $srcH;
$srcRatio = $srcW / $srcH;
$dstRatio = $w / $h;

switch ($objectFit) {
    case 'cover':
        if ($srcRatio > $dstRatio) {
            $newW = (int)($srcH * $dstRatio);
            $srcX = (int)(($srcW - $newW) / 2);
        } else {
            $newH = (int)($srcW / $dstRatio);
            $srcY = (int)(($srcH - $newH) / 2);
        }
        break;
    case 'contain':
        if ($srcRatio > $dstRatio) {
            $scale = $w / $srcW;
        } else {
            $scale = $h / $srcH;
        }
        $drawW = (int)($srcW * $scale);
        $drawH = (int)($srcH * $scale);
        $offX = (int)(($w - $drawW) / 2);
        $offY = (int)(($h - $drawH) / 2);
        imagecopyresampled($dstImg, $srcImg, $offX, $offY, 0, 0, $drawW, $drawH, $srcW, $srcH);
        break;
    case 'fill':
        imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $w, $h, $srcW, $srcH);
        break;
    case 'none':
        $offX = (int)(($w - $srcW) / 2);
        $offY = (int)(($h - $srcH) / 2);
        if ($offX < 0) $offX = 0;
        if ($offY < 0) $offY = 0;
        imagecopy($dstImg, $srcImg, $offX, $offY, 0, 0, min($srcW, $w), min($srcH, $h));
        break;
    case 'scale-down':
        $scaleW = $w / $srcW;
        $scaleH = $h / $srcH;
        $scale = min(1, min($scaleW, $scaleH));
        $drawW = (int)($srcW * $scale);
        $drawH = (int)($srcH * $scale);
        $offX = (int)(($w - $drawW) / 2);
        $offY = (int)(($h - $drawH) / 2);
        imagecopyresampled($dstImg, $srcImg, $offX, $offY, 0, 0, $drawW, $drawH, $srcW, $srcH);
        break;
    default:
        // default cover behavior
        if ($srcRatio > $dstRatio) {
            $newW = (int)($srcH * $dstRatio);
            $srcX = (int)(($srcW - $newW) / 2);
        } else {
            $newH = (int)($srcW / $dstRatio);
            $srcY = (int)(($srcH - $newH) / 2);
        }
        break;
}

if (!in_array($objectFit, ['contain','fill','none','scale-down'])) {
    imagecopyresampled($dstImg, $srcImg, 0, 0, $srcX, $srcY, $w, $h, $newW, $newH);
}

$tl = $tr = $br = $bl = 0;
if (isset($_GET['br']) && strlen(trim($_GET['br'])) > 0) {
    $raw = trim($_GET['br']);
    $raw = str_replace(',', ' ', $raw);
    $parts = preg_split('/\s+/', $raw, -1, PREG_SPLIT_NO_EMPTY);
    $vals = [];
    foreach ($parts as $p) {
        if (preg_match('/^\d+(px)?$/i', $p)) {
            $vals[] = (int)$p;
        }
    }
    if (count($vals) === 1) {
        $tl = $tr = $br = $bl = $vals[0];
    } elseif (count($vals) === 2) {
        $tl = $br = $vals[0];
        $tr = $bl = $vals[1];
    } elseif (count($vals) === 3) {
        $tl = $vals[0];
        $tr = $bl = $vals[1];
        $br = $vals[2];
    } elseif (count($vals) >= 4) {
        list($tl, $tr, $br, $bl) = array_slice($vals, 0, 4);
    }
    $maxR = (int)(min($w, $h) / 2);
    $tl = max(0, min($tl, $maxR));
    $tr = max(0, min($tr, $maxR));
    $br = max(0, min($br, $maxR));
    $bl = max(0, min($bl, $maxR));
}

if ($tl || $tr || $br || $bl) {
    $mask = imagecreatetruecolor($w, $h);
    imagealphablending($mask, false);
    imagesavealpha($mask, true);
    $clear = imagecolorallocatealpha($mask, 0, 0, 0, 127);
    imagefilledrectangle($mask, 0, 0, $w, $h, $clear);
    $solid = imagecolorallocatealpha($mask, 0, 0, 0, 0);

    imagefilledrectangle($mask, $tl, 0, $w - $tr, $h, $solid);
    imagefilledrectangle($mask, 0, $tl, $w, $h - $bl, $solid);

    if ($tl) imagefilledellipse($mask, $tl, $tl, $tl * 2, $tl * 2, $solid);
    if ($tr) imagefilledellipse($mask, $w - $tr, $tr, $tr * 2, $tr * 2, $solid);
    if ($br) imagefilledellipse($mask, $w - $br, $h - $br, $br * 2, $br * 2, $solid);
    if ($bl) imagefilledellipse($mask, $bl, $h - $bl, $bl * 2, $bl * 2, $solid);

    imagealphablending($dstImg, false);
    imagesavealpha($dstImg, true);
    for ($x = 0; $x < $w; $x++) {
        for ($y = 0; $y < $h; $y++) {
            $m = imagecolorat($mask, $x, $y);
            $ma = ($m >> 24) & 0x7F;
            if ($ma == 127) {
                $c = imagecolorat($dstImg, $x, $y);
                $rgba = imagecolorsforindex($dstImg, $c);
                $col = imagecolorallocatealpha($dstImg, $rgba['red'], $rgba['green'], $rgba['blue'], 127);
                imagesetpixel($dstImg, $x, $y, $col);
            }
        }
    }
    imagedestroy($mask);
}

if ($text && file_exists($fontFile)) {
    $fontSize = $fontSizeParam > 0 ? (int)$fontSizeParam : max(10, (int)($w / 20));

    $c = ltrim($colorHex, '#');
    if (strlen($c) === 3) $c = $c[0].$c[0].$c[1].$c[1].$c[2].$c[2];
    $r = hexdec(substr($c, 0, 2));
    $g = hexdec(substr($c, 2, 2));
    $b = hexdec(substr($c, 4, 2));
    $alpha = (int)(127 - ($opacity / 100) * 127);

    $bbox = imagettfbbox($fontSize, 0, $fontFile, $text);
    $tw = abs($bbox[2] - $bbox[0]);
    $th = abs($bbox[5] - $bbox[1]);
    if ($tw + 20 > $w) {
        $scale = ($w - 20) / $tw;
        $fontSize = max(5, (int)($fontSize * $scale));
    }
    $bbox = imagettfbbox($fontSize, 0, $fontFile, $text);
    $tw = abs($bbox[2] - $bbox[0]);
    $th = abs($bbox[5] - $bbox[1]);

    switch ($pos) {
        case 'tl': $x = 10; $y = $th + 10; break;
        case 'tr': $x = $w - $tw - 10; $y = $th + 10; break;
        case 'bl': $x = 10; $y = $h - 10; break;
        case 'br': $x = $w - $tw - 10; $y = $h - 10; break;
        case 't':  $x = (int)(($w - $tw) / 2); $y = $th + 10; break;
        case 'b':  $x = (int)(($w - $tw) / 2); $y = $h - 10; break;
        case 'c':  $x = (int)(($w - $tw) / 2); $y = (int)(($h + $th) / 2); break;
        default:   $x = 10; $y = $h - 10; break;
    }
    $txtCol = imagecolorallocatealpha($dstImg, $r, $g, $b, $alpha);
    imagettftext($dstImg, $fontSize, 0, (int)$x, (int)$y, $txtCol, $fontFile, $text);
}

imagewebp($dstImg, $cacheFile, $q);

header('Content-Type: image/webp');
readfile($cacheFile);

imagedestroy($srcImg);
imagedestroy($dstImg);
exit;
?>
