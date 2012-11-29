<?php

/**
 * Description of FinishNotRegistered
 *
 * @author mlaug
 */
class Yourdelivery_Form_Order_Finish_Private extends Yourdelivery_Form_Order_Finish_Abstract{

    public function init() {
        
        $this->customer();
        $this->location();
        $this->additional();
        $this->contact();
        
    }

}

