<?php
/**
 * @author mlaug
 */
class Yourdelivery_Model_Upselling_Storage_Product {

    /**
     * @var string
     */
    protected $_name = null;
   
    /**
     * @var array
     */
    protected $_labels = array(
        'Canton2626' => "Pizzakartons 26x26x4",
        'Canton2626N' => "Pizzakartons 26x26x4 Notebooksbilliger",
        'Canton2626D' => "Pizzakartons 26x26x4 Discotel",
        'Canton2626S' => "Pizzakartons 26x26x4 DeutschlandSIM",
        'Canton2626H' => "Pizzakartons 26x26x4 Hannover",
        'Canton2828' => "Pizzakartons 28x28x4",
        'Canton3232' => "Pizzakartons 32x32x4",
        'Sticks'     => "Chopsticks",
        'Servicing'  => "Servietten",
        'Bags'       => "Plastiktüten",
    );
    
    /**
     * @author mlaug
     * @since 02.07.2011
     * @param string $name
     */
    public function __construct($name) {
        
        $this->_name = $name;
    }

    /**
     * @author mlaug
     * @since 02.07.2011
     * @return string
     */
    public function getName() {
        
        return $this->_name;
    }

    /**
     * @author vpriem
     * @since 04.07.2011
     * @return string
     */
    public function getReadableName() {
        
        return $this->_labels[$this->_name];
    }
    
    /**
     * Get all products, which are aquired but not payed
     * @author vpriem
     * @since 04.07.2011
     * @return integer
     */
    public function getHold() {
        
        return $this->_select($this->_name, 0) + $this->_select($this->_name, 1);
    }
    
    /**
     * get all products, which are aquired and payed
     * @author mlaug
     * @since 02.07.2011
     * @return integer
     */
    public function getSend() {
        
        return $this->_select($this->_name, 2);
    }
    
    /**
     * check how many we have in store, this would be all available ever
     * bought - those already payed and send out
     * @author mlaug
     * @since 02.07.2011
     * @return integer
     */
    public function getCount() {
        
        return $this->_storage($this->_name) - $this->getSend();
    }

    /**
     * get all available products, which are those available in store
     * and not hold back
     * @author mlaug
     * @since 02.07.2011
     * @return integer
     */
    public function getFree() {
        
        return $this->getCount() - $this->getHold();
    }
    
    /**
     * get all every bought products
     * @author mlaug
     * @since 02.07.2011
     * @param string $product
     * @return integer
     */
    private function _storage($product){
        
        $db = Zend_Registry::get('dbAdapterReadOnly');
        return $db->fetchOne("SELECT SUM(`count`) FROM `upselling_storage` WHERE `product` = ?", $product);
    }

    /**
     * @author vpriem
     * @since 04.07.2011
     * @param string $product
     * @param int $status
     * @return integer
     */
    private function _select($product, $status = null) {

        // create select
        $db = Zend_Registry::get('dbAdapterReadOnly');
        return $db->fetchOne(
            "SELECT SUM(ug.count" . $product . " * ug.unit" . $product . ")
             FROM `upselling_goods` ug
             INNER JOIN `billing` b ON ug.id = b.refId 
                AND b.mode = 'upselling_goods'
             WHERE b.status = ?", $status);
    }

}
