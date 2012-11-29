<?php
/**
 * Upselling Storage DB Table
 * @author vpriem
 * @since 04.07.2011
 */
class Yourdelivery_Model_DbTable_Upselling_Storage extends Default_Model_DbTable_Base{

    /**
     * Table name
     * @var string
     */
    protected $_name = 'upselling_storage';

    /**
     * Primary key name
     * @var string
     */
    protected $_primary = 'id';
    
    /**
     * Get products
     * @author vpriem
     * @since 04.07.2011
     * @return array
     */
    public function getProducts(){
        
        return $this->getAdapter()->fetchAll(
            $this->select()
                 ->group('product')
        );
    }

}