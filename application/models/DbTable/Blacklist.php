<?php

/**
 * @author Matthias Laug <laug@lieferando.de>
 * @since 12.06.2012
 */
class Yourdelivery_Model_DbTable_Blacklist extends Default_Model_DbTable_Base {

    /**
     * Table name
     * @var string 
     */
    protected $_name = 'blacklist';

    /**
     * Primary key name
     * @var string 
     */
    protected $_primary = 'id';
    
    /**
     * get a select for the grids
     * 
     * @author Matthias Laug <laug@lieferando.de>
     * @since 12.06.2012
     * @param array $types
     * @return Zend_Db_Select 
     */
    public static function getGridSelect(array $types) {
        
        $adapter = Zend_Registry::get('dbAdapterReadOnly');
        $select = $adapter->select();

        $rowList = array();
        foreach ($types as $type) {
            $rowList[$type] = new Zend_Db_Expr(sprintf("GROUP_CONCAT(IF(bv.type = '%s', bv.value, NULL))", $type));
        }
        
        $select->from(array('b' => 'blacklist'), array_merge($rowList, array(
                    __b('Bestellung') => 'orderId', 
                    __b('Wieso') => 'comment', 
                    __b('Supporter') => 'aau.name', 
                    __b('Wann') => 'created',
                    __b('Verhalten') => 'bv.behaviour',
                    'ID' => 'b.id'
                    )))
               ->join(array('bv' => "blacklist_values"), "b.id = bv.blacklistId", $rowList)
               ->joinLeft(array('aau' => 'admin_access_users'), 'aau.id = b.adminId', array())
               ->group('b.id');
        
        foreach($types as $type){
            $select->orHaving($type ." IS NOT NULL" );
        }
                        
        return $select;
    }

}
