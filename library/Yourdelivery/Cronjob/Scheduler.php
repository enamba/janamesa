<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Scheduler
 *
 * @author Matthias Laug <laug@lieferando.de>
 */
class Yourdelivery_Cronjob_Scheduler {

    const VERSION = '1.2';

    /**
     * @var string
     */
    protected $xmlfile = null;

    /**
     * @var Yourdelivery_Log 
     */
    protected $logger = null;

    /**
     * @var Zend_Config_Ini 
     */
    protected $config = null;

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 26.01.2012 
     */
    public function __construct() {
        $this->xmlfile = APPLICATION_PATH . '/../cron/status.xml';
        $this->logger = Zend_Registry::get('logger');
        $this->config = Zend_Registry::get('configuration');
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 25.01.2011
     * @param string $dir
     * @return array 
     */
    protected function getFiles($dir, $extension = 'php') {
        $found = array();
        if ($files = @scandir($dir)) {
            $found = array();
            foreach ($files as $file) {
                if ($file == "." || $file == "..") {
                    continue;
                }
                $file = $dir . "/" . $file;
                if (is_dir($file)) {
                    $found = array_merge($found, $this->getFiles($file));
                } elseif (substr($file, (-1) * strlen($extension)) == $extension) {
                    $found[] = $file;
                }
            }
        }
        return $found;
    }

    /**
     * if anything had happend to the scheduler before, all locks should be
     * cleard to get a fresh new start
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 25.01.2012 
     */
    public function clearLocks() {
        foreach ($this->getFiles(APPLICATION_PATH . '/../cron/locks/', 'lockfile') as $lockfile) {
            unlink($lockfile);
        }
    }

    /**
     * create initial xml to register all cronjobs
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 25.01.2012 
     */
    public function initXml() {

        //if version do not match, we unlink the current xml file
        if (file_exists($this->xmlfile)) {
            $xml = simplexml_load_file($this->xmlfile);
            if ($xml->version->__toString() != self::VERSION) {
                unlink($this->xmlfile);
            }
            unset($xml);
        }

        if (!file_exists($this->xmlfile)) {
            $doc = new DOMDocument('1.0', 'UTF-8');
            $doc->formatOutput = true;
            $root_element = $doc->createElement("cronjobs");

            //add version of scheduler
            $messageElement = $doc->createElement("version");
            $messageElement->appendChild($doc->createTextNode(self::VERSION));
            $root_element->appendChild($messageElement);

            //add initial memory usage to xml file
            $memory = $doc->createElement("memory");
            $peak = $doc->createElement('peak');
            $peak->appendChild($doc->createTextNode(number_format(memory_get_peak_usage(), 0, '.', ',')));
            $usage = $doc->createElement('usage');
            $usage->appendChild($doc->createTextNode(number_format(memory_get_usage(), 0, '.', ',')));
            $memory->appendChild($peak);
            $memory->appendChild($usage);
            $root_element->appendChild($memory);

            $doc->appendChild($root_element);
            $doc->save($this->xmlfile);
            unset($doc);
        }
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 26.01.2012 
     */
    public function updateMemoryUsage() {
        $xml = simplexml_load_file($this->xmlfile);
        $sxe = new SimpleXMLElement($xml->asXML());
        $sxe->memory->peak = number_format(memory_get_peak_usage(), 0, '.', ',');
        $sxe->memory->usage = number_format(memory_get_usage(), 0, '.', ',');
        $sxe->saveXML($this->xmlfile);
    }

    /**
     * start the scheduler, which is called each minute from the cronjob. Here
     * we check if this script should ran at this loop and put it into an array.
     * Once there is a free slot, we call it
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 25.01.2012 
     */
    public function start() {

        //clear locks, if any have been not fit
        //$this->clearLocks();
        //create intial xml        
        $this->initXml();

        //get all cronjobs
        $found = $this->getFiles(APPLICATION_PATH . '/../cron/');

        //init cronjob list
        $processes = array(
            Yourdelivery_Cronjob_Abstract::PARALLEL => array(),
            Yourdelivery_Cronjob_Abstract::SERIAL => array()
        );

        //create cronjob list
        foreach ($found as $file) {
            if (file_exists($file)) {
                $this->logger->debug(sprintf('CRON SCHEDULER: found file %s, searching for class Yourdelivery_Cronjob_Abstract', $file));
                require_once($file);
                $job = new Zend_Reflection_File($file);
                foreach ($job->getClasses() as $class) {
                    /**
                     * @var Yourdelivery_Cronjob_Abstract 
                     */
                    $instance = new $class->name();
                    if ($instance instanceof Yourdelivery_Cronjob_Abstract && $instance->shouldRun()) {
                        $this->logger->debug(sprintf('CRON SCHEDULER: found class in file %s', $file));
                        $instance->xmlfile = $this->xmlfile;
                        $processes[$instance->getType()][] = $instance;
                    }
                }
            }
        }

        //do the serial once first and wait for each to finish
        //check first if we do not have blackout hours
        if (time() > strtotime($this->config->cronjobs->blackout->serial->from) &&
                time() < strtotime($this->config->cronjobs->blackout->serial->until)) {
            $this->logger->info('CRONJOBS: having blackout hours for serial jobs, doing nothing here');
        } else {
            foreach ($processes[Yourdelivery_Cronjob_Abstract::SERIAL] as $serial) {
                $serial->start();
                $this->logger->info(sprintf('CRON SCHEDULER: starting serial cron %s', $serial->getName()));
                $this->logger->info(sprintf('CRON SCHEDULER: this will block all others still finish'));
                while ($serial->isRunning()) {
                    $this->updateMemoryUsage();
                    sleep(1);
                    continue;
                }
                $result = $serial->stop();
                if ($result) {
                    $this->logger->info(sprintf('CRON SCHEULDER: finish serial cron %s', $serial->getName()));
                } else {
                    $this->logger->crit(sprintf('CRON SCHEULDER: failed to stop serial cron %s, memory leak possible', $serial->getName()));
                }
            }
        }

        if (time() > strtotime($this->config->cronjobs->blackout->parallel->from) &&
                time() < strtotime($this->config->cronjobs->blackout->parallel->until)) {
            $this->logger->info('CRONJOBS: having blackout hours for parallel jobs, doing nothing here');
        } else {
            //start all parallel processs
            foreach ($processes[Yourdelivery_Cronjob_Abstract::PARALLEL] as $parallel) {
                $this->logger->info(sprintf('CRON SCHEDULER: starting parallel cron %s', $parallel->getName()));
                $parallel->start();
            }

            //check all parallels
            while (count($processes[Yourdelivery_Cronjob_Abstract::PARALLEL]) > 0) {
                foreach ($processes[Yourdelivery_Cronjob_Abstract::PARALLEL] as $key => $parallel) {
                    if (!$parallel->isRunning()) {
                        $this->updateMemoryUsage();
                        $result = $parallel->stop();
                        if ($result) {
                            $this->logger->info(sprintf('CRON SCHEULDER: finish parallel cron %s', $parallel->getName()));
                        } else {
                            $this->logger->crit(sprintf('CRON SCHEULDER: failed to stop parallel cron %s, memory leak possible', $parallel->getName()));
                        }
                        unset($processes[Yourdelivery_Cronjob_Abstract::PARALLEL][$key]); //remove from list
                    }
                }
                sleep(1); //wait one second for next loop
            }
        }
    }

}
