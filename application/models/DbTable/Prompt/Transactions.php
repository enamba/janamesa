<?php
/**
 * Prompt Transaction Db Table
 * @author mlaug
 */
class Yourdelivery_Model_DbTable_Prompt_Transactions extends Default_Model_DbTable_Base{

    protected $_rowClass = 'Yourdelivery_Model_DbTableRow_Prompt_Transactions';

    /**
     * Table name
     * @var string
     */
    protected $_name = 'prompt_transactions';

    /**
     * Primary key name
     * @var string
     */
    protected $_primary = 'id';
    
    /**
     * Get transactions
     * @author vpriem
     * @since 29.09.2010
     * @param int $orderId
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getByOrder ($orderId) {

        return $this->fetchAll(
            $this->select()
                 ->where("`orderId` = ?", $orderId)
        );

    }

}

/**
 * Prompt Transaction Db Table Row
 * @author vpriem
 * @since 29.09.2010
 */
class Yourdelivery_Model_DbTableRow_Prompt_Transactions extends Zend_Db_Table_Row_Abstract{

    /**
     * @author vpriem
     * @since 29.09.2010
     * @return string
     */
    public function getResultMsg(){

        $result = unserialize($this->result);
        if ($result['code']) {
            return $result['msg'];
        }
        return "";

    }

}