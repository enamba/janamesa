<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Abstract
 *
 * @author matthiaslaug
 */
abstract class CrmController_Abstract extends Default_Controller_AdministrationBase{
    
    protected $object = null;
    
    public function init(){
        parent::init();
        $this->view->link = $this->getLink();
    }
    
    public function getObject(){
        return $this->object;
    }
    
    public function preDispatch(){
        parent::preDispatch();
        $this->view->setDir('administration/crm');
    }
    
    abstract public function getLink();
    
    public function indexAction(){
        $this->_forward('call');
    }

    abstract public function callAction();
    
    abstract public function taskAction();
}
?>
