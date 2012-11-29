<?php
/**
 * @author mlaug
 */
class Yourdelivery_Model_Upselling_Storage extends Default_Model_Base {

    /**
     * Get related table
     * @author mlaug
     * @since 01.07.2011
     * @return Yourdelivery_Model_DbTable_Upselling_Storage
     */
    public function getTable() {

        if ($this->_table === null) {
            $this->_table = new Yourdelivery_Model_DbTable_Upselling_Storage();
        }
        return $this->_table;
    }

    /**
     * @author mlaug
     * @since 01.07.2011
     * @return SplObjectStorage 
     */
    public function getProducts() {
        
        $spl = new SplObjectStorage();
        
        $products = $this->getTable()->getProducts();
        foreach ($products as $product) {
            $spl->attach(new Yourdelivery_Model_Upselling_Storage_Product($product['product']));
        }
        return $spl;
    }

}
