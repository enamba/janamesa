<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of City
 *
 * @author daniel
 */
class Yourdelivery_Model_DbTable_Mailing_Optivo_City extends Default_Model_DbTable_Base {
    
    /**
     * Table name
     */
    protected $_name = 'mailing_optivo_city';
    
    /**
     * Primary key name
     */
    protected $_primary = 'id';
    
    
    protected $_referenceMap = array(
      'Mailing_Optivo' => array(
          'columns' => 'mailingId',
          'refTableClass' => 'Yourdelivery_Model_DbTable_Mailing_Optivo',
          'refColumns' => 'id'
          )  
    );
                
}


