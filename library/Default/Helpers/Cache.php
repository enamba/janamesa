<?php

class Default_Helpers_Cache{
    
    /**
     * If caching is enabled by application.ini we try to
     * gather information from cache
     * @author mlaug
     * @param string $id
     * @return mixed
     */
    public static function load($id) {
        $config = Zend_Registry::get('configuration');
        if ($config->cache->use == 0) {
            return null;
        }

        $cache = null;
        try {
            $cache = Zend_Registry::get('cache');

            if (!is_object($cache)) {
                return null;
            }
        } catch (Exception $e) {
            return null;
        }

        if ($cache->test($id)) {
            return $cache->load($id);
        }
        return null;
    }

    /**
     * If caching is enabled by application.ini we try to
     * save information into cache
     * @author mlaug
     * @param string $id
     * @param mixed $value
     * @param array $tags
     * @return boolean
     */
    public static function store($id, $value, $tags=array()) {
        $config = Zend_Registry::get('configuration');
        if ($config->cache->use == 0) {
            return false;
        }

        $cache = null;
        try {
            $cache = Zend_Registry::get('cache');

            if (!is_object($cache)) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }

        //Tags are currently not supported by memcache backend
        $cache->save($value, $id);
        return true;
    }

    /**
     * If caching is enabled by application.ini we try to
     * remove information from cache
     * @author mlaug
     * @param string $id
     * @return boolean
     */
    public static function remove($id) {
        $config = Zend_Registry::get('configuration');
        if ($config->cache->use == 0) {      
            return false;
        }

        $cache = null;
        try {
            $cache = Zend_Registry::get('cache');

            if (!is_object($cache)) {               
                return false;
            }
        } catch (Exception $e) {       
            return false;
        }

        $cache->remove($id);
        return true;
    }

    /**
     * remove cache information based on given tags
     * @author mlaug
     * @param array $tags
     * @return boolean
     */
    public static function removeTag(array $tags) {
        if ($config->cache->use == 0) {
            return false;
        }
        $cache = null;
        try {
            $cache = Zend_Registry::get('cache');

            if (!is_object($cache)) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
        $cache->clean(
                Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $tags
        );
        return true;
    }
    
}