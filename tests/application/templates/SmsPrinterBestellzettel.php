<?php

/**
 * @author Felix Haferkorn <haferkorn@lieferando.de>
 */
/**
 * @runTestsInSeparateProcesses 
 */
class SmsPrinterBestellzettelTest extends Yourdelivery_Test {

    public function testRenderingBestellzettelTemplate() {
        
        $order = new Yourdelivery_Model_Order($this->placeOrder());
        
        $view = Zend_Registry::get('view');
        $view->setDir(APPLICATION_PATH . '/templates/sms');
        $view->order = $order;
        $msg .= $view->render('order.htm');
    }

}
