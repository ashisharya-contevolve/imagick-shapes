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
            // configure drawing
            $this->draw->setFillColor(new ImagickPixel($fill));
            $this->draw->setStrokeColor(new ImagickPixel($stroke));
            $this->draw->setStrokeWidth(2);

            $width  = $this->image->getImageWidth();
            $height = $this->image->getImageHeight();
            $offsetX = $width / 2;
            $offsetY = $height / 2;

            // calculate polygon points
            $polyPoints = [];
            foreach ($inpoints as $p) {
                $px = $p['x'] * $scale + $offsetX;
                $py = $offsetY - $p['y'] * $scale;
                $polyPoints[] = ['x' => $px, 'y' => $py];
            }

            // draw polygon
            if (!empty($polyPoints)) {
                $this->draw->polygon($polyPoints);
                $this->image->drawImage($this->draw);
            }

            // save
            $this->image->writeImage($ppath);

            return $ppath;
        } catch (\Exception $e) {
            throw new \Exception("ShapeGenerator::drawPolygon failed: " . $e->getMessage());
        }
    }

    public function __destruct()
    {
        if (isset($this->draw)) {
            $this->draw->destroy();
        }
        if (isset($this->image)) {
            $this->image->destroy();
        }
    }
}


