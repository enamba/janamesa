<?php

/**
 * Description of CmsSidebars
 *
 * @author mlaug
 */
class Yourdelivery_Model_DbTable_Cms_Sidebar extends Default_Model_DbTable_Base{

    protected $_name = "cms_sidebars";

/**
     * primary key
     * @param string
     */
    protected $_primary = 'id';

    /**
     *
     * @param integer $id
     * @param array $data
     *
     * @return void
     */
    public static function edit($id, $data)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->update('cms_sidebars', $data, 'cms_sidebars.id = ' . $id);
    }

    /**
     * delete a table row by given primary key
     * @param integer $id
     * @return void
     */
    public static function remove($id)
    {
        $db = Zend_Registry::get('dbAdapter');
        $db->delete('cms_sidebars', 'cms_sidebars.id = ' . $id);
    }

    /**
     * get rows
     * @param string $order
     * @param integer $limit
     * @param string $from
     */
    public static function get($order=null, $limit=0, $from=0)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("%ftable%" => "cms_sidebars") );

        if($order != null)
        {
            $query->order($order);
        }

        if($limit != 0)
        {
            $query->limit($limit, $from);
        }

        return $db->fetchAll($query);
    }
    
    /**
     * get a rows matching Id by given value
     * @param int $id
     */
    public static function findById($id)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("c" => "cms_sidebars") )
                    ->where( "c.id = " . $id );

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Title by given value
     * @param varchar $title
     */
    public static function findByTitle($title)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("c" => "cms_sidebars") )
                    ->where( "c.title = " . $title );

        return $db->fetchRow($query);
    }

    /**
     * get a rows matching Content by given value
     * @param text $content
     */
    public static function findByContent($content)
    {
        $db = Zend_Registry::get('dbAdapter');

        $query = $db->select()
                    ->from( array("c" => "cms_sidebars") )
                    ->where( "c.content = " . $content );

        return $db->fetchRow($query);
    }

}