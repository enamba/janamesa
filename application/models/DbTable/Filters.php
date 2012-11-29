<?php
/**
 * Filters Db Table
 * @author vpriem
 * @since 27.08.2010
 */
class Yourdelivery_Model_DbTable_Filters extends Zend_Db_Table_Abstract{

    protected $_defaultSource = self::DEFAULT_DB;
    protected $_rowClass = 'Yourdelivery_Model_DbTableRow_Filters';

    /**
     * Table name
     */
    protected $_name = 'filters';

    /**
     * Primary key name
     */
    protected $_primary = 'id';

    /**
     * Find row
     * @author vpriem
     * @since 01.09.2010
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
    
    /**
     * Find row by name
     * @author vpriem
     * @since 27.08.2010
     * @param string $name
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function findByName ($name, $type = null) {

        $select = $this
            ->select()
            ->where("`name` = ?", $name)
            ->order("priority DESC");
        if ($type !== null) {
            $select->where("`type` = ?", $type);
        }
        
        return $this->fetchAll($select);

    }

}

/**
 * Filters Db Table Row
 * @author vpriem
 * @since 27.08.2010
 */
class Yourdelivery_Model_DbTableRow_Filters extends Zend_Db_Table_Row_Abstract{

    /**
     * Filter
     * @author vpriem
     * @since 27.08.2010
     * @param string $subject
     * @return string
     */
    public function filter ($subject) {

        switch ($this->type) {
            case "replace":
                if ($this->limit > 0) {
                    $subject = preg_replace("/" . preg_quote($this->search, "/") . "/", $this->replace, $subject, $this->limit);
                }
                else {
                    $subject = str_replace($this->search, $this->replace, $subject);
                }
                break;

            case "ireplace":
                if ($this->limit > 0) {
                    $subject = preg_replace("/" . preg_quote($this->search, "/") . "/i", $this->replace, $subject, $this->limit);
                }
                else {
                    $subject = str_ireplace($this->search, $this->replace, $subject);
                }
                break;

            case "regex":
            case "regexp":
                $subject = preg_replace($this->search, $this->replace, $subject, $this->limit);
                break;
            
        }
        return $subject;

    }

}