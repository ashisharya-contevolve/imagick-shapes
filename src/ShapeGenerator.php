<?php
namespace Ashisharya\Imagemagic;

use Imagick;
use ImagickDraw;
use ImagickPixel;

class ShapeGenerator
{
    protected Imagick $image;
    protected ImagickDraw $draw;

    public function __construct(int $width = 800, int $height = 800, string $bg = 'white')
    {
        $this->image = new Imagick();
        $this->image->newImage($width, $height, new ImagickPixel($bg));
        $this->image->setImageFormat('png');

        $this->draw = new ImagickDraw();
    }

    public function polygon(array $points, string $fill = 'lightblue', string $stroke = 'black'): self
    {
        $this->draw->setFillColor(new ImagickPixel($fill));
        $this->draw->setStrokeColor(new ImagickPixel($stroke));
        $this->draw->setStrokeWidth(2);
        $this->draw->polygon($points);

        return $this;
    }
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


    public function drawPolygonWithLengths(
        array $points,
        string $savePath,
        int $scale = 20,
        int $offsetX = 500,
        int $offsetY = 500,
        int $width = 1000,
        int $height = 1000,
        string $fillColor = 'lightblue'
    ): string {
        // --- create blank image
        $image = new Imagick();
        $image->newImage($width, $height, new ImagickPixel('white'));
        $image->setImageFormat('jpg');

        $draw = new ImagickDraw();

        // --- fill + stroke settings
        $draw->setFillColor(new ImagickPixel($fillColor));
        $draw->setStrokeColor('black');
        $draw->setStrokeWidth(2);

        // --- draw filled polygon
        $polyPoints = [];
        foreach ($points as $p) {
            $px = $p['x'] * $scale + $offsetX;
            $py = $offsetY - $p['y'] * $scale;
            $polyPoints[] = ['x' => $px, 'y' => $py];
        }
        $draw->polygon($polyPoints);

        // --- annotate edge lengths
        $pointCount = count($points);
        for ($i = 0; $i < $pointCount; $i++) {
            $next = ($i + 1) % $pointCount;

            $x1 = $points[$i]['x'] * $scale + $offsetX;
            $y1 = $offsetY - $points[$i]['y'] * $scale;
            $x2 = $points[$next]['x'] * $scale + $offsetX;
            $y2 = $offsetY - $points[$next]['y'] * $scale;

            // calculate edge length
            $length = sqrt(
                pow($points[$next]['x'] - $points[$i]['x'], 2) +
                pow($points[$next]['y'] - $points[$i]['y'], 2)
            );
            $lengthTxt = number_format($length, 2);

            // midpoint
            $mx = ($x1 + $x2) / 2;
            $my = ($y1 + $y2) / 2;

            // offset text perpendicular above the line
            $dx = $x2 - $x1;
            $dy = $y2 - $y1;
            $len = sqrt($dx * $dx + $dy * $dy);
            if ($len > 0) {
                $nx = -$dy / $len;
                $ny = $dx / $len;
                $offset = 10;
                $mx += $nx * $offset;
                $my += $ny * $offset;
            }

            $draw->setFillColor('blue');
            $draw->setFontSize(10);
            $draw->annotation($mx, $my, $lengthTxt);
        }

        // --- draw red circles for points
        foreach ($points as $p) {
            $cx = $p['x'] * $scale + $offsetX;
            $cy = $offsetY - $p['y'] * $scale;

            $draw->setFillColor('red');
            $draw->circle($cx, $cy, $cx + 3, $cy + 3);
        }

        // render & save
        $image->drawImage($draw);
        $image->writeImage($savePath);

        return $savePath;
    }

    public function circle(int $cx, int $cy, int $radius, string $fill = 'red'): self
    {
        $this->draw->setFillColor(new ImagickPixel($fill));
        $this->draw->circle($cx, $cy, $cx + $radius, $cy);

        return $this;
    }

    public function rectangle(int $x1, int $y1, int $x2, int $y2, string $fill = 'grey'): self
    {
        $this->draw->setFillColor(new ImagickPixel($fill));
        $this->draw->rectangle($x1, $y1, $x2, $y2);

        return $this;
    }

    public function text(int $x, int $y, string $label, string $color = 'black', int $size = 14): self
    {
        $this->draw->setFillColor(new ImagickPixel($color));
        $this->draw->setFontSize($size);
        $this->draw->annotation($x, $y, $label);

        return $this;
    }

    public function save(string $path): string
    {
        $this->image->drawImage($this->draw);
        $this->image->writeImage($path);

        return $path;
    }

    public function output(): string
    {
        $this->image->drawImage($this->draw);
        return $this->image->getImageBlob();
    }
}
