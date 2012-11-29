<?php
/**
 * Description of Tree
 *
 * @author mlaug
 */
class Yourdelivery_Model_DbTable_Department_Customer extends Default_Model_DbTable_Base {

    protected $_name = 'department_customer';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';

    protected $_referenceMap    = array(
        'Customer' => array(
            'columns'           => 'customerId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Customer',
            'refColumns'        => 'id'
        ),
        'Department' => array(
            'columns'           => 'departmentId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Department',
            'refColumns'        => 'id'
        )
    );


}
?>
