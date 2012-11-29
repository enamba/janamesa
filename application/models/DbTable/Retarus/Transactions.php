<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Transactions
 *
 * @author daniel
 */
class Yourdelivery_Model_DbTable_Retarus_Transactions extends Zend_Db_Table_Abstract {

    //put your code here
    /**
     * Table name
     * @var string
     */
    protected $_name = 'retarus_transactions';

    /**
     * Primary key name
     * @var string
     */
    protected $_primary = 'id';

    /**
     * Get transactions by orderId
     * @author mlaug
     * @since 31.01.2011
     * @param int $orderId
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getByUniqueId($uniqueId) {
        return $this->fetchAll(
                        $this->select()
                                ->where("`uniqueId` = ?", $uniqueId)
        );
    }

}

?>
