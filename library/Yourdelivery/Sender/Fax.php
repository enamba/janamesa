<?php

/**
 * Description of Fax
 * @package sender
 * @subpackage fax
 * @author mlaug
 */
class Yourdelivery_Sender_Fax {
    const RETARUS = 'retarus';

    const INTERFAX = 'interfax';

    protected $_logging = null;

    public function __construct() {
        $this->_logging = Zend_Registry::get('logger');
    }

    /**
     * @author mlaug
     * @since 31.01.2011
     * @param string $to
     * @param string $pdf
     * @param string $service
     * @param string $unique
     * @return boolean
     */
    public function send($to, $pdf, $service, $type, $unique = null) {
        switch ($service) {
            default:
            case Yourdelivery_Sender_Fax::RETARUS:
                $sender = new Yourdelivery_Sender_Fax_Retarus();
                $ret = $sender->send($to, $pdf, $type, $unique);
                if ($ret === true) {
                    $this->_logging->info(sprintf('RETARUS: succesfully send out fax %s to %s', basename($pdf), $to));
                    return true;
                } else {
                    $this->_logging->crit(sprintf('RETARUS: failed send out fax %s to %s', basename($pdf), $to));
                    return false;
                }
                break;

            case Yourdelivery_Sender_Fax::INTERFAX:
                $sender = new Yourdelivery_Sender_Fax_Interfax();
                $ret = $sender->send($to, $pdf, $type, $unique);
                if ($ret > 0) {
                    $this->_logging->info(sprintf('INTERFAX: succesfully send out fax %s to %s', basename($pdf), $to));
                    return true;
                } else {
                    $this->_logging->crit(sprintf('INTERFAX: failed send out fax %s to %s', basename($pdf), $to));
                    return false;
                }
                break;
        }
        return false;
    }

    /**
     * @author mlaug
     * @since 31.01.2011
     * @param string $service 
     */
    public function processReports($service) {
        switch ($service) {
            default:
            case Yourdelivery_Sender_Fax::RETARUS:
                $retarus = new Yourdelivery_Sender_Fax_Retarus();
                $retarus->processReports();
                break;

            case Yourdelivery_Sender_Fax::INTERFAX:
                $interfax = new Yourdelivery_Sender_Fax_Interfax();
                $interfax->processReports();
                break;
        }
    }

    /**
     * @author mlaug
     * @since 04.02.2011
     * @param string $to
     * @param string $service
     * @return boolean
     */
    public function test($to, $service) {
        switch ($service) {
            default:
            case Yourdelivery_Sender_Fax::RETARUS:
                $retarus = new Yourdelivery_Sender_Fax_Retarus();
                return $retarus->test($to);
                break;

            case Yourdelivery_Sender_Fax::INTERFAX:
                $interfax = new Yourdelivery_Sender_Fax_Interfax();
                return $interfax->test($to);
                break;
        }
    }

}

?>
