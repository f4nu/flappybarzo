<?php
$width = $_GET['width'];
$height = $_GET['height'];
$x = $_GET['x'];
$y = $_GET['y'];

// Create image instances
$src = imagecreatefromgif('images/background.gif');
$dest = imagecreatetruecolor($width, $height);
imagefilledrectangle($dest, 0, 0, $width, $height, 0xFEFEFE);
imagecolortransparent($dest, 0xFEFEFE);
// Copy
imagecopy($dest, $src, 0, 0, $x, $y, $width, $height);

// Output and free from memory
header('Content-Type: image/gif');
imagegif($dest);

imagedestroy($dest);
imagedestroy($src);