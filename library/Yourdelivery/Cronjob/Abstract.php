<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Abstract
 *
 * @author Matthias Laug <laug@lieferando.de>
 */
abstract class Yourdelivery_Cronjob_Abstract extends Yourdelivery_Cronjob_Thread {
    /**
     * constant for a parallel process. This job is non-blocking and can run
     * in parallel with other non-blocking processes
     * @var string 
     */

    const PARALLEL = 'parallel';

    /**
     * constant for a serial process. This job blocks anything else and runs
     * only in a standalone mode with no other concurrent processes
     * @var string 
     */
    const SERIAL = 'serial';

    protected $key = null;  //user given value for the lockfile
    protected $file = null;  //resource to lock
    protected $own = false; //have we locked resource
    public $xmlfile = null;

    /**
     * class constructor - you can pass
     * the callback function as an argument
     *
     * @param callback $_runnable
     */
    public function __construct($_runnable = '_run') {
        parent::__construct($_runnable);

        if ($this->key === null) {
            throw new Exception('A key must be set for this cronjob');
        }

        //create a new resource or get exisitng with same key
        $this->file = fopen(APPLICATION_PATH . '/../cron/locks/' . $this->key . ".lockfile", 'w+');
    }

    public function __destruct() {
        $this->stop();
        $this->unlock();
    }

    abstract protected function _run();

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 02.03.2012
     * @return string
     */
    public function getName() {
        return $this->key;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 25.01.2012
     * @return boolean 
     */
    public function lock() {
        if (!flock($this->file, LOCK_EX | LOCK_NB)) { //failed
            $key = $this->key;
            clog("warn", "ExclusiveLock::acquire_lock FAILED to acquire lock [$key]");
            return false;
        }
        ftruncate($this->file, 0); // truncate file
        //write something to just help debugging
        fwrite($this->file, "Locked\n");
        fflush($this->file);

        $this->own = true;
        return $this->own;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 25.01.2012
     * @return boolean 
     */
    public function unlock() {
        $key = $this->key;
        if ($this->own == true) {
            if (!flock($this->file, LOCK_UN)) { //failed
                clog("warn", "ExclusiveLock::lock FAILED to release lock [$key]");
                return false;
            }
            ftruncate($this->file, 0); // truncate file
            //write something to just help debugging
            fwrite($this->file, "Unlocked\n");
            fflush($this->file);
        }
        $this->own = false;
        return $this->own;
    }

    /**
     * get the key of this class
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 25.01.2012
     * @return string 
     */
    public function getKey() {
        return md5($this->getName());
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 25.01.2012
     * @return string 
     */
    protected function getSchedule() {
        return ""; //never as default!
    }

    /**
     * get the xml object of the entire xmlfile
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 25.01.2012
     * @return \SimpleXMLElement 
     */
    public function getXml() {
        $xml = @simplexml_load_file($this->xmlfile);
        if ($xml instanceof SimpleXMLElement) {
            $sxe = new SimpleXMLElement($xml->asXML());
            return $sxe;
        }
        return null;
    }

    /**
     * get the xml element of this registered job
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 25.01.2012
     * @throws Exception
     * @return \SimpleXMLElement 
     */
    public function getXmlJob() {
        $sxe = $this->getXml();
        if ($sxe === null) {
            throw new Exception('could not get xml file');
        }

        $jobs = $sxe->children();

        //check if already registered
        foreach ($jobs as $job) {
            $attr = $job->attributes();
            if ($attr['key'] == $this->key) {
                return array($sxe, $job);
            }
        }

        return array($sxe, null);
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 25.01.2012
     * @reutrn SimpleXmlElement
     */
    public function register() {
        try {
            list($sxe, $job) = $this->getXmlJob();
        } catch (Exception $e) {
            return null;
        }

        if ($job !== null) {
            $job->running = "false";

            if ($this->lockXmlFile()) {
                $sxe->saveXML($this->xmlfile);
            }
            return $job;
        }

        //register and store xml file
        $cronjob = $sxe->addChild('cronjob');
        $cronjob->addAttribute('key', $this->key);
        $cronjob->addChild('registered', date('d.m.Y H:i:s'));
        $cronjob->addChild('lastrun', date('d.m.Y H:i:s'));
        $cronjob->addChild('running', 'false');

        if ($this->lockXmlFile()) {
            $sxe->saveXML($this->xmlfile);
        }
        return $cronjob;
    }

    /**
     * mark this service in the xml as running
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 26.01.2012 
     */
    public function markRunning() {
        try {
            list($sxe, $job) = $this->getXmlJob();
        } catch (Exception $e) {
            return null;
        }

        $job->running = "true";
        $job->lastrun = date('d.m.Y H:i:s');
        if ($this->lockXmlFile()) {
            $sxe->saveXML($this->xmlfile);
        }
    }

    /**
     * mark the service in the xml as stopped
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 26.01.2012 
     */
    public function markStopped() {
        try {
            list($sxe, $job) = $this->getXmlJob();
        } catch (Exception $e) {
            return null;
        }

        $job->running = "false";
        if ($this->lockXmlFile()) {
            $sxe->saveXML($this->xmlfile);
        }
    }

    /**
     * get the timestamp of the last schedules run
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 26.01.2012 
     * @return integer
     */
    public function getLastRun() {
        try {
            list($sxe, $job) = $this->getXmlJob();
        } catch (Exception $e) {
            return null;
        }

        return strtotime($job->lastrun);
    }

    /**
     * start the code implemented in _run and create a lock for this class
     * so there will be no parallel calls of the same script
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 25.01.2012
     * @return boolean 
     */
    public function start() {
        if ($this->lock()) {
            if ($this->xmlfile != null) {
                $this->register();
            }

            parent::start();

            if ($this->xmlfile != null) {
                $this->markRunning();
            }
            return true;
        }
        return false;
    }

    /**
     * stop the script and cleanup xml and lockfile
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 25.01.2012 
     * @return boolean
     */
    public function stop() {
        $this->unlock();
        if ($this->xmlfile != null) {
            $this->markStopped();
        }
        return parent::stop();
    }

    /**
     * define the typ of this script
     * 
     * PARALLEL (default): script may be called concurrent to other scripts
     * SERIAL: script should block all other scripts
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @return type 
     */
    public function getType() {
        return self::PARALLEL;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @return boolean 
     */
    public function isRunning() {
        return $this->isAlive();
    }

    /**
     * get the lock for the xml file
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @return boolean
     */
    protected function lockXmlFile() {
        $fp = fopen($this->xmlfile, 'r+');
        if (flock($fp, LOCK_EX)) {
            return true;
        }
        return false;
    }

    /**
     * @author http://www.binarytides.com/blog/php-check-if-a-timestamp-matches-a-given-cron-schedule/
     * @param string $cron
     * @param integer $lastrun
     * @return boolean 
     */
    public function shouldRun() {

        $cron = $this->getSchedule(); //get our schedule

        $time = time(); //always use now! 

        $cron_parts = explode(' ', $cron);
        if (count($cron_parts) != 5) {
            return false;
        }

        list($min, $hour, $day, $mon, $week) = explode(' ', $cron);

        $to_check = array('min' => 'i', 'hour' => 'G', 'day' => 'j', 'mon' => 'n', 'week' => 'w');

        $ranges = array(
            'min' => '0-59',
            'hour' => '0-23',
            'day' => '1-31',
            'mon' => '1-12',
            'week' => '0-6',
        );

        foreach ($to_check as $part => $c) {
            $val = $$part;
            $values = array();

            /*
             * For patters like 0-23/2
             */
            if (strpos($val, '/') !== false) {
                //Get the range and step
                list($range, $steps) = explode('/', $val);

                //Now get the start and stop
                if ($range == '*') {
                    $range = $ranges[$part];
                }
                list($start, $stop) = explode('-', $range);

                for ($i = $start; $i <= $stop; $i = $i + $steps) {
                    $values[] = $i;
                }
            }
            /*
             * For patters like :
             * 2
             * 2,5,8
             * 2-23
             */ else {
                $k = explode(',', $val);

                foreach ($k as $v) {
                    if (strpos($v, '-') !== false) {
                        list($start, $stop) = explode('-', $v);

                        for ($i = $start; $i <= $stop; $i++) {
                            $values[] = $i;
                        }
                    } else {
                        $values[] = $v;
                    }
                }
            }

            if (!in_array(date($c, $time), $values) and (strval($val) != '*')) {
                return false;
            }
        }

        return true;
    }

}
