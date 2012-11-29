<?php

/**
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 * @since 07.09.2011
 */
class Default_Helpers_Log {

    /**
     * get last log entry
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 07.09.2011
     *
     * @param $logfile
     * @return string
     */
    public static function getLastLog($logfile = 'log', $countLogEntries = 10) {
        
        $config = Zend_Registry::get('configuration');

        switch ($logfile) {
            case 'log':
                $logfile = $config->logging->file;

                break;
            case 'payment':
                $logfile = $config->logging->payment;
                break;
            default:
                return null;
                break;
        }

        $fileName = str_replace('%s', date('d-m-Y'), $logfile);

        if (!file_exists($fileName)) {
            return 'file '.$fileName.' dos not exit';
        }

        $fp = @fopen($fileName, 'r');
        if (!$fp) {
            return null;
        }

        $string = @fread($fp, filesize($fileName));
        if(!$string){
            return null;
        }

        return implode("\n", array_slice(explode("\n", $string), -($countLogEntries), $countLogEntries));
    }

}
