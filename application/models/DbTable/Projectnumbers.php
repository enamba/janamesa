<?php

/**
 * @author mlaug
 */
class Yourdelivery_Model_DbTable_Projectnumbers extends Default_Model_DbTable_Base {

    /**
     * @var string
     */
    protected $_name = 'projectnumbers';

    /**
     * @var string
     */
    protected $_primary = 'id';
    
    /**
     * @var array
     */
    protected $_dependentTables = array(
        'Yourdelivery_Model_DbTable_Department_Projectnumbers',
    );
    
    /**
     * @var array
     */
    protected $_referenceMap = array(
        'Company' => array(
            'columns' => 'companyId',
            'refTableClass' => 'Yourdelivery_Model_DbTable_Company',
            'refColumns' => 'id'
        )
    );

}
