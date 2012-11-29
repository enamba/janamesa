<?php
/**
 * Links Category Db Table
 * @author vpriem
 * @since 22.09.2010
 */
class Yourdelivery_Model_DbTable_Links_Category extends Zend_Db_Table_Abstract{

    protected $_defaultSource = self::DEFAULT_DB;
    protected $_rowClass = 'Yourdelivery_Model_DbTableRow_Links_Category';

    /**
     * Table name
     */
    protected $_name = 'links_categories';

    /**
     * Primary key name
     */
    protected $_primary = 'id';

    /**
     * Find row
     * @author vpriem
     * @since 23.09.2010
     * @param int $id
     * @return Zend_Db_Table_Row_Abstract
     */
    public function findRow ($id) {

        if ($id !== null) {
            $rows = $this->find($id);
            if ($rows->count()) {
                return $rows->current();
            }
        }
        return false;

    }

}

/**
 * Links Category Db Table Row
 * @author vpriem
 * @since 27.09.2010
 */
class Yourdelivery_Model_DbTableRow_Links_Category extends Zend_Db_Table_Row_Abstract{

    /**
     * Get all links of a category
     * @author vpriem
     * @since 27.09.2010
     * @return array
     */
    public function getLinks(){

        return $this->getTable()->getAdapter()->fetchAll(
            "SELECT l.id, l.domain
            FROM `links` l
            WHERE l.categoryId = ?", $this->id
        );

    }

}