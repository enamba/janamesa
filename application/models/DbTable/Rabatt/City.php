<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RabattRestaurant
 *
 * @author daniel
 */
class Yourdelivery_Model_DbTable_Rabatt_City extends  Default_Model_DbTable_Base{
    
    /**
     * Table name
     */
    protected $_name = 'rabatt_city';

    /**
     * Primary key name
     */
    protected $_primary = 'id';
    
    
    protected $_referenceMap = array(
      'Rabatt' => array(
          'columns' => 'rabattId',
          'refTableClass' => 'Yourdelivery_Model_DbTable_Rabatt',
          'refColumns' => 'id'
          )  
    );
    
    
    
    
}


