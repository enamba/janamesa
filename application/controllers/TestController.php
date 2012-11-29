<?php

class TestController extends Default_Controller_Base {

    public function testAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        /**
         * do something here
         */
        $r = new Yourdelivery_Model_Servicetype_Restaurant(22628);
        echo '<pre>';
        $helper = new Default_View_Helper_Openings_Format();
         echo $helper->formatOpeningsMerged($r->getOpening()->getIntervals(time()));
        #print_r($r->getOpening()->nextOpening(time(), strtotime('+7days')));
        echo '</pre>';
        return;
        $this->view->assign('r', $r);

        #var_dump($r->getOpening()->isOpen());
    }

    public function makehashAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        echo $string = $this->getRequest()->getParam('str');
        echo md5(SALT . $string . SALT);
    }

    public function infoAction() {
        gc_enable();
        phpinfo();


        $INFO = $MISS = array();
        foreach ($_SERVER as $v => $r) {
            if (substr($v, 0, 9) == 'HTTP_INFO') {
                if (!empty($r))
                    $INFO[substr($v, 10)] = $r;
                else
                    $MISS[substr($v, 10)] = $r;
            }
        }

        /* thanks Mike! */
        ksort($INFO);
        ksort($MISS);
        ksort($_SERVER);

        echo "Received These Variables:\n";
        print_r($INFO);

        echo "Missed These Variables:\n";
        print_r($MISS);

        echo "ALL Variables:\n";
        print_r($_SERVER);
    }

    public function deAction() {
        
    }

    public function plAction() {
        
    }

    public function account7Action() {
        
    }

    public function basketAction() {
        
    }

    public function taxirestoAction() {
        
    }

    public function voodooAction() {
        
    }

    public function gelbeseitenAction() {
        
    }

    public function refactoringAction() {
        
    }

    public function emailAction() {
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        // get all email templates
        $dir = APPLICATION_PATH . '/templates/email/';
        $handle = @opendir($dir);

        if (!$handle) {
            echo 'could not open directory ' . $dir;
            return;
        }

        $templates = array();
        while ($file = readdir($handle)) {
            if (strpos($file, 'htm') !== false && ($file != "." && $file != ".." && $file[0] != "_")) {
                $parts = null;
                $parts = explode('.', $file);
                $templates[] = $parts[0];
            }
        }
        closedir($handle);

        sort($templates);


        $cust = new Yourdelivery_Model_Customer_Company(1231, 1097);
        $order = new Yourdelivery_Model_Order(61667);
        $courierOrder = new Yourdelivery_Model_Order(192451);

        #return;

        foreach ($templates as $templateName) {
            $email = null;
            $email = new Yourdelivery_Sender_Email_Template($templateName);
            $email->setSubject('TestingMail ' . $templateName);
            $email->addTo($cust->getEmail());
            // assign some values
            $email->assign('from', '1305279764');
            $email->assign('until', '1305279799');

            $email->assign('cust', $cust);
            $email->assign('member', $cust);
            $email->assign('customer', $cust);

            #$email->assign('order', $order);
            $email->assign('order', $courierOrder);
            $email->assign('amount', '1234');

            $email->assign('code', 'fubar');
            $email->assign('discountPath', 'fubar-path');
            $email->assign('pass', 'my new password');
            $email->assign('password', 'my new password');

            $email->assign('yesadviseorderlink', 'rate/' . md5(SALT . $order->getNr()) . '/' . md5(SALT . 'yes'));
            $email->assign('noadviseorderlink', 'rate/' . md5(SALT . $order->getNr()) . '/' . md5(SALT . 'no'));


            $email->send();

            echo 'Email ' . $templateName . ' gesendet<br />';
        }
    }

    public function servicesinfomonthAction() {
        $servicesInfo = Yourdelivery_Statistics_Overallstats::getServicesInfoForMonth();

        $csv = new Default_Exporter_Csv();
        $csv->addRow('Restaurant');
        $csv->addRow('Umsatz in diesem Monat');
        $csv->addRow('Adresse');
        $csv->addRow('Stadt');
        $csv->addRow('Telefon');
        $csv->addRow('E-mail');
        $csv->addRow('Kontaktperson');
        $csv->addRow('Kontaktperson, Telefon');
        $csv->addRow('Kontaktperson, E-mail');

        foreach ($servicesInfo as $info) {
            $csv->addCol(
                    array(
                        'Restaurant' => $info['name'],
                        'Umsatz in April' => $info['sum'] / 100,
                        'Adresse' => $info['street'] . ' ' . $info['hausnr'],
                        'Stadt' => $info['plz'] . ' ' . $info['ort'],
                        'Telefon' => $info['tel'],
                        'E-mail' => $info['email'],
                        'Kontaktperson' => $info['conprename'] . ' ' . $info['conname'],
                        'Kontaktperson, Telefon' => $info['contel'],
                        'Kontaktperson, E-mail' => $info['conemail']
                    )
            );
        }

        $file = $csv->save();
        $email = new Yourdelivery_Sender_Email();
        $email->setSubject('Restaurants Info für diesen Monat');
        $email->addTo('team@yourdelivery.de');
        $attachment = $email->createAttachment(
                file_get_contents($file), 'text/comma-separated-values', Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64
        );
        $attachment->filename = 'restaurantsInfo.csv';
        $email->send();

        $this->view->assign('info', $servicesInfo);
    }

    public function testlightboxAction() {
        
    }

    public function testlightboxrequestAction() {
        
    }

}
