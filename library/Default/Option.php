<?php

/**
 * this stores options for any model or controller
 * @author mlaug
 */
class Default_Option {

    /**
     * hash to identify any options
     * @var string
     */
    protected $_hash = null;

    /**
     * store any protected options in here
     * @var array
     */
    protected $_protected = array();

    /**
     * make use of cache
     * @var Zend_Cache_Backend_Memcached
     */
    protected $_cache = null;

    /**
     * @var Default_Option_Table
     */
    protected $_table = null;

    public function __construct(){
        try{
            $cache = Zend_Registry::get('cache');
            $this->_cache = $cache;
        }
        catch ( Zend_Exception $e ){
            $this->_cache = null;
        }
    }

    /**
     * set the hash to identify any options
     * @author mlaug
     * @param string $hash
     * @return boolean
     */
    public function setHash($hash){
        if ( is_string($hash) ){
            $this->_hash = $hash;
            return true;
        }
        return false;
    }

    /**
     * delete an option if available
     * @author mlaug
     * @param string $option
     * @return boolean
     */
    public function delete($option){
        if ( is_null($this->_hash) ){
            return false;
        }
        return $this->getTable()
                    ->remove($this->_hash, $option);
    }

    /**
     * check if an option is set
     * @author mlaug
     * @param string $option
     * @return boolean
     */
    public function has($option){
        if ( is_null($this->_hash) ){
            return false;
        }
        $value = $this->getTable()
                      ->get($this->_hash, $option);
        if ( is_null($value) ){
            return false;
        }
        return true;
    }

    /**
     * get an option field
     * @author mlaug
     * @param string $option
     * @return mixed
     * @todo implement caching: take care of things other than strings
     */
    public function get($option){
        if ( is_null($this->_hash) ){
            return false;
        }
        $value = $this->getTable()
                      ->get($this->_hash, $option)
                      ->optionValue;
        return $this->maybeUnserialize($value);
    }

    /**
     * @author mlaug
     * @param mixed $option
     * @param mixed $value
     * @return boolean
     * @todo: implement caching
     */
    public function set($option,$value){
        if ( is_null($this->_hash) ){
            return false;
        }
        try{

            if ( !is_string($option) ){
                return false;
            }

            //check if this is an protected option
            $this->protectSpecialOptions($option);
            //maybe we need to serialize this data
            $value = $this->maybeSerialize($value);
            //store value (add or update)
            $this->getTable()
                 ->set($this->_hash, $option, $value);

            return true;
        }
        catch ( Exception $e ){
            return false;
        }
        
    }

    /**
     * @author mlaug
     * @return Default_Option_Table
     */
    private function getTable(){
        if ( is_null($this->_table) ){
            $this->_table = new Default_Option_Table();
        }
        return $this->_table;
    }

    /**
     * get a serialized string if needed
     * @author mlaug
     * @param mixed $data
     * @return string
     */
    private function maybeSerialize($data) {
        if ( is_array( $data ) || is_object( $data ) ){
            return serialize( $data );
        }

        if ( is_serialized( $data ) ){
            return serialize( $data );
        }

        return $data;
    }

    /**
     * return unserialized string if needed, otherwise
     * just return the original
     * @author mlaug
     * @param string $original
     * @return mixed
     */
    private function maybeUnserialize($original) {
        if ( is_serialized( $original ) ){
            return @unserialize( $original );
        }
        return $original;
    }

    /**
     * @author mlaug
     * @param string $option
     */
    private function protectSpecialOptions($option){
        if ( in_array( $option, $this->_protected ) ){
            throw new Exception('Cannot set an protected option');
        }
    }

}
?>
