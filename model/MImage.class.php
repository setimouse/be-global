<?php
/**
 * description
 *
 * Filename: LibImage.class.php
 *
 * @author liyan
 * @since 2015 1 14
 */
class MImage {

    protected $imagePathFile;
    protected $image;

    function __construct($imagePathFile) {
        DAssert::assert(extension_loaded('gd') || dl('gd'), 'gd lib not exist');
        $this->imagePathFile = $imagePathFile;
    }

    protected function getImage() {
        if (!is_resource($this->image)) {
            $imageData = file_get_contents($this->imagePathFile);
            $this->image = imagecreatefromstring($imageData);
        }
        return $this->image;
    }

    public function resize($width, $height) {
        $iw = imagesx($this->getImage());
        $ih = imagesy($this->getImage());

        $k1 = $iw / $ih;
        $k2 = $width / $height;

        if ($k1 > $k2) {
            $src_x = ($iw - $ih * $k2) / 2;
            $src_y = 0;
            $src_w = $iw - $src_x * 2;
            $src_h = $ih;
        } else {
            $src_x = 0;
            $src_y = ($ih - $iw / $k2) / 2;
            $src_w = $iw;
            $src_h = $ih - $src_y * 2;
        }

        $im = imagecreatetruecolor($width, $height);

        if (!imagecopyresampled($im, $this->getImage(), 0, 0, $src_x, $src_y, $width, $height, $src_w, $src_h)) {
            throw new Exception("resize image fail", 1);
        }

        $this->image = $im;
    }

    public function saveTo($path, $quality = 75) {
        imagejpeg($this->getImage(), $path, $quality);
    }

    public function display() {
        header("Content-type: image/jpeg");
        imagejpeg($this->getImage());
    }

}

