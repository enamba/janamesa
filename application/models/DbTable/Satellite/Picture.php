<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of db-interface for pictures for satellites
 * @author alex
 * @since 28.04.2011
 */
class Yourdelivery_Model_DbTable_Satellite_Picture extends Default_Model_DbTable_Base {

    protected $_name = "satellite_pictures";

    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('satellite_pictures', 'satellite_pictures.id = ' . $id);
    }
}
?>
