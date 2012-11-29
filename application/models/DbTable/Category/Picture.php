<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Support
 *
 * @author mlaug
 */
class Yourdelivery_Model_DbTable_Category_Picture extends Default_Model_DbTable_Base {

    protected $_primary = 'id';

    protected $_name = 'category_picture';
    
    /**
     * get all category_picture entries
     * @return array
     */
    public static function all(){
        $db = Zend_Registry::get('dbAdapter');
        $sql = "select * from category_picture";
        return $db->fetchAll($sql);
    }

    public function getIdsNames($sort = 'name'){
        $sql = sprintf('select id, name from category_picture order by ' . $sort);
        $fields = $this->getAdapter()->fetchAll($sql);
        return $fields;
    }

    /**
     * get all meal categories associated with this category
     * @author Alex Vait <vait@lieferando.de>
     * @since 07.12.2011
     */
    public function getAssociatedCategories(){
        $sql = sprintf('select * from meal_categories where categoryPictureId=%d', $this->getId());
        return $this->getAdapter()->fetchAll($sql);
    }
    
}
?>
