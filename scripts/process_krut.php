<?php
$srcPath = 'C:/Users/THC-PC/.gemini/antigravity/brain/2d91bd96-6b7b-4165-9154-04794d641d53/.user_uploaded/media_1795425907448_f5c1306d.jpg';
$outPng = __DIR__ . '/../public/images/krut.png';
$outOfficialPng = __DIR__ . '/../public/images/krut_official.png';

$im = imagecreatefromjpeg($srcPath);
if (!$im) {
    die("Failed to open source image\n");
}

$w = imagesx($im);
$h = imagesy($im);
echo "Source: {$w}x{$h}\n";

// Only clear the isolated scanner dust speck at (31..33, 21..24)
for ($y = 21; $y <= 24; $y++) {
    for ($x = 31; $x <= 33; $x++) {
        imagesetpixel($im, $x, $y, imagecolorallocate($im, 255, 255, 255));
    }
}
echo "Cleared isolated dust speck at (32, 22-23).\n";

$target = imagecreatetruecolor($w, $h);
imagealphablending($target, false);
imagesavealpha($target, true);

$minX = $w;
$maxX = 0;
$minY = $h;
$maxY = 0;

for ($y = 0; $y < $h; $y++) {
    for ($x = 0; $x < $w; $x++) {
        $rgb = imagecolorat($im, $x, $y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        $lum = 0.299 * $r + 0.587 * $g + 0.114 * $b;

        // Anti-aliasing with pure black lines and variable alpha:
        // lum >= 238 -> 100% transparent
        // lum <= 50  -> 100% opaque black
        // lum between 50 and 238 -> smooth alpha
        if ($lum >= 238) {
            $color = imagecolorallocatealpha($target, 0, 0, 0, 127);
        } elseif ($lum <= 50) {
            $color = imagecolorallocatealpha($target, 0, 0, 0, 0);
            if ($x < $minX) $minX = $x;
            if ($x > $maxX) $maxX = $x;
            if ($y < $minY) $minY = $y;
            if ($y > $maxY) $maxY = $y;
        } else {
            $alpha = (int)round((($lum - 50) / (238 - 50)) * 127);
            $alpha = max(0, min(127, $alpha));
            $color = imagecolorallocatealpha($target, 0, 0, 0, $alpha);
            if ($alpha < 100) {
                if ($x < $minX) $minX = $x;
                if ($x > $maxX) $maxX = $x;
                if ($y < $minY) $minY = $y;
                if ($y > $maxY) $maxY = $y;
            }
        }
        imagesetpixel($target, $x, $y, $color);
    }
}

echo "Garuda Bounding Box: minX=$minX, maxX=$maxX, minY=$minY, maxY=$maxY\n";
$boxW = $maxX - $minX + 1;
$boxH = $maxY - $minY + 1;
echo "Box Size: {$boxW}x{$boxH}\n";

// Add small margin around bounding box (10px)
$pad = 10;
$cropW = $boxW + ($pad * 2);
$cropH = $boxH + ($pad * 2);

$cropped = imagecreatetruecolor($cropW, $cropH);
imagealphablending($cropped, false);
imagesavealpha($cropped, true);

$trans = imagecolorallocatealpha($cropped, 0, 0, 0, 127);
imagefilledrectangle($cropped, 0, 0, $cropW, $cropH, $trans);

imagecopy($cropped, $target, $pad, $pad, $minX, $minY, $boxW, $boxH);

// Save cropped transparent PNG
imagepng($cropped, $outPng, 9);
imagepng($cropped, $outOfficialPng, 9);
echo "Saved successfully to $outPng and $outOfficialPng\n";

// Also copy to deploy paths if directories exist
$deployTargets = [
    __DIR__ . '/../_deploy_moph/it-system/public/images/krut.png',
    __DIR__ . '/../_deploy_moph/it-system/public/images/krut_official.png',
    __DIR__ . '/../_deploy_moph/it-system/images/krut.png',
    __DIR__ . '/../_deploy_moph/it-system/images/krut_official.png',
];

foreach ($deployTargets as $dst) {
    $dir = dirname($dst);
    if (is_dir($dir)) {
        copy($outPng, $dst);
        echo "Copied to $dst\n";
    }
}

imagedestroy($im);
imagedestroy($target);
imagedestroy($cropped);
