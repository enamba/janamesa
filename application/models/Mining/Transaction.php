<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Transaction
 *
 * @author mlaug
 */
class Yourdelivery_Model_Mining_Transaction extends Default_Model_Base {

    /**
     * store all items associated to transaction
     * @var SplObjectStorage
     */
    protected $_items = null;

    /**
     * support of transaction
     * @var float
     */
    protected $_support = 0;

    /**
     * confidence of transaction
     * @var float
     */
    protected $_confidence = 0;

    public function  __construct($id = null) {
        parent::__construct($id);
        $this->_items = new SplObjectStorage();
    }

    /**
     * add an item to this transaction
     * @param Yourdelivery_Model_Mining_Item $item
     */
    public function addItem($item = null){
        if ( is_object($item) ){
            $this->_items->attach($item);
        }
    }

    /**
     * get all items added to this transaction
     * @return SplObjectStorage
     */
    public function getItems(){
        return $this->_items;
    }

    /**
     * get count of all items
     * @return int
     */
    public function count(){
        return $this->_items->count();
    }

    /**
     * check if this transaction contains a certian item
     * @param Yourdelivery_Model_Mining_Item $item
     * @return boolean
     */
    public function contains($item = null){
        if ( !is_null($item) && is_object($item) ){
            foreach($this->getItems() as $_item){
                if ( $_item->getMealId() == $item->getMealId()){
                    return true;
                }
            }
        }
        return false;
    }

    //put your code here
    public function getTable() {
        return null;
    }
}
?>
