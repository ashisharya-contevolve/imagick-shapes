<?php
namespace Ashisharya\Imagemagic;

use Imagick;
use ImagickDraw;
use ImagickPixel;

class ShapeGenerator
{
   
public static function drawPolygon(
    array $inpoints,
    string $ppath,
    int $width = 1000,
    int $height = 1000,
    string $bg = 'white',
    string $fill = 'lightblue',
    string $stroke = 'black'
): string {
    try {
        // --- initialize Imagick image
        $image = new \Imagick();
        $image->newImage($width, $height, new \ImagickPixel($bg));
        $image->setImageFormat('jpg');

        // --- initialize drawing object
        $draw = new \ImagickDraw();
        $draw->setFillColor(new \ImagickPixel($fill));
        $draw->setStrokeColor(new \ImagickPixel($stroke));
        $draw->setStrokeWidth(2);

        // --- calculate polygon points
        $scale   = 20;
        $offsetX = $width / 2;
        $offsetY = $height / 2;

        $polyPoints = [];
        foreach ($inpoints as $p) {
            $px = $p['x'] * $scale + $offsetX;
            $py = $offsetY - $p['y'] * $scale;
            $polyPoints[] = ['x' => $px, 'y' => $py];
        }

        // --- draw polygon
        if (!empty($polyPoints)) {
            $draw->polygon($polyPoints);
            $image->drawImage($draw);
        }

        // --- save the image
        $image->writeImage($ppath);

        // --- clear resources
        $draw->destroy();
        $image->destroy();

        return $ppath;

    } catch (\Exception $e) {
        // fallback so no "undefined $image" warning
        throw new \Exception("ShapeGenerator::drawPolygon failed: " . $e->getMessage());
    }
}




   
}
