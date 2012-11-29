<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once('Extern/Pheanstalk/pheanstalk_init.php');

/**
 * Description of Queue
 *
 * @author daniel
 */
class Pheanstalk_Queue {

    /**
     * Boolean flag saying whether pheanstalk queue is enabled
     * @var boolean
     */
    protected static $isEnabled = false;

    /**
     * Pheanstalk host
     * @var string
     */
    protected static $host;

    /**
     * Pheanstalk tube name
     * @var string
     */
    protected static $tube;

    /**
     * Order logger instance
     * @var Zend_Log
     */
    protected static $logger;

    /**
     * Pheanstalk client instance
     * @var Pheanstalk
     */
    protected $pheanstalk = null;

    /**
     * Initialization of Pheanstalk by setting its options
     *
     * @param Zend_Option_Ini $pheanstalkOptions
     * @return void
     */
    public static function init($pheanstalkOptions) {
        self::$isEnabled = (bool) $pheanstalkOptions->enabled;
        self::$host = $pheanstalkOptions->host;
        self::$tube = $pheanstalkOptions->tube;
    }

    /**
     * Object custructor - creates pheanstalk instance
     */
    public function __construct() {
        if (self::$isEnabled) {
            $this->pheanstalk = new Pheanstalk(self::$host);        
        }
    }

    /**
     * Insterts content into the queue
     *
     * @param string $data
     */
    public function insert($data) {
        if (self::$isEnabled) {
            try {
                $this->pheanstalk->useTube(self::$tube)->put($data);
                $this->log(sprintf('Pheanstalk tube `%s`: data succesfully inserted into the queue', self::$tube), Zend_Log::INFO);
            } catch (Pheanstalk_Exception $ex) {
                $this->log(sprintf('Pheanstalk tube `%s`: data insertion error: %s', self::$tube, $ex->getMessage()), Zend_Log::ALERT);
                throw new ErrorException('Pheanstalk queue problem: ' . $ex->getMessage());
            }
        } else {
            $this->log(sprintf('Pheanstalk tube `%s`: data inserting succesfully simulated', self::$tube), Zend_Log::INFO);
        }
    }
    
    public function getStats() {
        return $this->pheanstalk->stats();
    }
    
    
    /**
     * Logs pheanstalk operation details
     *
     * @param string $message
     * @param integer $priority
     * @return void
     */
    protected function log($message, $priority) {
        if (!isset($logger)) {
            self::$logger = Zend_Registry::get('logger');
        }
        self::$logger->log($message, $priority);
    }

    
    
}

?>
