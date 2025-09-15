<?php
namespace Ashisharya\Imagemagic; 
use Imagick; 
use ImagickDraw; 
use ImagickPixel; 
class ShapeGenerator { 
  
   
   public function __construct(int $width = 800, int $height = 800, string $bg = 'white') {
      $this->image = new Imagick(); 
      $this->image->newImage($width, $height, new ImagickPixel($bg));
      $this->image->setImageFormat('png'); 
      $this->draw = new ImagickDraw(); 
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
        //$image = new Imagick();
        $this->image->newImage($width, $height, new \ImagickPixel($bg));
        $this->image->setImageFormat('jpg');

        // --- initialize drawing object
         $this->draw = new ImagickDraw();
        $this->draw->setFillColor(new ImagickPixel($fill));
       $this->draw->setStrokeColor(new ImagickPixel($stroke));
        $this->draw->setStrokeWidth(2);

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
            $this->draw->polygon($polyPoints);
            $this->draw->drawImage($this->draw);
        }

        // --- save the image
        $this->image->writeImage($ppath);

        // --- clear resources
       $this->draw->destroy();
        $this->image->destroy();

        return $ppath;

    } catch (\Exception $e) {
        // fallback so no "undefined $image" warning
        throw new \Exception("ShapeGenerator::drawPolygon failed: " . $e->getMessage());
    }
}




   
}
