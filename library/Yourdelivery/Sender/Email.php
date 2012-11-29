<?php

/**
 * Description of Yourdelivery_Sender_Email
 * @author mlaug
 */
class Yourdelivery_Sender_Email extends Yourdelivery_Sender_Email_Abstract {

    /**
     * Quick send
     * @author vpriem
     * @since 14.11.2010
     * @param string $subject
     * @param string $message
     * @param string $pdf
     * @param string $to
     * @param string $cc
     * @return Zend_Email
     */
    public static function quickSend($subject, $body, $pdf = null, $to = "developers", $cc = null) {

        $email = new Yourdelivery_Sender_Email();
        $email
                ->addTo($to)
                ->setSubject('Yourdelivery' . ($to == "developers" ? " Developer" : "") . ': ' . $subject);
        if ($pdf !== null) {
            $email->attachPdf($pdf);
        }
        if (strpos($body, "<") !== false && strpos($body, ">") !== false) {
            $email->setBodyHtml($body);
        } else {
            $email->setBodyText($body);
        }
        if ($cc !== null) {
            $email->addCc($cc);
        }
        return $email->send('system');
    }

    /**
     * If something happens within the system (like an placed order)
     * we send out a notify to the support
     * @author vpriem
     * @since 14.11.2010
     * @return Zend_Email
     */
    public static function notify($text, $pdf = null, $onlyDeveloper = false, $subject = 'Notify', $cc = null) {

        $email = new Yourdelivery_Sender_Email();
        $email
                ->addTo($onlyDeveloper ? "developers" : "EMAIL")
                ->setSubject("Yourdelivery" . ($onlyDeveloper ? " Developer" : "") . ": " . $subject)
                ->setBodyHtml($text);

        if ($pdf !== null) {
            $email->attachPdf($pdf, 'bestellzettel.pdf');
        }

        if ($cc !== null) {
            $email->addCc($cc);
        }

        return $email->send('system');
    }

    /**
     * Send out a warning to the support. This message should rise attention, but
     * mostly no further action are necessary
     * @author vpriem
     * @since 14.11.2010
     * @return Zend_Email
     */
    public static function warning($text, $pdf = null, $onlyDeveloper = false) {

        $email = new Yourdelivery_Sender_Email();
        $email
                ->addTo($onlyDeveloper ? "developers" : "EMAIL")
                ->setSubject("Yourdelivery" . ($onlyDeveloper ? " Developer" : "") . ": Warning")
                ->setBodyHtml($text);

        if ($pdf !== null) {
            $email->attachPdf($pdf, 'bestellzettel.pdf');
        }

        return $email->send('system');
    }

    /**
     * Send out an error mail to support or only to
     * developers if $onlyDeveloper flag is true
     * @todo: extend this method to except Exceptions and parse them into the email
     * @author vpriem
     * @since 14.11.2010
     * @return Zend_Email
     */
    public static function error($html, $onlyDeveloper = false) {

        $email = new Yourdelivery_Sender_Email();
        return $email
                        ->addTo($onlyDeveloper ? "developers" : "EMAIL")
                        ->setSubject("Yourdelivery" . ($onlyDeveloper ? " Developer" : "") . ": Error")
                        ->setBodyHtml(sprintf(
                                        '<h2>Fehlermeldung:</h2>
                <hr />
                <p>%s</p>', nl2br($html)))
                        ->send('system');
    }

    /**
     * Send out the a confidential email, which will only be sent to a
     * specific group
     * @author vpriem
     * @since 14.11.2010
     * @return Zend_Email
     */
    public static function confidence($html) {

        $email = new Yourdelivery_Sender_Email();
        return $email
                        ->addTo(array(
                            'EMAIL(s)'
                        ))
                        ->setSubject("Yourdelivery: Confidence")
                        ->setBodyHtml($html)
                        ->send('system');
    }

    /**
     * Send out an email, that alerts the support, that a fraud has
     * been detected while using the system
     * @author vpriem
     * @since 14.11.2010
     * @return Zend_Email
     */
    public static function fraud($text, $pdf = null, $onlyDeveloper = false) {

        $email = new Yourdelivery_Sender_Email();
        return $email
                        ->addTo($onlyDeveloper ? "developers" : "EMAIL")
                        ->setSubject("Yourdelivery" . ($onlyDeveloper ? " Developer" : "") . ": FRAUD DETECTION")
                        ->setBodyText($text);

        if ($pdf !== null) {
            $email->attachPdf($pdf, 'bestellzettel.pdf');
        }

        return $email->send('system');
    }

    /**
     * Send out an email to osticket
     * @author vpriem
     * @since 27.01.2011
     * @param string $name supporter name
     * @param string $subject subject
     * @param string $text message
     * @param string $send boolean
     * @return boolean|Yourdelivery_Sender_Email
     */
    public static function osTicket($supporter, $subject, $text, $send = true) {

        $config = Zend_Registry::get('configuration');
        
        $osTicketConf = $config->osTicket;
        
        if (!is_null($osTicketConf)) {
            $data = $osTicketConf->toArray();
            $supporterConf = $data[$supporter];
        }

        if (is_null($supporterConf)) {
           $to = array("Samson Tiffy", "EMAIL");
        }
        else {
            $to = array($supporterConf['name'], $supporterConf['email']);
        }

        $email = new Yourdelivery_Sender_Email();
        $email->addTo($to[1])
              ->setSubject("[ticketto:" . $to[0] . "] " . $subject)
              ->setBodyText($text);
        if ($send) {
            return $email->send('system');
        }
        return $email;
    }

    /**
     * sent out an email with an attached pdf, which is verified by
     * www.signaturportal.de and send through to the customer
     * @author mlaug
     * @since 03.02.2011
     * @param string $to
     * @param string $pdf
     * @return boolean
     */
    public static function verifyPdf($to, $pdf) {

        if (!IS_PRODUCTION) {
            $to = 'laug@lieferando.de';
        }

        if (!file_exists($pdf)) {
            return false;
        }

        // create another transport and use sigmail gateway
        $transport = new Zend_Mail_Transport_Smtp('sigmail.de', array(
                    'auth' => 'login',
                    'username' => 'USERNAME',
                    'password' => 'PASSWORD',
                    'port' => '25',
                    'ssl' => 'tls'
                ));

        $email = new Yourdelivery_Sender_Email();
        return $email
                        ->addTo($to)
                        ->setSubject(__('Ihre signierte Rechnung'))
                        ->setBodyText('')
                        ->attachPdf($pdf)
                        ->setFrom('EMAIL')
                        ->send('customer', $transport);
    }

}
