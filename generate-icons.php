<?php

$src = __DIR__ . '/public/logo.png';
$img = imagecreatefrompng($src);
$w   = imagesx($img);
$h   = imagesy($img);

foreach ([192, 512] as $size) {
    $new = imagecreatetruecolor($size, $size);
    imagesavealpha($new, true);
    $transparent = imagecolorallocatealpha($new, 0, 0, 0, 127);
    imagefill($new, 0, 0, $transparent);
    imagecopyresampled($new, $img, 0, 0, 0, 0, $size, $size, $w, $h);
    imagepng($new, __DIR__ . "/public/icon-{$size}.png");
    echo "icon-{$size}.png generado\n";
}

echo "Listo!\n";