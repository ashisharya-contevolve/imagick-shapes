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
            // configure polygon drawing
            $this->draw->setFillColor(new ImagickPixel($fill));
            $this->draw->setStrokeColor(new ImagickPixel($stroke));
            $this->draw->setStrokeWidth(2);

            $width  = $this->image->getImageWidth();
            $height = $this->image->getImageHeight();
            $offsetX = $width / 2;
            $offsetY = $height / 2;

            // calculate polygon points (scaled to canvas)
            $polyPoints = [];
            foreach ($inpoints as $p) {
                $px = $p['x'] * $scale + $offsetX;
                $py = -$p['y'] * $scale + $offsetY; // ✅ fixed Y-axis
                $polyPoints[] = ['x' => $px, 'y' => $py];
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
                $p1 = $inpoints[$i];
                $p2 = $inpoints[($i + 1) % $numPoints];

                // length in original units
                $length = sqrt(
                    pow($p2['x'] - $p1['x'], 2) +
                    pow($p2['y'] - $p1['y'], 2)
                );

                // scaled points for drawing
                $px1 = $p1['x'] * $scale + $offsetX;
                $py1 = -$p1['y'] * $scale + $offsetY;
                $px2 = $p2['x'] * $scale + $offsetX;
                $py2 = -$p2['y'] * $scale + $offsetY;

                // midpoint
                $midX = ($px1 + $px2) / 2;
                $midY = ($py1 + $py2) / 2;

                // angle in degrees
                $angle = rad2deg(atan2($py2 - $py1, $px2 - $px1));

                // --- label (rotated length)
                $labelDraw->push(); // save state
                $labelDraw->translate($midX, $midY);
                $labelDraw->rotate($angle);
                $labelDraw->annotation(0, 0, number_format($length, 2));
                $labelDraw->pop();  // restore state

                // --- vertex marker (red circle)
                $dotDraw->circle($px1, $py1, $px1 + 3, $py1 + 3);
            }

            // apply labels and dots
            $this->image->drawImage($labelDraw);
            $this->image->drawImage($dotDraw);

            // save
            $this->image->writeImage($ppath);

            // cleanup
            $labelDraw->destroy();
            $dotDraw->destroy();

            return $ppath;
        } catch (\Exception $e) {
            throw new \Exception("ShapeGenerator::drawPolygon failed: " . $e->getMessage());
        }
    }



    
 }
