<?php
/**
 * Filter model
 * @author vpriem
 * @since 01.09.2010
 */
class Yourdelivery_Model_Filter{

    /**
     * Get all filters for the grid
     * @author vpriem
     * @return Zend_Db_Select
     */
    public static function getGrid(){

        // get db
        $db = Zend_Registry::get('dbAdapter');

        return $db
            ->select()
            ->from("filters",
                array("id", "name", "Suchen" => "search", "Ersetzen" => "replace"));

    }
}
