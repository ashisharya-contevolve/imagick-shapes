<?php
namespace Ashisharya\Imagemagic;

use Imagick;
use ImagickDraw;
use ImagickPixel;

class ShapeGenerator
{
    protected Imagick $image;
    protected ImagickDraw $draw;

    public function __construct(
        int $width = 800,
        int $height = 800,
        string $bg = 'white',
        string $format = 'png'
    ) {
        $this->image = new Imagick();
        $this->image->newImage($width, $height, new ImagickPixel($bg));
        $this->image->setImageFormat($format);

        $this->draw = new ImagickDraw();
     }

     public function drawPolygon(
    array $inpoints,
    string $ppath,
    string $fill = 'lightblue',
    string $stroke = 'black',
    int $scale = 20
): string {
    try {
        $this->draw->setFillColor(new ImagickPixel($fill));
        $this->draw->setStrokeColor(new ImagickPixel($stroke));
        $this->draw->setStrokeWidth(2);

        $width  = $this->image->getImageWidth();
        $height = $this->image->getImageHeight();
        $offsetX = $width / 2;
        $offsetY = $height / 2;

        // --- Map original points once
        $polyPoints = [];
        foreach ($inpoints as $p) {
            $polyPoints[] = [
                'x' => $p['x'] * $scale + $offsetX,
                'y' => -$p['y'] * $scale + $offsetY  // single Y-flip
            ];
        }

        // draw polygon
        if (!empty($polyPoints)) {
            $this->draw->polygon($polyPoints);
            $this->image->drawImage($this->draw);
        }

        // --- Calculate edge lengths + annotate
        $numPoints = count($polyPoints);
        $labelDraw = new ImagickDraw();
        $labelDraw->setFillColor(new ImagickPixel('black'));
        $labelDraw->setFontSize(13);

        $dotDraw = new ImagickDraw();
        $dotDraw->setFillColor(new ImagickPixel('red'));

        for ($i = 0; $i < $numPoints; $i++) {
            $p1 = $polyPoints[$i];
            $p2 = $polyPoints[($i + 1) % $numPoints];

            // length (using *original* coords, not flipped)
            $orig1 = $inpoints[$i];
            $orig2 = $inpoints[($i + 1) % $numPoints];
            $length = sqrt(
                pow($orig2['x'] - $orig1['x'], 2) +
                pow($orig2['y'] - $orig1['y'], 2)
            );

            // midpoint
            $midX = ($p1['x'] + $p2['x']) / 2;
            $midY = ($p1['y'] + $p2['y']) / 2;

            // angle for text
            $angle = rad2deg(atan2($p2['y'] - $p1['y'], $p2['x'] - $p1['x']));

            // normalize angle so text is never upside down
            if ($angle > 90) {
                $angle -= 180;
            } elseif ($angle < -90) {
                $angle += 180;
            }

            // --- label (rotated length)
            $labelDraw->push();
            $labelDraw->translate($midX, $midY);
            $labelDraw->rotate($angle);
            $labelDraw->annotation(0, 0, number_format($length, 2));
            $labelDraw->pop();

            // vertex marker
           // $dotDraw->circle($p1['x'], $p1['y'], $p1['x'] + 1, $p1['y'] + 1);
        }

        $this->image->drawImage($labelDraw);
        //$this->image->drawImage($dotDraw);

        $this->image->writeImage($ppath);

        $labelDraw->destroy();
       // $dotDraw->destroy();

        return $ppath;
    } catch (\Exception $e) {
        throw new \Exception("ShapeGenerator::drawPolygon failed: " . $e->getMessage());
    }
}




    
 }
