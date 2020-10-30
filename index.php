<?php
function loadImage($path)
{
    return imagecreatefromstring(file_get_contents($path));
}

function printImage($path)
{
    header('Content-type: image/png');
    imagepng(loadImage($path));
}

function watermarkImage(
    string $imagePath,
    string $watermarkPath,
    int $coverPercentage = 50,
    string $position = 'centre',
    int $paddingPercentage = 0,
    string $outputFilename = null,
    bool $preserveTransparency = true
) {
    // load watermark and image
    $w = loadImage($watermarkPath);
    $i = loadImage($imagePath);

    // calculate new width and height while preserving old ratio
    $destW = round(imagesx($i) * $coverPercentage / 100);
    $destH = round(imagesy($w) / (imagesx($w) / $destW));

    // set x y location based on $position and $padding
    if ($position) {
        $x = round((imagesx($i) / 2) - ($destW / 2));
        $y = round((imagesy($i) / 2) - ($destH / 2));

        $paddingPixels = round(imagesx($i) * $paddingPercentage / 100);

        if (strpos(strtolower($position), 'left') !== false) {
            $x = round($paddingPixels);
        }

        if (strpos(strtolower($position), 'right') !== false) {
            $x = round(imagesx($i) - $destW - $paddingPixels);
        }

        if (strpos(strtolower($position), 'top') !== false) {
            $y = round($paddingPixels);
        }

        if (strpos(strtolower($position), 'bottom') !== false) {
            $y = round(imagesy($i) - $destH - $paddingPixels);
        }
    }

    // Preserve transparency
    if ($preserveTransparency === false) {
        imagealphablending($i, false);
        imagesavealpha($i, true);
    }

    // Merge images
    // imagecopy($i, $w, imagesx($i) / 5, imagesy($i) / 5, 0, 0, imagesx($w), imagesy($w));
    imagecopyresized($i, $w, $x, $y, 0, -1, $destW, $destH, imagesx($w), imagesy($w));

    // check output filename
    isset($outputFilename) ?: $outputFilename = 'watermarked-' . $imagePath;

    // output to file
    imagepng($i, $outputFilename);

    // output to screen
    // header('Content-type: image/png');
    // imagepng($i);

    // clear memory
    imagedestroy($i);

    return file_exists($outputFilename);
}


// :: Tests ::

// Test 1 with all settings
if (watermarkImage('image-1.jpg', 'logo.png', 20, 'Bottom-Right', 4, 'output.jpg', true)) {
    printImage('output.jpg');
}

// Test 2 with minimal settings
// if (watermarkImage('image-2.jpg', 'logo.png', 80)) {
//     printImage('watermarked-image-2.jpg');
// }
