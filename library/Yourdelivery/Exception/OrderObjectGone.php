<?php
/**
 * Description of AlreadyFinished
 * @package exception
 * @author mlaug
 */
class Yourdelivery_Exception_OrderObjectGone extends Zend_Exception{

    public function  __construct($msg = '', $code = 0, Exception $previous = null) {
        parent::__construct('Order object has gone away :(', $code, $previous);
    }

}
?>
