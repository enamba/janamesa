<?php

/**
 * @package Yourdelivery
 * @subpackage SMS
 * @author Daniel Scain <farenzena@lieferando.de>, Marek Hejduk <m.hejduk@pyszne.pl>
 * @since 17.08.2012
 */
class Yourdelivery_Sender_Sms_SmsNewsMedia {
    /**
     * Default timeout for SMS request
     * @var integer 
     */
    const DEFAULT_TIMEOUT = 60;

    /**
     * Max size of read response length
     * @var integer
     */
    const RESPONSE_MAXLENGTH = 40000;

    /**
     * Success status
     * @var integer
     */
    const RESPONSE_STATUS_OK = 100;

    /**
     * @var Zend_Config 
     */
    private $_config;

    /**
     * @var Zend_Log
     */
    private $_logger;

    /**
     * Status code and messages for each service's request status.
     * @var array
     */
    private static $responseStatuses = array(
        100 => "Request received for processing. (100)",
        150 => "Password or username incorrects. (150)",
        159 => "Service ID unknown (should be SMS). (159)",
        160 => "Message not found (check the meaning of this with the service provider). (160)",
        200 => "Requested XML is not well formed or is incomplete. (200)",
        254 => "The request was deleted (check with the service provider the reason). (254)"
    );

    /**
     * @author Daniel Scain <farenzena@lieferando.de>
     * @since 22.08.2011
     */
    public function __construct() {
        $this->_config = Zend_Registry::get('configuration');
        $this->_logger = Zend_Registry::get('logger');
    }

    /**
     * Send a message via a SMS-News Media server
     * @author Daniel Scain <farenzena@lieferando.de>, Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 17.08.2012
     * @param string $to
     * @param string $msg
     * @return boolean
     */
    public function sendSmsMessage($to, $msg) {
        try {
            $phone = $this->preparePhoneNumber($to);
            $xmlMessage = $this->prepareMessage($phone, $msg);
            $this->sendRequest($xmlMessage);
            // if we are here, it means success
            $this->_logger->info("SMSNewsMedia: SMS successfully send to " . $to);
            return true;
        } catch (UnexpectedValueException $uEx) {
            // incorrect phone number and/or message
            $errorMessage = "SMSNewsMedia: SMS preparation failure - " . $uEx->getMessage();
        } catch (ErrorException $eEx) {
            // SMS request failure
            $errorMessage = "SMSNewsMedia: SMS sending out failure - " . $eEx->getMessage();
        }
        // if we are here, it means failure
        $this->_logger->err($errorMessage);
        Yourdelivery_Sender_Email::error($errorMessage, true);
        return false;
    }

    /**
     * Normalizes passed raw phone number and returns the result
     *
     * @author Daniel Scain <farenzena@lieferando.de>, Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 29.08.2012
     *
     * @param string $to
     * @return string
     * @throws UnexpectedValueException
     */
    private function preparePhoneNumber($to) {
        $phone = Default_Helpers_Normalize::telephone($to);
        if ($phone === false) {
            throw new UnexpectedValueException('Could not normalize phone number: ' . $to);
        }
        return $phone;
    }

    /**
     * Generates XML document containing SMS content
     *
     * @author Daniel Scain <farenzena@lieferando.de>, Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 29.08.2012
     *
     * @param string $phone
     * @param string $msg
     * @return string
     * @throws UnexpectedValueException
     */
    private function prepareMessage($phone, $msg) {
        if (!strlen($msg)) {
            throw new UnexpectedValueException('Message content is empty');
        }

        // Preparing XML document
        $smsConfig = $this->_config->sender->sms;
        $sendTime = $this->getTimestamp();
        $xmlString = <<< XML
<?xml version="1.0"?>
<ident user="{$smsConfig->username}" pass="{$smsConfig->password}">
    <content id="100" sendtime="$sendTime">
        <message></message>
        <target structur="adresse">$phone</target>
    </content>
</ident>
XML;

        // This conversion to XML object is important because SMSNewsMedia will only accepting XML escaped
        // characters with numbers and not mnemonics, like &uuml;. This could be done with htmlentities and option ENC_XML1,
        // but only starting in PHP 5.4.
        $xml = new SimpleXMLElement($xmlString);
        $xml->content[0]->message = $msg;
        return $xml;
    }

    /**
     * Pushes a request sending formatted message out
     *
     * @author Daniel Scain <farenzena@lieferando.de>, Marek Hejduk <m.hejduk@pyszne.pl>
     * @since 29.08.2012
     *
     * @param SimpleXMLElement $xmlMessage
     * @return void
     * @throws ErrorException
     */
    private function sendRequest($xmlMessage) {
        $smsConfig = $this->_config->sender->sms;
        $context = stream_context_create(array('http' => array(
            'method' => 'POST',
            'header' => "Content-type: application/x-www-form-urlencoded",
            'content' => "xml_daten=" . urlencode($xmlMessage->asXML()),
            'timeout' => $smsConfig->timeout || self::DEFAULT_TIMEOUT
        )));
        $rawResult = file_get_contents($smsConfig->url, false, $context, -1, self::RESPONSE_MAXLENGTH);

        // Response analysis
        if ($rawResult === false) {
            throw new ErrorException('HTTP request failure');
        }
        $XmlResult = simplexml_load_string(trim($rawResult));
        if ($XmlResult === false) {
            throw new ErrorException('HTTP response is not a valid XML document');
        }

        // Note that status is an attribute of XML root element, not an element itself
        if (isset($XmlResult['status'])) {
            $status = $XmlResult['status'];
            if ($status == self::RESPONSE_STATUS_OK) {
                // success
                return ;
            } elseif (isset(self::$responseStatuses[$status])) {
                throw new ErrorException(
                    'XML status of HTTP response means a failure: ' .
                    self::$responseStatuses[$status]
                );
            } else {
                throw new ErrorException("XML status of HTTP response: $status is unknown");
            }
        } else {
            throw new ErrorException('XML of HTTP response does not contain status');
        }
    }

    /**
     * TODO: contact SMSNewsMedia to specify better the behavior on timestamps in the past.
     * Generate a timestamp formatted according to SMSNewsMedia format.
     * @author Daniel Scain <farenzena@lieferando.de>
     * @since 17.08.2012
     * @param integer $time is a UNIX format timestamp. 
     * @return integer
     */
    private function getTimestamp($time = null) {
        if ($time == null) {
            $time = time();
        }

        /* Original time will get added by a few seconds because SMSNewsMedia service 
         * does not specify the behavior of the service when the timestamp is current time 
         * or a past time, so we give it an error margin. */
        $formatted = date("ymdHis", $time + 5);
        return $formatted;
    }

}

/** Constants below are not complete and should be checked with SMS News Media service. The SMS News Media documentation is not clear
        * enough in a few states. Also, we currently gave up of checking status of messages, so this work is currently halted.
       * 
       * Also, before following with that, we need an async process such as a cronjob or RabbitMQ for that.
       **/
/*
class SMSNewsMediaStatus {
      const STATUS_MESSAGESENT = "100";
      const STATUS_VERSANDZEIT_NICHT_EINDEUTIG__NACHRICHT_WURDE_SOFORT_VERSAND = "101";
      const STATUS_VERSANDZEIT_BENUTZERNAME_ODER_PASSWORT_FALSCH_ODER_NICHT_BEKANNT = "150";
      const STATUS_VERSANDZEIT_XML_DEFINITION_NICHT_EINGEHALTEN = "151";
      const STATUS_VERSANDZEIT_DIENST_NICHT_VERFUUGBAR = "152";
      const STATUS_VERSANDZEIT_FUUR_DIESEN_DIENST_NICHT_FREIGESCHALTET = "153";
      const STATUS_VERSANDZEIT_MESSAGE_BLOCK_NICHT_DEFINIERT = "154";
      const STATUS_VERSANDZEIT_CONTENT_BLOCK_NICHT_DEFINIERT = "155";
      const STATUS_VERSANDZEIT_TARGET_BLOCK_NICHT_DEFINIERT = "156";
      const STATUS_VERSANDZEIT_TARGET_BLOCK_NICHT_EINDEUTIG = "157";
      const STATUS_VERSANDZEIT_CONTENT_BLOCK_NICHT_DEFINIERT = "155";
      const STATUS_VERSANDZEIT_CONTENT_BLOCK_NICHT_DEFINIERT = "155";
      // We don't use appendix, therefore it was excluded for simplicity.   => 102 mehr als eine Anlage (appendix) und kein Reihenfolge definiert, Versand erfolgt
      // Not clear                                                          => 103 Sendebericht bei Absenderkennung SMS wurde Versand
       
      /** Will leave incomplete from here. (see phpDoc from class)
       *       158 Guthaben erschöpft (werden mehrere Nachrichten in einem Block übertragen wird keine versendet sondern der gesamte Block gesperrt)
       *       159 Dienste-ID unbekannt Nachricht nicht gefunden
       *       200 Ausnahmefehler / XML Script unvollständig
       *       201 bis 250 Ausnahmefehler
       *       254 Nachricht durch User gelöscht
       *       255 Nachricht gelöscht
     **/
//}