<?php
/**
 * Link model
 * @author vpriem
 */
class Yourdelivery_Model_Link{

    /**
     * Get all links for the grid
     * @author vpriem
     * @return Zend_Db_Select
     */
    public static function getGrid(){

        // get db
        $db = Zend_Registry::get('dbAdapter');

        return $db
            ->select()
            ->from("links",
                array("id", "domain", "url", "title", "Reiter" => "tab", "robots", "Ausgehende" => "linksTo", "Eingehende" => "linksFrom"))
            ->order("url");

    }
}
