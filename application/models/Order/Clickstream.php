<?php

/**
 * Description of Budget
 * @package order
 * @author mlaug
 */
class Yourdelivery_Model_Order_Clickstream extends Default_Model_Base {

    /**
     * store the clickstream, which is base64 encoded
     * @author mlaug
     * @since 26.08.2011
     * @param string $stream 
     */
    public function store($stream, $orderId) {
        $clicks = explode('||', $stream);
        $firstHit = $clicks[0];
        unset($clicks[0]);        
        foreach ($clicks as $click) {
            $click = explode('::', base64_decode($click));      
            if (count($click) == 2) {
                $this->getTable()->createRow(
                        array(
                            'orderId' => $orderId,
                            'hit' => date(DATETIME_DB,($click[0])),
                            'url' => $click[1]
                        )
                )->save();
            }
        }
    }

    /**
     * get table
     * @author mlaug
     * @return Yourdelivery_Model_DbTable_Order_BucketMeals
     */
    public function getTable() {
        if (is_null($this->_table)) {
            $this->_table = new Yourdelivery_Model_DbTable_Order_Clickstream();
        }
        return $this->_table;
    }

}

?>
