<?php

/**
 * Description of Interfax
 * @since 24.01.2011
 * @author mlaug
 */
class Yourdelivery_Sender_Fax_Interfax implements Yourdelivery_Sender_Fax_Interface {

    /**
     * @var string
     */
    protected $_username = 'USERNAME';
    /**
     * @var string
     */
    protected $_password = 'PASS';
        
    /**
     * @var string
     */
    protected $_wsdl = null;
    
    /**
     * @var Zend_Log
     */
    protected $_logging = null;
    protected $_mockObject = null;

    /**
     * @author mlaug
     * @since 24.01.2011
     * @param string $username
     * @param string $password
     */
    public function __construct($username = null, $password = null) {

        //load config
        $this->_config = Zend_Registry::get('configuration');
        
        
        if ($username === null) {
            $this->_username = $this->_config->sender->fax->interfax->username;
        }else{
             $this->_username = $username;
        }

        if ($username === null) {
            $this->_password = $this->_config->sender->fax->interfax->password;
        }else {
            $this->_password = $password;
        }
        
        $this->_wsdl = $this->_config->sender->fax->interfax->wsdl;       
        
        $this->_logging = Zend_Registry::get('logger');

        
    }

    /**
     * @author mlaug
     * @since 24.01.2011
     * @param string $to
     * @param string $pdf
     * @param string $type
     * @param int $unique
     * @return int
     */
    public function send($to, $pdf, $type, $unique = null) {

        if (file_exists($pdf)) {

            if (!IS_PRODUCTION) {
                $to = "TELEPHONE";
            }

            $params = new stdClass();
            $params->Username = $this->_username;
            $params->Password = $this->_password;
            $params->FaxNumber = $to;
            $params->FileData = file_get_contents($pdf);
            $params->FileType = 'PDF';

            $client = new SoapClient($this->_wsdl);

            if (IS_PRODUCTION) {
                try {
                    $result = $client->Sendfax($params);
                } catch (Exception $e) {
                    $this->_logging->crit('INTERFAX: ' . $e->getMessage());
                    return 0;
                }
            } else {
                $result = new stdClass();
                $result->SendfaxResult = '123456789';

                // send out email in development
                $email = new Yourdelivery_Sender_Email_Template('faxdevel.txt');
                $email->addTo("samson@tiffy.de") // will trigger the testing email adress
                        ->setSubject('FAX TEST VERSAND')
                        ->attachPdf($pdf)
                        ->send();
            }

            if ($result instanceof stdClass) {

                /**
                 * save this transaction to be validated
                 */
                switch ($type) {
                    default:
                    case 'order':
                        $table = new Yourdelivery_Model_DbTable_Interfax_Transactions();
                        $row = $table->createRow(array(
                                    'orderId' => $unique,
                                    'transactionId' => $result->SendfaxResult,
                                ));
                        if ($result->SendfaxResult == '-150') {
                            $row->currentStatus = 'error';
                        }
                        $row->save();
                        break;
                }

                return $result->SendfaxResult;
            }
        }

        return 0;
    }

    /**
     * @author mlaug
     * @since 31.01.2011
     */
    public function processReports() {

        $client = new SoapClient($this->_wsdl, array('trace' => 1));

        $params = new stdClass();
        $params->Username = $this->_username;
        $params->Password = $this->_password;
        $params->MaxItems = 1;
        $params->Verb = 'EQ';
        $params->ResultCode = '';
        

        $table = new Yourdelivery_Model_DbTable_Interfax_Transactions();
        $transactions = $table->fetchAll("`currentStatus` = 'unconfirmed'");
        foreach ($transactions as $transaction) {
            
            $storno = false;
            
            $orderId = (integer) $transaction->orderId;
            if ($orderId > 0) {
                try {
                    $order = new Yourdelivery_Model_Order($orderId);
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    $this->_logging->crit(sprintf('INTERFAX: Could not get order object by given id %d', $orderId));

                    $transaction->currentStatus = 'unable to find order';
                    $transaction->save();

                    continue;
                }
                
                if($order->getState() == Yourdelivery_Model_Order::STORNO){
                    $storno = true;
                }
                
                $params->VerbData = $transaction->transactionId;
                // no valid transaction id? continue and set to error
                if ($transaction->transactionId < 0 && !$storno) {
                    $transaction->currentStatus = 'error';
                    $transaction->save();

                    $this->_logging->warn('INTERFAX: Failed sending order #' . $order->getId() . ' got negative transaction id');
                    // end out error message if fax was not delivered correctly
                    $order
                            ->setStatus(Yourdelivery_Model_Order::DELIVERERROR, 
                                                 new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::INTERFAX_ERROR, $state) 
                                    );

                    continue;
                }


                //Mock Result Object for testing
                if (is_null($this->_mockObject)) {
                    try {
                        $queryResult = $client->FaxQuery($params);
                        $transaction->lastResponse = $client->__getLastResponse();
                    } catch (Exception $e) {
                        $this->_logging->crit('INTERFAX: ' . $e->getMessage());
                        continue;
                    }
                } else {
                    $queryResult = $this->_mockObject;
                }



                if ($queryResult->ResultCode == 0) {
                    if ($queryResult->FaxQueryResult->FaxItemEx) {
                        $state = $queryResult->FaxQueryResult->FaxItemEx->Status;
                        if ($state == 0) {
                            if ($storno) {
                                $this->_logging->info('INTERFAX: Confirm cancellation of order #' . $order->getId());
                                //do nothing more
                            } else {
                                /**
                                 * @todo: this is redundant with retarus report check
                                 * should be refactored in one solid function!
                                 * http://tickets.yourdelivery.de:3000/issues/3143
                                 */
                                $this->_logging->info('INTERFAX: Confirm reception of order #' . $order->getId());
                                // affirm order
                                if ($order->getMode() != "great") {
                                    if ($order->getState() < Yourdelivery_Model_Order::AFFIRMED) {
                                        $order
                                                ->setStatus(Yourdelivery_Model_Order::AFFIRMED,
                                                          new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::INTERFAX_OK) 
                                                 );
                                    }
                                } elseif ($order->getMode() == "great" && $order->getState() == Yourdelivery_Model_Order::DELIVERERROR) {
                                    $order
                                            ->setStatus(Yourdelivery_Model_Order::NOTAFFIRMED,
                                                             new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::INTERFAX_OK_GREAT) 
                                                    );
                                }

                                // defined in Bootstrap!
                                hook_after_fax_is_ok($order);

                                // check for woopla service if available
                                if ($this->_config->domain->base == 'taxiresto.fr') {
                                    $wooplaService = new Woopla_Connect();
                                    $wooplaService->setOrder($order);
                                    $wooplaService->call();
                                }
                            }
                            
                            $transaction->currentStatus = 'affirmed';
                            
                        } elseif ($state == -22) {
                            $this->_logging->info(sprintf('INTERFAX: Failed reception of %sorder #%s with code %s', $storno ? 'cancellation-' : '', $order->getId(), $state));
                            // send out error message if interfax has no money
                            $storno ? null :
                            $order
                                    ->setStatus(Yourdelivery_Model_Order::DELIVERERROR,
                                                        new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::INTERFAX_ERROR_NO_CREDIT, $state) 
                                                        );

                            $transaction->currentStatus = 'error';


                            //processing fax
                        } elseif ($state < 0 && $state != -22 && $transaction->processCount < 8) {
                            $this->_logging->info(sprintf('INTERFAX: Fax is being handled, %sorder #%s with code %s', $storno ? 'cancellation-' : '', $order->getId(), $state));

                            $transaction->processCount += 1;

                            //timeout after 9 tries
                        } elseif ($state < 0 && $state != -22 && $transaction->processCount >= 8) {
                            $this->_logging->info(sprintf('INTERFAX: Failed reception of %sorder #%s with code %s - Fax is being handled:Timeout', $storno ? 'cancellation-' : '', $order->getId(), $state ));
                            // end out error message if fax was not delivered correctly
                            $storno ? null :
                            $order
                                    ->setStatus(Yourdelivery_Model_Order::FAX_ERROR_NO_TRAIN,
                                                          new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::INTERFAX_ERROR_NO_TRAIN, $state) 
                                       );

                            $transaction->currentStatus = 'error';
                        } else {

                            $errors = $this->getErrorCodes();
                            if (!empty($errors[(string) $state])) {
                                $statusMessage = " - " . $errors[(string) $state];
                            } else {
                                $statusMessage = "";
                            }

                            $this->_logging->info(sprintf('INTERFAX: Failed reception of %sorder #%s with code %s', $storno ? 'cancellation-' : '', $order->getId(), $state));
                            // end out error message if fax was not delivered correctly
                            $storno ? null :
                            $order
                                    ->setStatus(Yourdelivery_Model_Order::DELIVERERROR,
                                                         new Yourdelivery_Model_Order_StatusMessage(Yourdelivery_Model_Order_StatusMessage::INTERFAX_ERROR, $state . $statusMessage) 
                                            );

                            $transaction->currentStatus = 'error';
                        }
                        $transaction->save();
                    } else {
                        $this->_logging->warn(sprintf('INTERFAX: Receiving queryResult with ResultCode %s for transaction #%s, cannot do anything', $queryResult->ResultCode, $transaction->transactionId));
                    }
                } else {
                    $this->_logging->crit(sprintf('INTERFAX: Error receiving information for transaction #%s', $transaction->transactionId));
                }
            }
        }
    }

    /**
     * test fax
     * @author mlaug
     * @param string $to
     * @return boolean
     */
    public function test($to) {

        $config = Zend_Registry::get('configuration');
        $pdf = APPLICATION_PATH . "/templates/fax/testfax/ydtestfax-" . $config->domain->base . ".pdf";
        return $this->send($to, $pdf, 'testfax');
    }

    /**
     *
     *  errorcodes geparsed von http://www.interfax.net/en/help/error_codes
     *
     * var trs = $("tr[bgcolor="#FFE8E5"]);
     *
     * var codes = "";


      $(trs).each(function(index, item) {
      var key = $(item).children("th").text();

      var text = $(item).children("td")[0];

      codes  += "'" + key + "' => '" + $(text).text() + "',\n";
      //                                     console.log(key + ": " + $(text).text());
      })

      console.log(codes)

     *
     * @return array
     */
    protected function getErrorCodes() {

        return array(
            '256' => 'Internal error',
            '263' => 'Busy',
            '403' => 'Fax manually canceled.',
            '3072' => 'Telephony error',
            '3080' => 'Telephony error',
            '3211' => 'Fax machine incompatibility',
            '3220' => 'Fax machine incompatibility',
            '3223' => 'An unexpected disconnect command was sent',
            '3224' => 'The remote fax machine failed to respond ',
            '3225' => 'Fax machine incompatibility',
            '3230' => 'A disconnect message was received while attempting to negotiate transmission',
            '3231' => 'Fax machine incompatibility',
            '3233' => 'Fax machine incompatibility',
            '3264' => 'Fax machine incompatibility',
            '3267' => 'Fax machine incompatibility',
            '3268' => 'Transmission error (after page break)',
            '3269' => 'Fax machine incompatibility',
            '3300' => 'Telephony error',
            '3510' => 'Telephony error',
            '3830' => 'Telephony error',
            '3912' => 'Phone number not operational',
            '3931' => 'Busy',
            '3932' => 'Phone number not operational',
            '3933' => 'Busy', '3935' => 'No answer (might be out of paper)',
            '3936' => 'Human voice answer',
            '3937' => 'Ring busy',
            '3938' => 'Phone number not operational',
            '6001' => 'Phone number not operational',
            '6002' => 'No route available',
            '6003' => 'Telephony error',
            '6004' => 'Telephony error',
            '6016' => 'Telephony error',
            '6017' => 'Busy',
            '6018' => 'No answer (Might be out of paper)',
            '6019' => 'Telephony error',
            '6021' => 'Call rejected ',
            '6022' => 'Number changed ',
            '6027' => 'Phone number not operational',
            '6028' => 'Phone number not operational',
            '6029' => 'Call rejected ',
            '6031' => 'Telephony error',
            '6034' => 'Telephony error',
            '6038' => 'Telephony error',
            '6041' => 'Telephony error',
            '6042' => 'Telephony error',
            '6043' => 'Telephony error',
            '6044' => 'Telephony error',
            '6047' => 'Telephony error',
            '6050' => 'Telephony error',
            '6054' => 'Telephony error',
            '6057' => 'Telephony error',
            '6058' => 'Telephony error',
            '6063' => 'Telephony error',
            '6065' => 'Telephony error',
            '6069' => 'Telephony error',
            '6079' => 'Telephony error',
            '6088' => 'Incompatible destination',
            '6095' => 'Incompatible destination',
            '6097' => 'Incompatible destination',
            '6099' => 'Incompatible destination',
            '6100' => 'Incompatible destination',
            '6102' => 'Telephony error',
            '6111' => 'Telephony error',
            '6127' => 'Telephony error',
            '7004' => 'Telephony error',
            '7012' => 'Telephony error',
            '7013' => 'Telephony error',
            '8010' => 'The remote fax machine hung up before receiving fax',
            '8021' => 'No answer',
            '8025' => 'Busy',
            '204000' => 'Rendering error',
            '204001' => 'Rendering error',
            '205000' => 'Quota exceeded (Prepaid card depleted)',
            '205001' => 'Internal System error (FindRoute)',
            '206001' => 'Internal System Error (LocalSender)');
    }

    public function setTestingObject($object) {

        $this->_mockObject = $object;
    }

}
