<?php
/**
 * Description of OrderSheet
 *
 * @package order
 * @subpackage pdf
 * @author mlaug
 */
abstract class Yourdelivery_Model_Order_Pdf_FaxAbstract{

    protected $_filename = null;

    protected $_storage = null;

    protected $_latex = null;

    protected $_order = null;


    /**
     * stores some data in session/cache
     * @author mlaug
     * @return array
     */
    public function __sleep(){
        $this->_table = null;
        return array('_filename','_storage','_latex','_order');
    }

    public function __construct(){
        //init latex engine
        $this->_latex = new Yourdelivery_Pdf_Latex();
        $this->_storage = new Default_File_Storage();
    }
    
    abstract public function generatePdf();

    /**
     * set order
     * @author mlaug
     * @param Yourdelivery_Model_OrderAbstract $order
     * @return boolean
     */
    public function setOrder($order = null){
        if ( is_null($order) ){
            return false;
        }
        $this->_order = $order;
        return true;
    }

    /**
     * get current order
     * @author mlaug
     * @return Yourdelivery_Model_OrderAbstract
     */
    public function getOrder(){
        return $this->_order;
    }
    
    /**
     * assign values to latex engine ( smarty is used here )
     * @author mlaug
     * @param mixed $spec
     * @param mixed $value
     */
    public function assign($spec,$value){
        $this->_latex->assign($spec, $value);
    }

}
