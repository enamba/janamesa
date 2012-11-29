<?php
/**
 * Restaurant tags
 *
 * @author Alex Vait 
 * @since 23.08.2012
 */
class Yourdelivery_Model_DbTable_Tag extends Default_Model_DbTable_Base {

    protected $_name = "tags";

    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('tags', 'id = ' . $id);
    }
}
?>
