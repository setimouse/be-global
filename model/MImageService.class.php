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

    public static function fetchImage($imgUrl) {
        println('fetching image:'.$imgUrl);
        $curl = MCurl::curlGetRequest($imgUrl);
        $curl->setUseProxy(true);
        $imageData = $curl->sendRequest();

        $hash = md5($imageData);
        $filepath = self::getImagePath($hash);
        $ret = self::writeImage($filepath, $imageData);
        if (!$ret) {
            throw new Exception('save image fail', 101);
        }
        return $hash;
    }

    public static function writeImage($filepath, $imageData) {
        $path = dirname($filepath);
        if (!file_exists($path)) {
            mkdir($path, true);
            chmod($path, 0755);
        }
        $ret = file_put_contents($filepath, $imageData);
        return $ret;
    }

    public static function getImagePath($hash) {
        $path = static::imageRoot();
        $path.= substr($hash, 0, 2);
        $filepath = $path.'/'.$hash;
        return $filepath;
    }

    abstract protected static function imageRoot();

}
