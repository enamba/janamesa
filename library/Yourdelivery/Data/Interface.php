<?php
/**
 *
 * @author mlaug
 */
interface Yourdelivery_Data_Interface {
    
    public function setView($view);
    
    public function getView();
    
    public function regenerate();
    
    public function setSelect($select);

    public function getSelect();
    
}
