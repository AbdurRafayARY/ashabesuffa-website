<?php
/**
 * Ashabesuffa Foundation — Image Placeholder Generator
 * Run once, then DELETE this file.
 */

$dir = __DIR__;
if (!function_exists('imagecreatetruecolor')) {
    die("❌ GD extension is not enabled in PHP. Enable it in php.ini, then try again.\n");
}

echo "Generating images...\n";

// Colors
$green = imagecolorallocate(imagecreatetruecolor(1,1), 11, 110, 79);
$gold  = imagecolorallocate(imagecreatetruecolor(1,1), 212, 175, 55);
$white = imagecolorallocate(imagecreatetruecolor(1,1), 255, 255, 255);

// ---------- logo.png (200x200) ----------
$im = imagecreatetruecolor(200, 200);
imagesavealpha($im, true);
$transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
imagefill($im, 0, 0, $transparent);
$g = imagecolorallocate($im, 11, 110, 79);
$go = imagecolorallocate($im, 212, 175, 55);
$w = imagecolorallocate($im, 255, 255, 255);
imagefilledellipse($im, 100, 100, 190, 190, $g);
imagesetthickness($im, 3);
imageellipse($im, 100, 100, 164, 164, $go);
imagestring($im, 5, 90, 92, 'ASF', $w);
imagepng($im, $dir . '/logo.png');
imagedestroy($im);
echo "  ✓ logo.png\n";

// ---------- favicon.png (32x32) ----------
$im = imagecreatetruecolor(32, 32);
imagesavealpha($im, true);
imagefill($im, 0, 0, imagecolorallocatealpha($im, 0, 0, 0, 127));
imagefilledellipse($im, 16, 16, 30, 30, imagecolorallocate($im, 11, 110, 79));
imagepng($im, $dir . '/favicon.png');
imagedestroy($im);
echo "  ✓ favicon.png\n";

// ---------- favicon.ico (copy of favicon.png) ----------
copy($dir . '/favicon.png', $dir . '/favicon.ico');
echo "  ✓ favicon.ico\n";

// ---------- hero-bg.jpg (1920x1080) ----------
$im = imagecreatetruecolor(1920, 1080);
for ($y = 0; $y < 1080; $y++) {
    $r = (int)(6 + ($y / 1080) * 5);
    $gg = (int)(78 + ($y / 1080) * 32);
    $b = (int)(59 + ($y / 1080) * 20);
    imageline($im, 0, $y, 1920, $y, imagecolorallocate($im, $r, $gg, $b));
}
imagestring($im, 5, 880, 530, 'ASF HERO', imagecolorallocate($im, 255, 255, 255));
imagejpeg($im, $dir . '/hero-bg.jpg', 85);
imagedestroy($im);
echo "  ✓ hero-bg.jpg\n";

// ---------- page-header.jpg (1920x600) ----------
$im = imagecreatetruecolor(1920, 600);
for ($y = 0; $y < 600; $y++) {
    $r = (int)(6 + ($y / 600) * 5);
    $gg = (int)(78 + ($y / 600) * 32);
    $b = (int)(59 + ($y / 600) * 20);
    imageline($im, 0, $y, 1920, $y, imagecolorallocate($im, $r, $gg, $b));
}
imagejpeg($im, $dir . '/page-header.jpg', 85);
imagedestroy($im);
echo "  ✓ page-header.jpg\n";

// ---------- about.jpg (1000x800) ----------
$im = imagecreatetruecolor(1000, 800);
imagefill($im, 0, 0, imagecolorallocate($im, 11, 110, 79));
imagestring($im, 5, 460, 395, 'ABOUT', imagecolorallocate($im, 255, 255, 255));
imagejpeg($im, $dir . '/about.jpg', 85);
imagedestroy($im);
echo "  ✓ about.jpg\n";

// ---------- about-2.jpg ----------
$im = imagecreatetruecolor(1000, 800);
imagefill($im, 0, 0, imagecolorallocate($im, 11, 110, 79));
imagestring($im, 5, 450, 395, 'ABOUT 2', imagecolorallocate($im, 255, 255, 255));
imagejpeg($im, $dir . '/about-2.jpg', 85);
imagedestroy($im);
echo "  ✓ about-2.jpg\n";

// ---------- share-preview.jpg (1200x630) ----------
$im = imagecreatetruecolor(1200, 630);
imagefill($im, 0, 0, imagecolorallocate($im, 11, 110, 79));
imagestring($im, 5, 520, 310, 'ASF SHARE', imagecolorallocate($im, 255, 255, 255));
imagejpeg($im, $dir . '/share-preview.jpg', 85);
imagedestroy($im);
echo "  ✓ share-preview.jpg\n";

// ---------- pattern.png (512x512) ----------
$im = imagecreatetruecolor(512, 512);
imagesavealpha($im, true);
imagefill($im, 0, 0, imagecolorallocatealpha($im, 0, 0, 0, 127));
$accent = imagecolorallocatealpha($im, 212, 175, 55, 100);
for ($x = 0; $x < 512; $x += 64) {
    for ($y = 0; $y < 512; $y += 64) {
        imageellipse($im, $x, $y, 20, 20, $accent);
    }
}
imagepng($im, $dir . '/pattern.png');
imagedestroy($im);
echo "  ✓ pattern.png\n";

// ---------- placeholder.jpg (800x600) ----------
$im = imagecreatetruecolor(800, 600);
imagefill($im, 0, 0, imagecolorallocate($im, 230, 230, 230));
imagestring($im, 5, 355, 295, 'NO IMAGE', imagecolorallocate($im, 120, 120, 120));
imagejpeg($im, $dir . '/placeholder.jpg', 85);
imagedestroy($im);
echo "  ✓ placeholder.jpg\n";

echo "\n✅ Done! All images created in: $dir\n";
echo "👉 Now DELETE this file (generate-placeholders.php).\n";
