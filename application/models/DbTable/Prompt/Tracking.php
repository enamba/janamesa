<?php
/**
 * Prompt Tracking
 * @author mlaug
 */
class Yourdelivery_Model_DbTable_Prompt_Tracking extends Default_Model_DbTable_Base{

    /**
     * Table name
     * @var string
     */
    protected $_name = 'prompt_tracking';

    /**
     * Primary key name
     * @var string
     */
    protected $_primary = 'id';

    /**
     * Reference to
     * @var array
     */
    protected $_referenceMap = array(
        'Order' => array(
            'columns'       => 'orderId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Order',
            'refColumns'    => 'id'
        )
    );

    /**
     * Get trackings
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
