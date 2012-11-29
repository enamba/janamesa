<?php
/**
 * TAble to store options
 *
 * @author mlaug
 */
class Default_Option_Table extends Default_Model_DbTable_Base {
     /**
     * name of the table
     * @param string
     */
    protected $_name = 'options';

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'id';

    /**
     * delete an option if available
     * @author mlaug
     * @param string $hash
     * @param string $option
     * @return boolean
     */
    public function remove($hash,$option){
        $select = $this->select()
                       ->where('hash=?',$hash)
                       ->where('optionName=?',$option);
        $row = $this->fetchRow($select);
        if ( $row instanceof Zend_Db_Table_Row_Abstract ){
            $row->delete();
            return true;
        }
        return false;
    }
    
    /**
     * get an option
     * @author mlaug
     * @param string $hash
     * @param string $option
     * @return  Zend_Db_Table_Row
     */
    public function get($hash,$option){
        $select = $this->select()
                       ->where('hash=?',$hash)
                       ->where('optionName=?',$option);
        $row = $this->fetchRow($select);
        if ( $row instanceof Zend_Db_Table_Row_Abstract ){
            return $row;
        }
        return null;
    }

    /**
     * add/update an option
     * @author mlaug
     * @param string $hash
     * @param string $option
     * @param mixed $value
     * @return int
     */
    public function set($hash,$option,$value){
        //get old one if available
        $old = $this->get($hash,$option);
        if ( $old instanceof Zend_Db_Table_Row_Abstract ){
            $row = $old;
        }
        else{
            $row = $this->createRow();
        }
        $row->hash = $hash;
        $row->optionName = $option;
        $row->optionValue = $value;
        return $row->save();
    }

}
?>
