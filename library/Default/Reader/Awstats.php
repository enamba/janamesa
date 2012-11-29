<?php
/**
 * AWStats
 * Read awstats logs
 * @author vpriem
 * @since 09.05.2011
 */
class Default_Reader_Awstats{

    /**
     * @var resource file stream 
     */
    private $_awstats;
    
    /**
     * @var array sections with offset
     */
    private $_sections;
    
    /**
     * @var string awstats config
     */
    private $_config;
    
    /**
     * @var string awstats path
     */
    private $_path;

    /**
     * Constructor
     * @author vpriem
     * @since 09.05.2011
     * @param string awstats config
     * @param string awstats path
     * @return void
     */
    public function __construct ($config, $path = "/var/lib/awstats") {

        $this->_config = basename($config);
        $this->_path   = rtrim($path, "/") . "/";

    }

    /**
     * Read awstats
     * @author vpriem
     * @since 09.05.2011
     * @param int month 
     * @param int year 
     * @return boolean
     */
    public function read ($m = null, $y = null) {

        if ($m === null) $m = date('m');
        if ($y === null) $y = date('Y');
        if (checkdate($m, 1, $y)) {
            $path = $this->_path . "awstats" . date('mY', mktime(0, 0, 0, $m, 1, $y)) . "." . $this->_config . ".txt";
            @fclose($this->_awstats);
            if ($this->_awstats = @fopen($path, 'rt')) {
                $this->_sections = array();
                while (!feof($this->_awstats)) {
                    $line = fgets($this->_awstats);
                    if (preg_match('/^END_MAP/', $line)) break;
                    if (preg_match('/^POS_(\w+) (\d+)/', $line, $matches)) {
                        $this->_sections[$matches[1]] = intval($matches[2]);
                    }
                }
                return true;
            }
        }
        return false;

    }

    /**
     * Read section
     * @author vpriem
     * @since 09.05.2011 
     * @param string section 
     * @param string key1 
     * @param string key2 
     * @return mixed
     */
    function section(){

        $args = func_get_args();
        $section = strtoupper(array_shift($args));
        if ($offset = $this->_sections[$section]) {
            if (fseek($this->_awstats, $offset) == 0) {
                fgets($this->_awstats);
                $lines = array();
                while (!feof($this->_awstats)) {
                    $line = fgets($this->_awstats);
                    if (preg_match('/^END_' . $section . '/', $line)) break;
                    $lines[] = array_combine($args, explode(" ", $line));
                }
                return $lines;
            }
        }
        return false;

    }

    /**
     * Destructor
     * @author vpriem
     * @since 09.05.2011
     * @return void
     */
    public function __destruct(){

        if (is_resource($this->_awstats)) fclose($this->_awstats);

    }

}
