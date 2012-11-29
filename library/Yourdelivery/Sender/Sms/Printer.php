<?php

class Yourdelivery_Sender_Sms_Printer {

    /**
     * send a message to the sms printer
     * @author mlaug
     * @param string $to
     * @param string $pass
     * @param string $msg
     * @param Yourdelivery_Model_Order $order 
     * @return json
     */
    static public function send($to, $pass, $msg, Yourdelivery_Model_Order $order = null) {

        $logger = Zend_Registry::get('logger');
        

        if (is_object($order)) {
            //prepare message for sms printer
            $view = Zend_Registry::get('view');
            $view->setDir(APPLICATION_PATH . '/templates/sms/');
            $view->order = $order;
            $msg .= $view->render('order.htm');
            $view->setDir(null);

            //cleanup
            $msg = Default_Helpers_String::replaceUmlaute($msg);
            $msg = preg_replace("%[^\040-\176\r\n\t\243]%", '', $msg);
        }

        //prepare call
        $url = "https://sms2printer.com/manage/api/?apiver=1&imei=%s&password=%s&action=print&print_message=%s";
        $call = sprintf($url, $to, $pass, urlencode($msg));     
        $logger->debug('SMS PRINTER: ' . substr($call, 0, 200));

        $result = null;
        $xml = null;
        //parse response
        try {
            $content = file_get_contents($call);
            $xml = simplexml_load_string($content);
            $result = $xml->status->__toString();
            if ($result == 'success') {
                $msg = 'SMS PRINTER: successfully send out message to ' . $to;
                $logger->info($msg);
                return json_encode(array(
                    'result' => true,
                    'msg' => $msg
                ));
            }
        } catch (Exception $e) {

            $msg = 'SMS PRINTER: failed sending out message to ' . $to . ' because of : ' . $e->getMessage();
            $logger->warn($msg);
            return json_encode(array(
                'result' => false,
                'msg' => $msg
            ));
        }

        $msg = 'SMS PRINTER: failed sending out message to ' . $to . ' (result: "' . $result . '" - code: "' . $xml->error->code . '" - message: "' . $xml->error->message . '")';
        $logger->warn($msg);
        return json_encode(array(
            'result' => false,
            'msg' => $msg
        ));
    }

    /**
     * ping the sms printer
     * @author mlaug
     * @param string $to
     * @param string $pass
     * @return json
     */
    static public function ping($to, $pass) {

        $logger = Zend_Registry::get('logger');

        //prepare call
        $url = "https://sms2printer.com/manage/api/?apiver=1&imei=%s&password=%s&action=ping";
        $call = sprintf($url, $to, $pass);
        $xml = file_get_contents($call);

        $result = null;
        //parse response
        try {
            $content = file_get_contents($call);
            $xml = simplexml_load_string($content);
            $result = $xml->status->__toString();
            $status = $xml->{'printer_status'}->__toString();
            if ($status == 'online' && $result == 'success') {
                $msg = 'SMS PRINTER: successfully pinged ' . $to;
                $logger->info($msg);
                return json_encode(array(
                    'result' => true,
                    'msg' => $msg
                ));
            }
        } catch (Exception $e) {
            $msg = 'SMS PRINTER: failed ping to ' . $to . ' because of : ' . $e->getMessage();
            $logger->warn($msg);
            return json_encode(array(
                'result' => false,
                'msg' => $msg
            ));
        }

        $msg = 'SMS PRINTER: failed ping to ' . $to . ' because printer is offline';
        $logger->warn($msg);
        return json_encode(array(
            'result' => false,
            'msg' => $msg
        ));
    }

    /**
     * flash the light  
     * @author mlaug
     * @param string $to
     * @param string $pass
     * @return json
     */
    static public function flash($to, $pass) {

        $logger = Zend_Registry::get('logger');
        
        $url = "https://sms2printer.com/manage/api/?apiver=1&imei=%s&password=%s&action=status_led&status_led_value=flash";
        $call = sprintf($url, $to, $pass);
        $xml = file_get_contents($call);

        //parse response
        try {
            $content = file_get_contents($call);
            $xml = simplexml_load_string($content);
            $result = $xml->status->__toString();
            if ($result === 'success') {
                $msg = 'SMS PRINTER: successfully send flash to ' . $to;
                $logger->info($msg);
                return json_encode(array(
                    'result' => true,
                    'msg' => $msg
                ));
            }
        } catch (Exception $e) {

            $msg = 'SMS PRINTER: failed flashing to ' . $to . ' because of : ' . $e->getMessage();
            $logger->warn($msg);
            return json_encode(array(
                'result' => false,
                'msg' => $msg
            ));
        }

        $msg = 'SMS PRINTER: failed flashing to ' . $to . ' (result: "' . $result . '" - code: "' . $xml->error->code . '" - message: "' . $xml->error->message . '")';
        $logger->warn($msg);
        return json_encode(array(
            'result' => false,
            'msg' => $msg
        ));
    }

}
