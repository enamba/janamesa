<?php
/**
 * Description of Cache
 * @package cache
 * @author mlaug
 */
final class Yourdelivery_Cache_Html {

    static public function exists($cacheId){
        $cacheDir = APPLICATION_PATH . '/../public/cache/';
        return file_exists($cacheDir . $cacheId);
    }

    static public function load($cacheId){
        $cacheDir = APPLICATION_PATH . '/../public/cache/';
        return file_get_contents($cacheDir . $cacheId);
    }

    static public function save($cacheId,$content){
        $cacheDir = APPLICATION_PATH . '/../public/cache/';
        $cache = $cacheDir . $cacheId;
        if (self::exists($cacheId)){
            unlink($cache);
        }
        $fp = fopen($cache, 'a+');
        fwrite($fp,$content);
        fclose($fp);
    }

}
?>
