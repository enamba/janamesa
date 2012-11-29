<?php

/**
 * GeoPC Database
 * @author Jens Naie <naie@lieferando.de>
 */
class Yourdelivery_Model_DbTable_GeoPC extends Default_Model_DbTable_Base {

    protected $_name = "GeoPC";

    protected $_dependentTables = array(
        'Yourdelivery_Model_DbTable_Regions',
        'Yourdelivery_Model_DbTable_Districts',
    );

    /**
     * primary key
     * @param string
     */
    protected $_primary = 'ID';
}
