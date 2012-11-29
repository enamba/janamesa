<?php
/**
 * Description of Tree
 *
 * @author mlaug
 */
class Yourdelivery_Model_DbTable_Department_Projectnumbers extends Default_Model_DbTable_Base {

    protected $_name = 'department_projectnumbers';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';

    protected $_referenceMap    = array(
        'Projectnumbers' => array(
            'columns'           => 'projectnumbersId',
            'refTableClass'     => 'Yourdelivery_Model_DbTable_Projectnumbers',
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
