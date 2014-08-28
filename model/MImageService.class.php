<?php
/**
 *
 *
 * Filename: MImageService.class.php
 *
 * @author liyan
 * @since 2014 7 21
 */
abstract class MImageService {

    public static function writeImage($filepath, $imageData) {
        $path = dirname($filepath);
        if (!file_exists($path)) {
            mkdir($path, true);
            chmod($path, 0755);
        }
        $ret = file_put_contents($filepath, $imageData);
        return $ret;
    }

    protected static function imageHashPath($hash) {
        $path = substr($hash, 0, 2);
        $filepath = $path.'/'.$hash;
        return $filepath;
    }

    public static function getImagePath($hash) {
        $path = static::imageRoot();
        $filepath = $path.self::imageHashPath($hash);
        return $filepath;
    }

    public static function getImageUrl($hash) {
        $urlprefix = static::imageUrlPrefix();
        $url = $urlprefix.self::imageHashPath($hash);
        return $url;
    }

    abstract protected static function imageRoot();

    abstract protected static function imageUrlPrefix();

}
