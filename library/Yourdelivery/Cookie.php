<?php

/**
 * @author mlaug
 * @since 17.07.2011
 */
class Yourdelivery_Cookie {

    /**
     * Cookie name
     * @var string
     */
    protected $_name = '';
    
    /**
     * Data
     * @var array 
     */
    protected $_data = array();

    /**
     * Create a cookie
     * @author vpriem
     * @since 26.07.2011
     * @param string $cookieName
     * @return Yourdelivery_Cookie 
     */
    public static function factory($cookieName) {
        
        switch ($cookieName) {
            case 'yd-state':
                $cookie = new Yourdelivery_Cookie($cookieName, array(
                    'city' => null,
                    'location' => null,
                    'kind' => "priv",
                    'mode' => "rest",
                    'number' => null,
                    'verbose' => null,
                ));
                break;

            case 'yd-customer':
                $cookie = new Yourdelivery_Cookie($cookieName, array(
                    'name' => null,
                    'prename' => null,
                    'company' => null,
                    'admin' => 0,
                    'companyId' => 0,
                    'hasLocations' => 0,
                ));
                break;
            
            case 'yd-recurring':
                $cookie = new Yourdelivery_Cookie($cookieName, array(
                    'lastorder' => null,
                    'lastarea' => null,
                    'lastorderarea' => null
                ));
                break;
            
            default:
                throw new Yourdelivery_Exception("Unknow cookie: " . $cookieName);
        }
        
        //finalize and return
        return $cookie;
    }

    /**
     * @author vpriem
     * @since 26.07.2011
     * @param string $name
     * @param array $data 
     */
    public function __construct($name, array $data) {
        
        $this->_name = $name;
        $this->_data = $data;
        $this->read();
    }
    
    /**
     * Get the name of the cookie
     * @author mlaug
     * @since 17.07.2011
     * @return string
     */
    public function getName() {
        
        return $this->_name;
    }

    /**
     * @author mlaug
     * @since 12.07.2011
     * @param string $key
     * @return string 
     */
    public function get($key) {
        
        if (!array_key_exists($key, $this->_data)) {
            return null;
        }
        
        return $this->_data[$key];
    }

    /**
     * @author mlaug
     * @since 17.07.2011
     * @param string $key
     * @param string $value 
     * @return boolean
     */
    public function set($key, $value) {
        
        if (!array_key_exists($key, $this->_data)) {
            return false;
        }
        
        // encode string as non utf8 string
        // because its not suported by
        // the jquery base64 plugin
        if (is_string($value)) {
            $value = utf8_decode($value);
        }
        
        $this->_data[$key] = $value;
        return true;
    }

    /**
     * Fill the cookie data
     * @autor mlaug
     * @since 12.07.2011
     */
    public function read() {
        
        $cookie = Default_Helpers_Web::getCookie($this->_name);
        if ($cookie !== null) {
            $i = 0;
            $data = explode('#', $cookie);
            foreach ($this->_data as $key => $value) {
                $this->_data[$key] = $data[$i];
                $i++;
                if ($i >= count($data)) {
                    break;
                }
            }
        }
    }

    public function getData(){
        return $this->_data;
    }
    
    /**
     * Store data back to cookie
     * @author mlaug
     * @return boolean
     * @since 17.07.2011
     */
    public function save() {     
        return Default_Helpers_Web::setCookie($this->_name, implode('#', $this->_data));
    }

}
