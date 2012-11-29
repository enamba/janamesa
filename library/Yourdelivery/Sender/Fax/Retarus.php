<?php

/**
 * Description of Fax
 * @package sender
 * @subpackage fax
 * @author mlaug
 */
class Yourdelivery_Sender_Fax_Retarus implements Yourdelivery_Sender_Fax_Interface {

    /**
     * connection to retarus ftp server
     * @var resource
     */
    protected $_conn = null;

    /**
     * @var Default_File_Storage
     */
    protected $_storageReports = null;

    /**
     * @var Default_File_Storage
     */
    protected $_storageJobs = null;

    /**
     * @var Zend_Config
     */
    protected $_config = null;

    /**
     * @var Zend_Log
     */
    protected $_logging = null;

    /**
     * @var SoapClient
     */
    public $_client = null;

    /**
     * @var string
     */
    protected $_method = null;

    /**
     * @param string $method method of sending fax to retaurs ("ftp" | "api")
     *
     * @author mlaug, hahn@lieferando.de
     * @since 17.04.2012
     */
    public function __construct($method = null) {
        $this->_logging = Zend_Registry::get('logger');

        //load config
        $this->_config = Zend_Registry::get('configuration');

        $this->_method = is_null($method) ? $this->_config->sender->fax->method : $method;

        switch ($this->_method) {
            default:
            case "ftp": {
                    $this->_initFtp();
                    $this->_method = 'ftp';
                    break;
                }
            case "api" : {
                    //Retarus SOAP Client
                    $this->_client = new SoapClient("https://fax4ba.retarus.com/Faxolution?wsdl", array('trace' => 1));
                    $this->_UserPassword = new stdClass();
                    $this->_UserPassword->userName = $this->_config->sender->fax->username;
                    $this->_UserPassword->password = $this->_config->sender->fax->password;
                    break;
                }
        }
    }

    /**
     * Destructor
     * @author mlaug
     * @since 17.04.2012
     */
    public function __destruct() {
        if (is_resource($this->_conn)) {
            ftp_close($this->_conn);
        }
    }

    /**
     * send fax by API
     *
     * @param string $to     number to send fax to
     * @param file   $pdf    pdf file to send as fax
     * @param string $type   type of fax ("order", "bill", ...)
     * @param string $unique unique name of fax document
     *
     * @return boolean
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 17.04.2012
     */
    public function sendByApi($to, $pdf, $type, $unique = null) {
        //overwrite if development
        if (!IS_PRODUCTION) {
            $to = "TELEPHONE";
        }

        if (!file_exists($pdf)) {
            $this->_logging->crit('RETARUS: Did not get any valid pdf: ' . $pdf);
            return false;
        }

        if (IS_PRODUCTION || $this->_isTesting) {
            $JobRequest = $this->_UserPassword;
            $JobRequest->faxNumbers = new stdClass();
            $JobRequest->faxNumbers->number = $to;
            $JobRequest->documents = new stdClass();
            $JobRequest->documents->name = $unique . '-' . basename($pdf);
            $JobRequest->documents->text = "0";
            $JobRequest->documents->data = file_get_contents($pdf);
            $JobRequest->options = new stdClass();
            $JobRequest->options->blacklist = false;
            $JobRequest->options->express = true;
            if (IS_PRODUCTION) {
                $JobRequest->options->csid = "004980020207702";
            }
            $JobRequest->options->resolution = "low";
            $JobRequest->options->jobReference = $type . "-" . $unique;

            $table = new Yourdelivery_Model_DbTable_Retarus_Transactions();
            $row = $table->createRow(array('uniqueId' => $type . "-" . $unique, 'type' => $type, 'documentName' => basename($pdf), 'faxNumber' => $to, 'created' => date(DATETIME_DB)));
            $row->save();

            try {
                $this->_client->sendFaxJob($JobRequest);
            } catch (SoapFault $e) {
                $this->_logging->crit(sprintf('RETARUS - API: send failed with error %s', $e->getMessage()));
                return false;
            }

            return true;
        } else {
            /**
             * send out email in development
             */
            $email = new Yourdelivery_Sender_Email_Template('faxdevel.txt');
            $email->addTo($this->_config->testing->email);
            $email->setSubject('FAX TEST VERSAND - RETARUS API');
            $email->createAttachment(
                    file_get_contents($pdf), 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64
            );

            $email->send();
            return true;
        }
    }

    /**
     * send by ftp
     *
     * @param string $to     number to send fax to
     * @param file   $pdf    pdf file to send as fax
     * @param string $type   type of fax ("order", "bill", ...)
     * @param string $unique unique name of fax document
     *
     * @return boolean
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 17.04.2012
     */
    public function sendByFtp($to, $pdf, $type, $unique = null) {
        if (is_null($this->_conn)) {
            $this->_logging->crit('RETARUS - FTP: Could not connect to retarus FPT Server');
            return false;
        }

        $xmlraw = $this->_config->sender->fax->template->xml;

        //load xml file
        $xml = new SimpleXMLElement($xmlraw, NULL, true);

        // add fax number
        $xml->fax->{'distribution-list'}->{'fax-address'}->{'fax-number'} = str_replace(' ', '', $to);
        // add pdf file to be sent
        $up_pdf = $unique . '-' . basename($pdf);
        $xml->fax->{'fax-file-list'}->{'fax-file'}->{'file-name'} = $up_pdf;
        // references
        $xml->reference = 'yd-fax-' . $type . '-' . $unique;
        $xml->fax->{'job-reference'} = 'yd-fax-' . $type . '-' . $unique . "-1";

        $this->_storageJobs->setTimeStampFolder();
        $jobfile = $this->_storageJobs->getCurrentFolder() . '/job-' . $type . "-" . $unique . '.xml';
        $xml->asXML($jobfile);

        if (IS_PRODUCTION) {
            //upload pdf
            $upload = ftp_put($this->_conn, '/in/' . $up_pdf, $pdf, FTP_BINARY);

            if (!$upload) {
                $this->_logging->crit('RETARUS - FTP: Could not upload PDF');
                return false;
            }

            //upload xml
            $upload = ftp_put($this->_conn, '/in/' . basename($jobfile), $jobfile, FTP_BINARY);

            if (!$upload) {
                $this->_logging->crit('RETARUS - FTP: Could not upload XML');
                return false;
            }
            return true;
        } else {
            /*
             * send out email in development
             */
            $email = new Yourdelivery_Sender_Email_Template('faxdevel.txt');
            $email->addTo($this->_config->testing->email);
            $email->setSubject('FAX TEST VERSAND - RETARUS FTP');
            $email->createAttachment(
                    file_get_contents($pdf), 'application/pdf', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64
            );
            $email->createAttachment(
                    file_get_contents($jobfile), 'html/xml', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64
            );
            $email->send();
            return true;
        }
    }

    /**
     * Wrapper for FTP/API send functions
     *
     * @param string $to
     * @param string $pdf
     * @param int $unique
     *
     * @return boolean
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 17.04.2012
     */
    public function send($to, $pdf, $type, $unique = null) {
        //get unique number and remove dots
        $randomNumber = str_replace('.', '', uniqid('', true));

        if ($unique === null) {
            $unique = 'none-' . $randomNumber;
        } else {
            /**
             * append a timestamp to the unique value,
             * to allow sending fax multiple (like an order) times
             */
            $unique .= '-' . $randomNumber;
        }

        $unique .= '-' . $this->_config->domain->base;

        //overwrite if development
        if (!IS_PRODUCTION) {
            $to = "TELEPHONE";
        }

        if (!file_exists($pdf)) {
            $this->_logging->crit('RETARUS: Did not get any valid pdf: ' . $pdf);
            return false;
        }

        if (!Default_Helper::fax_validate($to)) {
            $this->_logging->crit('RETARUS: Did not get a valid number: ' . $to);
            return false;
        }

        switch ($this->_method) {
            default:
            case "ftp":
                return $this->sendByFtp($to, $pdf, $type, $unique);

            case "api" :
                //Retarus SOAP Client
                return $this->sendByApi($to, $pdf, $type, $unique);
                break;
        }
    }

    /**
     * test fax
     *
     * @param string $to
     *
     * @return boolean
     *
     * @author mlaug
     */
    public function test($to) {
        $pdf = APPLICATION_PATH . sprintf("/templates/fax/testfax/ydtestfax-%s.pdf", $this->_config->domain->base);
        return $this->send($to, $pdf, 'testfax');
    }

    /**
     * process Reports with Retarus API
     *
     * @return boolean
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 17.04.2012
     */
    public function processReportsByApi() {
        if (!$this->_client instanceof SoapClient) {
            $this->_logging->crit('RETARUS - processReportsByApi: no Soap Client Available');
            return false;
        }

        $availableReportsRequest = $this->_UserPassword;

        /**
         * fetch list with reports from retarus API
         */
        try {
            $availableReportsResponse = $this->_client->getListOfAvailableFaxReports($availableReportsRequest);
        } catch (SoapFault $e) {
            $this->_logging->crit(sprintf('RETARUS -processReportsByApi: could not get list of reports with error %s', $e->getMessage()));
            return false;
        }

        $jobIds = array();

        if (is_array($availableReportsResponse->availableReports)) {

            foreach ($availableReportsResponse->availableReports as $report) {
                $jobIds[] = $report->jobId;
            }
        } elseif ($availableReportsResponse->availableReports instanceof stdClass) {
            $jobIds[] = $availableReportsResponse->availableReports->jobId;
        }

        foreach ($jobIds as $jobId) {
            $reportRequest = $this->_UserPassword;
            $reportRequest->jobId = $jobId;

            try {
                $reportResponse = $this->_client->getFaxReport($reportRequest);
            } catch (SoapFault $e) {
                $this->_logging->crit(sprintf('RETARUS -processReportsByApi: could not get report for job #%s with error %s - skipping this job', $jobId, $e->getMessage()));
                continue;
            }

            $reference = $reportResponse->options->jobReference;
            $filename = $reportResponse->documents->name;
            $state = $reportResponse->faxNumbers->status;
            $number = $reportResponse->faxNumbers->number;
            $parts = explode("-", $reference);

            if ($parts[3] != $this->_config->domain->base) {
                continue;
            }

            $table = new Yourdelivery_Model_DbTable_Retarus_Transactions();
            $rows = $table->getByUniqueId($reference);

            if (count($rows) > 0) {
                foreach ($rows as $row) {
                    $row->response = serialize($reportResponse);
                    $row->save();
                }
            }

            //delete if domain is matching
            if (IS_PRODUCTION) {
                //will be debug after evaluating phase
                $this->_logging->info(sprintf('RETARUS -processReportsByApi: try to delete report job #%s from API list', $jobId));
                try {
                    $deleteReportRequest = $this->_UserPassword;
                    $deleteReportRequest->jobId = $jobId;
                    $this->_client->deleteFaxReport($deleteReportRequest);
                    $this->_logging->info(sprintf('RETARUS -processReportsByApi: deleted report job #%s from API list', $jobId));
                } catch (SoapFault $e) {
                    $this->_logging->warn(sprintf('RETARUS -processReportsByApi: job #%s cannot be deleted, error %s', $jobId, $e->getMessage()));
                }
            } else {
                $this->_logging->debug(sprintf('RETARUS -processReportsByApi: deleted report with job #%s from API list', $jobId));
            }

            $type = $parts[0];

            switch ($type) {
                default:
                    break;

                case 'testdevice':
                    $serviceId = $parts[1];
                    $this->_sendDeviceMail($serviceId, $state);
                    break;
                    
                case 'testfax':
                    $this->_logging->info('RETARUS -processReportsByApi: fax report for file %s sent with message %', $filename, $state);
                    break;

                case 'faxtool':
                    $this->_sendFaxtoolMail($filename, $number, $state);
                    break;

                case 'billing':
                    $this->_sendBillingMail($filename, $number, $state);
                    break;

                case 'order':
                    $orderId = $parts[1];
                    $this->_setOrderState($orderId, $state);
                    break;
            }
        }
    }

    /**
     * wrapper function to switch between ftp and api
     *
     * @return type
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 17.04.2012
     */
    public function processReports() {
        switch ($this->_method) {
            default:
            case "ftp": {
                    return $this->processReportsByFtp();
                    break;
                }
            case "api" : {
                    //Retarus SOAP Client
                    return $this->processReportsByApi();
                    break;
                }
        }
    }

    /**
     * get all reports from ftp and process them
     * check if any of them did not work out correctly
     *
     * @return void
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 17.04.2012
     */
    public function processReportsByFtp() {
        @ftp_chdir($this->_conn, '/out/');
        $rawlist = @ftp_rawlist($this->_conn, '');

        $files = array();
        foreach ($rawlist as $fileraw) {
            $file = substr($fileraw, 55, strlen($fileraw) - 55);
            //check if we really need that file, get only our reports
            if (is_string($file) &&
                    (strstr($file, $this->_config->domain->base) || ($this->_config->domain->base === "lieferando.de" && strstr($file, "eat-star.de")))) {
                $this->_logging->debug(sprintf('RETARUS: getting file %s from ftp server', $file));
                @ftp_get($this->_conn, $this->_storageReports->getCurrentFolder() . '/' . $file, $file, FTP_BINARY);
            }
        }

        $files = $this->_storageReports->ls();

        foreach ($files as $file) {
            $filename = basename($file);

            //ignore all that do not start with 'rep'
            if (preg_match('!rep!i', $filename)) {

                //muse be a xml file
                if (Default_Helpers_File::getFileExtension($filename) != "xml") {
                    $this->_logging->warn(sprintf('RETARUS: Ignoring %s because this seems not to be a valid xml file', $filename));
                    continue;
                }

                //the order id append at the end of the file name
                $repParts = explode('-', $filename);
                $type = $repParts[1];
                $domain = substr($repParts[4], 0, -4);

                if (($domain != $this->_config->domain->base) && ($this->_config->domain->base === "lieferando.de" && $domain !== 'eat-star.de')) {
                    $this->_storageReports->delete($filename);
                    $this->_logging->debug(sprintf('RETARUS: Ignoring fax report %s from different domain %s, we are in %s', $filename, $domain, $this->_config->domain->base));
                    continue;
                }

                //delete if domain is matching
                if (IS_PRODUCTION) {
                    //will be debug after evaluating phase
                    $this->_logging->debug(sprintf('RETARUS: delete file %s from ftp server', $filename));
                    @ftp_delete($this->_conn, $filename);
                } else {
                    $this->_logging->debug(sprintf('RETARUS: delete file %s from ftp server', $filename));
                }

                switch ($type) {
                    default: {
                            break;
                        }

                    case 'testdevice': {
                            $serviceId = (integer) $repParts[2];
                        
                            //use simple xml framework to work through xml file
                            $xml = simplexml_load_file($this->_storageReports->getCurrentFolder() . '/' . $filename);
                            $state = (string) $xml->fax->{'distribution-list'}->{'fax-address'}->status;

                            $this->_sendDeviceMail($serviceId, $state);
                            
                            //store processed report in done folder
                            $this->_storageReports->move($filename, '/done/' . date('d-m-Y') . '/' . $filename);
                            break;
                        }
                        
                    case 'testfax': {
                            //use simple xml framework to work through xml file
                            $xml = simplexml_load_file($this->_storageReports->getCurrentFolder() . '/' . $filename);
                            $state = (string) $xml->fax->{'distribution-list'}->{'fax-address'}->status;
                            $number = (string) $xml->fax->{'distribution-list'}->{'fax-address'}->{'fax-number'};

                            //store processed report in done folder
                            $this->_storageReports->move($filename, '/done/' . date('d-m-Y') . '/' . $filename);
                            break;
                        }

                    case 'faxtool': {
                            //use simple xml framework to work through xml file
                            $xml = simplexml_load_file($this->_storageReports->getCurrentFolder() . '/' . $filename);
                            $state = (string) $xml->fax->{'distribution-list'}->{'fax-address'}->status;
                            $number = (string) $xml->fax->{'distribution-list'}->{'fax-address'}->{'fax-number'};

                            $this->_sendFaxtoolMail($filename, $number, $state);

                            //store processed report in done folder
                            $this->_storageReports->move($filename, '/done/' . date('d-m-Y') . '/' . $filename);
                            break;
                        }

                    case 'billing': {
                            //use simple xml framework to work through xml file
                            $xml = simplexml_load_file($this->_storageReports->getCurrentFolder() . '/' . $filename);
                            $state = (string) $xml->fax->{'distribution-list'}->{'fax-address'}->status;
                            $number = (string) $xml->fax->{'distribution-list'}->{'fax-address'}->{'fax-number'};

                            $this->_sendBillingMail($filename, $number, $state);

                            $this->_logging->info(sprintf('RETARUS: Receiving fax report of bill %s with status %s', $filename, $state));

                            //store processed report in done folder
                            $this->_storageReports->move($filename, '/done/' . date('d-m-Y') . '/' . $filename);
                            break;
                        }


                    case 'order': {
                            $orderId = (integer) $repParts[2];

                            //use simple xml framework to work through xml file
                            $xml = simplexml_load_file($this->_storageReports->getCurrentFolder() . '/' . $filename);
                            $state = (string) $xml->fax->{'distribution-list'}->{'fax-address'}->status;

                            $this->_setOrderState($orderId, $state);
                            $this->_storageReports->move($filename, '/done/' . date('d-m-Y') . '/' . $filename);
                            break;
                        }
                }
            }
        }
    }

    /**
     * Initialize Ftp Connection
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 17.04.2012
     *
     * @throws Yourdelivery_Exception_NoConnection
     */
    protected function _initFtp() {
        //load server config
        $faxc = array();
        $faxc['server'] = $this->_config->sender->fax->server;
        $faxc['username'] = $this->_config->sender->fax->username;
        $faxc['password'] = $this->_config->sender->fax->password;

        // fax, check for timeouts and login failures
        try {
            //check if we can resolve the ip
            $hostIP = gethostbyname($faxc['server']);
            //if this produces an error, it returns hostname
            if ($hostIP == $faxc['server']) {
                $this->_logging->err('RETARUS: Could not resolve hostname of retarus server');
                throw new Yourdelivery_Exception_NoConnection('Could not resolve hostname of retarus server');
            }

            $this->_conn = @ftp_connect($hostIP, 21, 10);
            if ($this->_conn === false) {
                throw new Yourdelivery_Exception_NoConnection('Time out from retarus ftp server');
            }
            $login_result = ftp_login($this->_conn, $faxc['username'], $faxc['password']);
        } catch (Exception $e) {
            $this->_conn = null;
            $this->_logging->err('RETARUS: Could not login to retarus ftp server');
            throw new Yourdelivery_Exception_NoConnection('Could not login to retarus ftp server - exception: ' . $e->getMessage());
        }

        if (!$login_result) {
            $this->_logging->err('RETARUS: Could not get connection to fax ftp server');
            throw new Yourdelivery_Exception_NoConnection('Could not get connection to fax ftp server');
        }

        $this->_storageReports = new Default_File_Storage();
        $this->_storageReports->setSubFolder('fax/reports');

        $this->_storageJobs = new Default_File_Storage();
        $this->_storageJobs->setSubFolder('fax/jobs');
    }

    /**
     * Send Mail to Faxtool people
     *
     * @param type $filename
     * @param type $number
     * @param type $state
     *
     * @return void
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 17.04.2012
     */
    protected function _sendFaxtoolMail($filename, $number, $state) {
        $info = "Vertrag " . $filename . "wurde mit dem Status: " . $state . " versand";

        $email = new Yourdelivery_Sender_Email();
        $email->addTo('gia@lieferando.de');
        $email->addCc('laug@lieferando.de');
        $email->addCc('gerbig@lieferando.de');
        $email->addCc('gerber@lieferando.de');
        $email->addCc('hansen@lieferando.de');
        $email->setSubject('Vertrag an: ' . $number . ' mit Status: ' . $state);
        $email->setBodyHtml($info);
        $email->send('system');
    }

    /**
     * send Mail to Billing
     *
     * @param type $filename
     * @param type $number
     * @param type $state
     *
     * @return void
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 17.04.2012
     */
    protected function _sendBillingMail($filename, $number, $state) {
        $info = "Rechnung " . $filename . "wurde mit dem Status: " . $state . " versand";

        $email = new Yourdelivery_Sender_Email();
        $email->addTo('rechnung@lieferando.de');
        $email->setSubject('Rechnung . ' . $filename . ' an: ' . $number . ' mit Status: ' . $state);
        $email->setBodyHtml($info);
        $email->send('system');
    }

    /**
     * Send mail to device
     *
     * @param int $serviceId
     * @param string $state
     *
     * @return void
     *
     * @author Vincent Priem <priem@lieferando.de>
     * @since 16.05.2012
     */
    protected function _sendDeviceMail($serviceId, $state) {
        
        if (!in_array($state, array('OK'))) {
            return;
        }
        
        $errors = $this->_getErrorStates();
        $stateMsg = array_key_exists($state, $errors) ? $errors[$state] : "";
        
        $email = new Yourdelivery_Sender_Email();
        $email->addTo('gia@lieferando.de')
              ->addTo('ohrmann@lieferando.de')
              ->addTo('liss@lieferando.de')
              ->setSubject(sprintf('DL %s Sendebericht %s', $serviceId, $state))
              ->setBodyText(sprintf('DL %s Sendebericht %s %s', $serviceId, $state, $stateMsg))
              ->send('system');
    }

    /**
     * Set Current Order State after receiving Fax
     *
     * @param type $orderId
     * @param string $state (OK, BUSY, ...)
     *
     * @return boolean
     *
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 17.04.2012
     */
    protected function _setOrderState($orderId, $state) {
        if ($orderId == 0) {
            $this->_logging->err(sprintf('RETARUS - setOrderState: Did not get orderId'));
            return false;
        }

        //get order
        $order = null;
        try {
            $order = new Yourdelivery_Model_Order($orderId);
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
            $this->_logging->crit(sprintf('RETARUS - setOrderState: Could not find order by id #%s', $orderId));
            return false;
        }

        if ($order->getState() == Yourdelivery_Model_Order::STORNO) {
            $this->_logging->info(sprintf('RETARUS - setOrderState: Confirm reception of order but will no change state becuase of storno state #%s', $order->getId()));
            return false;
        }

        if ($order->getState() == Yourdelivery_Model_Order::FAKE) {
            $this->_logging->info(sprintf('RETARUS - setOrderState: Confirm reception of order but will no change state becuase of fake state #%s', $order->getId()));
            return false;
        }

        if ($order->getState() == Yourdelivery_Model_Order::COMPANYORDER) {
            $this->_logging->info(sprintf('RETARUS - setOrderState: Confirm reception of order but will no change state becuase of unaffirmed company #%s', $order->getId()));
            return false;
        }

        $errors = $this->_getErrorStates();

        $stateMsg = ( array_key_exists($state, $errors)) ? $errors[$state] : $state;

        if (in_array($state, array('OK'))) {
            $this->_logging->info(sprintf('RETARUS: Confirm reception of order #%s', $order->getId()));
            //affirm order
            if ($order->getMode() != "great") {
                if ($order->getState() < Yourdelivery_Model_Order::AFFIRMED) {
                    $order
                            ->setStatus(
                                    Yourdelivery_Model_Order::AFFIRMED,
                                     new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::RETARUS_OK)                                     
                    );
                }
            } elseif ($order->getMode() == "great" && $order->getState() == Yourdelivery_Model_Order::DELIVERERROR) {
                $order
                        ->setStatus(
                                Yourdelivery_Model_Order::NOTAFFIRMED,
                                 new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::RETARUS_OK_GREAT)                                   
                );
            }

            //defined in Bootstrap!
            hook_after_fax_is_ok($order);
                      
            //check for woopla service if available
            if ($this->_config->domain->base == 'taxiresto.fr') {
                $wooplaService = new Woopla_Connect();
                $wooplaService->setOrder($order);
                $wooplaService->call();
            }

        } elseif ($state == "NO_TRAIN" || $state == "EOD_FAILED") {
            $order
                    ->setStatus(
                            Yourdelivery_Model_Order::FAX_ERROR_NO_TRAIN,
                             new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::RETARUS_ERROR_NO_TRAIN, $stateMsg)                                                                               
            );
        } else {
            //all the rest are bad
            $this->_logging->info(sprintf('RETARUS: Failed reception of order #%s with code %s, meaning: %s', $order->getId(), $state, $stateMsg));
            //end out error message if fax was not delivered correctly
            $order
                    ->setStatus(
                            Yourdelivery_Model_Order::DELIVERERROR, 
                            new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::RETARUS_ERROR, $stateMsg)                                     
            );

            $text = "Bestellfax an " . $order->getService()->getName() . " ist nicht rausgegangen: " . $stateMsg . " // (#" . $order->getId() . ") von " . $order->getCustomer()->getFullname() . " // Tel: " . $order->getService()->getTel();
            if ($order->getCustomer()->isEmployee()) {
                $text .= " (" . $order->getCustomer()->getCompany()->getName() . ")";
            }

            $sms = new Yourdelivery_Sender_Sms();
            $sms->send2support($text);
        }
        
        return true;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @return array
     */
    protected function _getErrorStates() {
        
        return array(
            'BUSY' => __b('Die Leitung war besetzt.'),
            'NO_TRAIN' => __b('Beim Austausch der Faxkennung trat ein Fehler auf.'),
            'PAGE_SEND_ERROR' => __b('Das Fax konnte nicht übertragen werden, da z.B. das Papier zu Ende ging oder die Leitung unterbrochen wurde.'),
            'EOD_FAILED' => __b('Das Fax wurde nicht als empfangen bestätigt, ist jedoch häufig beim Empfänger angekommen.'),
            'REM_DIS' => __b('Gegenstelle hat aufgelegt.'),
            'NO_REMOTEINFO' => __b('Die Gegenstelle hat aufgelegt, bevor eine Vereinbarung über den Übertragungsmodus getroffen  werden konnte.'),
            'BAD_LINES' => __b('Es sind zu viele Zeilenfehler aufgetreten. Möglicherweise ist das Fax unleserlich.'),
            'FANS_GF' => __b('Die Verbindung wurde aufgrund von Protokollfehlern der Gegenstelle während der Protokollverhandlung abgebrochen.'),
            'DIAL_GF' => __b('Fehler beim Verbindungsaufbau. (ISDN Kanal B Fehler)'),
            'NOFAX' => __b('Der Anruf wurde nicht von einem Faxgerät, sondern von einem Modem, Anrufbeantworter oder einer Person entgegengenommen.'),
            'NOFAX_DIS' => __b('Die Gegenstelle hat die Verbindung aktiv beendet, z.B. hat eine Person den Anruf entgegengenommen und wieder aufgelegt.'),
            'NOFAX_TO' => __b('Zeitüberschreitung bei der Faxkommunikation.'),
            'RING_TO' => __b('Das angewählte Faxgerät reagiert nicht auf den Anruf. Mögliche Gründe sind ein abgeschaltetes Faxgerät,  eine automatische Umleitung des Anrufs auf ein   Telefon, oder das Papier des Faxgeräts ist zu Ende.'),
            'MISSING_FAX_FILE' => __b('Mindestens eine der zu versendenden Dateien fehlt oder der Dateiname ist nicht angegeben.'),
            'UNKNOWN_FAX_FILE' => __b('Mindestens eine der zu versendenden Dateien fehlt oder der Dateiname ist nicht angegeben.'),
            'DIAL_NC' => __b('Die Telefongesellschaft lehnt die angewählte Nummer aufgrund von Ungültigkeit oder Unvollständigkeit sofort  ab.'),
            'CHANGED_NO' => __b('Die Rufnummer hat sich geändert, Bandansage.'),
            'WOUT_TO' => __b('Nach Anwahl dieser Nummer erfolgte keine Reaktion (oft ist der Grund eine Nummer ohne Durchwahl zum Endgerät)'),
            'WRONG_NO' => __b('Falsche oder nicht vergebene Rufnummer.'),
            'TIFF_PAGE_ERR' => __b('Das Faxdokument war beschädigt.'),
            'RENDERING_ERROR' => __b('Das Dokument konnte nicht in ein Fax konvertiert werden, z.B. wurde versucht, eine geschützte PDF Datei zu versenden'),
            'BLACKLIST' => __b('Die angegebene Nummer ist in der von der Retarus geführten Robinson-Datenbank der BITKOM und ECOFAX verzeichnet und wurde nicht angewählt, weil die Blacklist-Option aktiviert war.'),
            'CANCELLED' => __b('Faxauftrag wurde durch einen Benutzer im Retarus EAS-Portal abgebrochen.')
        );
    }

    /**
     * Only for testing, never use in Production
     *
     * @param type $client
     */
    public function setMockObject($client) {
        if (!IS_PRODUCTION) {
            $this->_client = $client;
            $this->_method = 'api';
            $this->_isTesting = true;
        }
    }

}
